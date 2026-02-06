<?php
/**
 * Plugin Loader
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main loader class.
 *
 * Orchestrates plugin initialization and hooks.
 */
class Spai_Loader {

	/**
	 * Array of actions to register.
	 *
	 * @var array
	 */
	protected $actions = array();

	/**
	 * Array of filters to register.
	 *
	 * @var array
	 */
	protected $filters = array();

	/**
	 * Initialize the loader.
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_api_hooks();
	}

	/**
	 * Load dependencies.
	 */
	private function load_dependencies() {
		// Dependencies are loaded in main plugin file
	}

	/**
	 * Set plugin locale for internationalization.
	 */
	private function set_locale() {
		$i18n = new Spai_i18n();
		$this->add_action( 'plugins_loaded', $i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register admin hooks.
	 */
	private function define_admin_hooks() {
		$admin = new Spai_Admin();
		$settings = new Spai_Settings();

		// Admin menu
		$this->add_action( 'admin_menu', $admin, 'add_admin_menu' );
		$this->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );

		// Settings
		$this->add_action( 'admin_init', $settings, 'register_settings' );

		// Admin notices
		$this->add_action( 'admin_notices', $admin, 'admin_notices' );

		// AJAX handlers
		$this->add_action( 'wp_ajax_spai_test_connection', $admin, 'ajax_test_connection' );
		$this->add_action( 'wp_ajax_spai_dismiss_welcome', $admin, 'ajax_dismiss_welcome' );

		// Plugin action links
		$this->add_filter( 'plugin_action_links_' . SPAI_PLUGIN_BASENAME, $admin, 'add_action_links' );
	}

	/**
	 * Register API hooks.
	 */
	private function define_api_hooks() {
		// Initialize REST API
		$this->add_action( 'rest_api_init', $this, 'register_rest_routes' );
		// Attach rate-limit headers to both success and error responses.
		$this->add_filter( 'rest_post_dispatch', $this, 'add_spai_rate_limit_headers', 10, 3 );
	}

	/**
	 * Register all REST routes.
	 */
	public function register_rest_routes() {
		// Site info
		$site_controller = new Spai_REST_Site();
		$site_controller->register_routes();

		// Posts
		$posts_controller = new Spai_REST_Posts();
		$posts_controller->register_routes();

		// Pages
		$pages_controller = new Spai_REST_Pages();
		$pages_controller->register_routes();

		// Media
		$media_controller = new Spai_REST_Media();
		$media_controller->register_routes();

		// Elementor (basic)
		$elementor_controller = new Spai_REST_Elementor();
		$elementor_controller->register_routes();

		// Webhooks
		$webhooks_controller = new Spai_REST_Webhooks();
		$webhooks_controller->register_routes();

		// MCP (Model Context Protocol)
		$mcp_controller = new Spai_REST_MCP();
		$mcp_controller->register_routes();

		/**
		 * Action to register additional REST routes.
		 *
		 * Used by Pro add-on to register additional endpoints.
		 */
		do_action( 'spai_register_rest_routes' );
	}

	/**
	 * Add SPAI rate-limit headers to REST responses.
	 *
	 * @param WP_HTTP_Response $response Result to send to the client.
	 * @param WP_REST_Server   $server   Server instance.
	 * @param WP_REST_Request  $request  Request used to generate the response.
	 * @return WP_HTTP_Response Filtered response.
	 */
	public function add_spai_rate_limit_headers( $response, $server, $request ) {
		if ( ! class_exists( 'Spai_Rate_Limiter' ) ) {
			return $response;
		}

		if ( ! $request instanceof WP_REST_Request ) {
			return $response;
		}

		if ( ! method_exists( $request, 'get_route' ) || ! method_exists( $response, 'header' ) ) {
			return $response;
		}

		$route = (string) $request->get_route();
		if ( 0 !== strpos( $route, '/site-pilot-ai/v1/' ) ) {
			return $response;
		}

		$limiter = Spai_Rate_Limiter::get_instance();
		$headers = $limiter->get_headers();

		foreach ( $headers as $key => $value ) {
			$response->header( $key, $value );
		}

		$status = method_exists( $response, 'get_status' ) ? (int) $response->get_status() : 200;
		if ( 429 !== $status ) {
			return $response;
		}

		$data = method_exists( $response, 'get_data' ) ? $response->get_data() : null;
		if ( ! is_array( $data ) || empty( $data['data'] ) || ! is_array( $data['data'] ) ) {
			return $response;
		}

		if ( isset( $data['data']['retry_after'] ) ) {
			$response->header( 'Retry-After', max( 0, (int) $data['data']['retry_after'] ) );
		}

		if ( isset( $data['data']['limit'] ) ) {
			$response->header( 'X-RateLimit-Limit', (int) $data['data']['limit'] );
		}

		if ( isset( $data['data']['remaining'] ) ) {
			$response->header( 'X-RateLimit-Remaining', max( 0, (int) $data['data']['remaining'] ) );
		}

		if ( isset( $data['data']['reset'] ) ) {
			$response->header( 'X-RateLimit-Reset', (int) $data['data']['reset'] );
		}

		return $response;
	}

	/**
	 * Add an action to the collection.
	 *
	 * @param string $hook          Hook name.
	 * @param object $component     Component with the callback.
	 * @param string $callback      Callback method name.
	 * @param int    $priority      Priority.
	 * @param int    $accepted_args Number of accepted arguments.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a filter to the collection.
	 *
	 * @param string $hook          Hook name.
	 * @param object $component     Component with the callback.
	 * @param string $callback      Callback method name.
	 * @param int    $priority      Priority.
	 * @param int    $accepted_args Number of accepted arguments.
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add hook to collection.
	 *
	 * @param array  $hooks         Current hooks.
	 * @param string $hook          Hook name.
	 * @param object $component     Component with the callback.
	 * @param string $callback      Callback method name.
	 * @param int    $priority      Priority.
	 * @param int    $accepted_args Number of accepted arguments.
	 * @return array Updated hooks.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);
		return $hooks;
	}

	/**
	 * Register all hooks with WordPress.
	 */
	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}

		foreach ( $this->actions as $hook ) {
			add_action(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}
	}
}
