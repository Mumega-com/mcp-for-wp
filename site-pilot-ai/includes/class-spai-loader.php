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

	use Spai_Logging;

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
		// Run late so we can prune any Freemius-injected "upgrade" links when Pro is active.
		$this->add_filter( 'plugin_action_links_' . SPAI_PLUGIN_BASENAME, $admin, 'add_action_links', 100 );
	}

	/**
	 * Register API hooks.
	 */
	private function define_api_hooks() {
		// Initialize REST API
		$this->add_action( 'rest_api_init', $this, 'register_rest_routes' );
		// Attach rate-limit headers to both success and error responses.
		$this->add_filter( 'rest_post_dispatch', $this, 'add_spai_rate_limit_headers', 10, 3 );
		// Ensure log cleanup cron is scheduled and executed.
		$this->add_action( 'init', $this, 'maybe_schedule_log_cleanup' );
		$this->add_action( 'spai_cleanup_logs', $this, 'cleanup_old_logs' );

		// Alert checks (cron).
		$this->add_filter( 'cron_schedules', $this, 'register_cron_schedules' );
		$this->add_action( 'init', $this, 'maybe_schedule_alert_checks' );
		$this->add_action( 'spai_check_alerts', $this, 'check_alerts' );
	}

	/**
	 * Register custom cron schedules.
	 *
	 * @param array $schedules Schedules.
	 * @return array
	 */
	public function register_cron_schedules( $schedules ) {
		if ( ! isset( $schedules['spai_every_five_minutes'] ) ) {
			$schedules['spai_every_five_minutes'] = array(
				'interval' => 5 * MINUTE_IN_SECONDS,
				'display'  => __( 'Every 5 Minutes (SPAI)', 'site-pilot-ai' ),
			);
		}

		return $schedules;
	}

	/**
	 * Ensure periodic alert checks are scheduled.
	 */
	public function maybe_schedule_alert_checks() {
		if ( ! function_exists( 'wp_next_scheduled' ) || ! function_exists( 'wp_schedule_event' ) ) {
			return;
		}

		if ( wp_next_scheduled( 'spai_check_alerts' ) ) {
			return;
		}

		wp_schedule_event( time() + 2 * MINUTE_IN_SECONDS, 'spai_every_five_minutes', 'spai_check_alerts' );
	}

	/**
	 * Run alert checks.
	 */
	public function check_alerts() {
		if ( ! class_exists( 'Spai_Alerts' ) ) {
			return;
		}

		$alerts = new Spai_Alerts();
		$alerts->check_alerts();
	}

	/**
	 * Ensure periodic activity-log cleanup is scheduled.
	 */
	public function maybe_schedule_log_cleanup() {
		if ( ! function_exists( 'wp_next_scheduled' ) || ! function_exists( 'wp_schedule_event' ) ) {
			return;
		}

		if ( wp_next_scheduled( 'spai_cleanup_logs' ) ) {
			return;
		}

		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'spai_cleanup_logs' );
	}

	/**
	 * Cleanup old activity logs using configured retention days.
	 */
	public function cleanup_old_logs() {
		global $wpdb;
		if ( ! isset( $wpdb->prefix ) ) {
			return;
		}

		$settings = get_option( 'spai_settings', array() );
		$days     = isset( $settings['log_retention_days'] ) ? max( 1, absint( $settings['log_retention_days'] ) ) : 30;
		$table    = $wpdb->prefix . 'spai_activity_log';
		$cutoff   = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table} WHERE created_at < %s",
				$cutoff
			)
		);
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

		// Menus
		$menus_controller = new Spai_REST_Menus();
		$menus_controller->register_routes();

		// Generic content (custom post types)
		$content_controller = new Spai_REST_Content();
		$content_controller->register_routes();

		// Elementor (basic)
		$elementor_controller = new Spai_REST_Elementor();
		$elementor_controller->register_routes();

		// Webhooks
		$webhooks_controller = new Spai_REST_Webhooks();
		$webhooks_controller->register_routes();

		// Screenshot
		$screenshot_controller = new Spai_REST_Screenshot();
		$screenshot_controller->register_routes();

		// MCP (Model Context Protocol)
		$mcp_controller = new Spai_REST_MCP();
		$mcp_controller->register_routes();

		// Batch
		$batch_controller = new Spai_REST_Batch();
		$batch_controller->register_routes();

		/**
		 * Action to register additional REST routes.
		 *
		 * Used by Pro add-on to register additional endpoints.
		 */
		do_action( 'spai_register_rest_routes' );

		// Register routes from third-party integrations.
		if ( class_exists( 'Spai_Integration' ) ) {
			foreach ( Spai_Integration::resolve_all() as $integration ) {
				$integration->register_routes();
			}
		}
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

		// Ensure we log permission_callback failures and server errors which never reach controller-level logging.
		if ( $status >= 400 ) {
			$payload = null;
			if ( method_exists( $response, 'get_data' ) ) {
				$payload = $response->get_data();
			}
			$this->log_activity( 'rest_error', $request, $payload, $status );
		}

		if ( 429 !== $status ) {
			return $response;
		}

		// Ensure 429 responses always have a JSON body (#92).
		$data = method_exists( $response, 'get_data' ) ? $response->get_data() : null;

		if ( empty( $data ) ) {
			// Build a fallback body from rate limiter state.
			$rl_headers = $limiter->get_headers();
			$retry      = isset( $rl_headers['Retry-After'] ) ? (int) $rl_headers['Retry-After'] : 0;
			$response->set_data( array(
				'code'    => 'rate_limit_exceeded',
				'message' => sprintf( 'Rate limit exceeded. Try again in %d seconds.', $retry ),
				'data'    => array(
					'status'      => 429,
					'retry_after' => $retry,
				),
			) );
			$data = $response->get_data();
		}

		$response->header( 'Content-Type', 'application/json; charset=UTF-8' );

		if ( is_array( $data ) && ! empty( $data['data'] ) && is_array( $data['data'] ) ) {
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
