<?php
/**
 * Elementor REST Controller (Basic - FREE)
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Basic Elementor REST controller.
 *
 * FREE tier includes:
 * - Get/set Elementor data
 * - Check Elementor status
 * - Create Elementor-enabled page
 *
 * PRO endpoints registered via site-pilot-ai-pro plugin.
 */
class Spai_REST_Elementor extends Spai_REST_API {

	/**
	 * Elementor handler.
	 *
	 * @var Spai_Elementor_Basic
	 */
	private $elementor;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->elementor = new Spai_Elementor_Basic();
	}

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Elementor status
		register_rest_route(
			$this->namespace,
			'/elementor/status',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_status' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Get/set page Elementor data
		register_rest_route(
			$this->namespace,
			'/elementor/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_elementor_data' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'set_elementor_data' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'elementor_data' => array(
							'description' => __( 'Elementor data (JSON array or object).', 'site-pilot-ai' ),
							'type'        => array( 'string', 'array' ),
						),
						'elementor_json' => array(
							'description' => __( 'Elementor data as JSON string.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
					),
				),
			)
		);

		// Create Elementor page
		register_rest_route(
			$this->namespace,
			'/elementor/page',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_elementor_page' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'title'          => array(
							'description' => __( 'Page title.', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
						),
						'status'         => array(
							'description' => __( 'Page status.', 'site-pilot-ai' ),
							'type'        => 'string',
							'enum'        => array( 'publish', 'draft', 'pending', 'private' ),
							'default'     => 'draft',
						),
						'elementor_data' => array(
							'description' => __( 'Initial Elementor data.', 'site-pilot-ai' ),
							'type'        => array( 'string', 'array' ),
						),
					),
				),
			)
		);

		// Regenerate CSS
		register_rest_route(
			$this->namespace,
			'/elementor/regenerate-css',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'regenerate_css' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'id' => array(
							'description' => __( 'Page ID. Omit to regenerate all site CSS.', 'site-pilot-ai' ),
							'type'        => 'integer',
						),
					),
				),
			)
		);
	}

	/**
	 * Get Elementor status.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_status( $request ) {
		$this->log_activity( 'elementor_status', $request );

		$status = $this->elementor->get_status();

		// Add info about available features
		$status['features'] = array(
			'free' => array(
				'get_data'    => true,
				'set_data'    => true,
				'create_page' => true,
			),
			'pro' => array(
				'templates'    => false,
				'landing_page' => false,
				'clone'        => false,
				'widgets'      => false,
				'globals'      => false,
			),
		);

		// Check if Pro is active
		if ( class_exists( 'Spai_Elementor_Pro' ) ) {
			$status['features']['pro'] = array(
				'templates'    => true,
				'landing_page' => true,
				'clone'        => true,
				'widgets'      => true,
				'globals'      => true,
			);
		}

		return $this->success_response( $status );
	}

	/**
	 * Get Elementor data for page.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function get_elementor_data( $request ) {
		$this->log_activity( 'get_elementor', $request );

		$page_id = $request->get_param( 'id' );
		$result = $this->elementor->get_elementor_data( $page_id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result );
	}

	/**
	 * Set Elementor data for page.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function set_elementor_data( $request ) {
		$this->log_activity( 'set_elementor', $request );

		$page_id = $request->get_param( 'id' );
		$data = array(
			'elementor_data' => $request->get_param( 'elementor_data' ),
			'elementor_json' => $request->get_param( 'elementor_json' ),
		);

		$result = $this->elementor->set_elementor_data( $page_id, $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result );
	}

	/**
	 * Create Elementor page.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function create_elementor_page( $request ) {
		$this->log_activity( 'create_elementor_page', $request );

		$data = array(
			'title'          => $request->get_param( 'title' ),
			'status'         => $request->get_param( 'status' ),
			'elementor_data' => $request->get_param( 'elementor_data' ),
		);

		$result = $this->elementor->create_elementor_page( $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result, 201 );
	}

	/**
	 * Regenerate Elementor CSS.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function regenerate_css( $request ) {
		$this->log_activity( 'regenerate_elementor_css', $request );

		$page_id = $request->get_param( 'id' );
		$result  = $this->elementor->regenerate_css( $page_id ? absint( $page_id ) : null );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result );
	}
}
