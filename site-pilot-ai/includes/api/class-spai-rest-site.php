<?php
/**
 * Site REST Controller
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site info REST controller.
 */
class Spai_REST_Site extends Spai_REST_API {

	/**
	 * Core handler.
	 *
	 * @var Spai_Core
	 */
	private $core;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->core = new Spai_Core();
	}

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Site info
		register_rest_route(
			$this->namespace,
			'/site-info',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_site_info' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Analytics
		register_rest_route(
			$this->namespace,
			'/analytics',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_analytics' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'days' => array(
							'description' => __( 'Number of days.', 'site-pilot-ai' ),
							'type'        => 'integer',
							'default'     => 30,
							'minimum'     => 1,
							'maximum'     => 365,
						),
					),
				),
			)
		);

		// Plugin detection
		register_rest_route(
			$this->namespace,
			'/plugins',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_plugins' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);
	}

	/**
	 * Get site info.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_site_info( $request ) {
		$this->log_activity( 'site_info', $request );

		$info = $this->core->get_site_info();

		return $this->success_response( $info );
	}

	/**
	 * Get analytics.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_analytics( $request ) {
		$this->log_activity( 'analytics', $request );

		$days = $request->get_param( 'days' );
		$analytics = $this->core->get_analytics( $days );

		return $this->success_response( $analytics );
	}

	/**
	 * Get detected plugins.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_plugins( $request ) {
		$this->log_activity( 'plugins', $request );

		$plugins = $this->core->detect_plugins();

		return $this->success_response( array(
			'plugins'      => $plugins,
			'capabilities' => $this->core->get_capabilities(),
		) );
	}
}
