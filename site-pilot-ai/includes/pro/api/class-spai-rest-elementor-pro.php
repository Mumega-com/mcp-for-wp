<?php
/**
 * Elementor Pro REST API Controller
 *
 * @package SitePilotAI_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API controller for Elementor Pro features.
 *
 * Provides endpoints for templates, landing pages, cloning, and widgets.
 */
class Spai_REST_Elementor_Pro extends Spai_REST_API {

	/**
	 * Elementor Pro handler.
	 *
	 * @var Spai_Elementor_Pro
	 */
	private $elementor_pro;

	/**
	 * Constructor.
	 *
	 * @param Spai_Elementor_Pro $elementor_pro Elementor Pro handler.
	 */
	public function __construct( $elementor_pro ) {
		$this->elementor_pro = $elementor_pro;
	}

	/**
	 * Register REST routes.
	 */
	public function register_routes() {
		// Templates.
		register_rest_route(
			$this->namespace,
			'/elementor/templates',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_templates' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_template' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Single template.
		register_rest_route(
			$this->namespace,
			'/elementor/templates/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_template' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_template' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_template' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Apply template to page.
		register_rest_route(
			$this->namespace,
			'/elementor/templates/(?P<id>\d+)/apply',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'apply_template' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Clone page.
		register_rest_route(
			$this->namespace,
			'/elementor/clone',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'clone_page' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Landing page.
		register_rest_route(
			$this->namespace,
			'/elementor/landing-page',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_landing_page' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Widgets.
		register_rest_route(
			$this->namespace,
			'/elementor/widgets',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_widgets' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Globals.
		register_rest_route(
			$this->namespace,
			'/elementor/globals',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_globals' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
	}

	/**
	 * Get all templates.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_templates( $request ) {
		$args = array(
			'per_page' => $request->get_param( 'per_page' ) ?: 50,
			'page'     => $request->get_param( 'page' ) ?: 1,
			'type'     => $request->get_param( 'type' ),
		);

		$templates = $this->elementor_pro->get_templates( $args );

		$this->log_activity( 'get_templates', $request, $templates );

		return $this->success_response( array(
			'templates' => $templates,
			'total'     => count( $templates ),
		) );
	}

	/**
	 * Get single template.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function get_template( $request ) {
		$template_id = absint( $request->get_param( 'id' ) );
		$template    = $this->elementor_pro->get_template( $template_id );

		if ( is_wp_error( $template ) ) {
			$this->log_activity( 'get_template', $request, null, 404 );
			return $this->error_response( $template->get_error_code(), $template->get_error_message(), 404 );
		}

		$this->log_activity( 'get_template', $request, $template );

		return $this->success_response( $template );
	}

	/**
	 * Create a template.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function create_template( $request ) {
		$data = array(
			'title'          => $request->get_param( 'title' ),
			'type'           => $request->get_param( 'type' ),
			'elementor_data' => $request->get_param( 'elementor_data' ),
		);

		$template = $this->elementor_pro->create_template( $data );

		if ( is_wp_error( $template ) ) {
			$this->log_activity( 'create_template', $request, null, 400 );
			return $this->error_response( $template->get_error_code(), $template->get_error_message(), 400 );
		}

		$this->log_activity( 'create_template', $request, $template, 201 );

		return $this->success_response( $template, 201 );
	}

	/**
	 * Update a template.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function update_template( $request ) {
		$template_id = absint( $request->get_param( 'id' ) );
		$data        = array(
			'title'          => $request->get_param( 'title' ),
			'elementor_data' => $request->get_param( 'elementor_data' ),
		);

		$template = $this->elementor_pro->update_template( $template_id, $data );

		if ( is_wp_error( $template ) ) {
			$this->log_activity( 'update_template', $request, null, 400 );
			return $this->error_response( $template->get_error_code(), $template->get_error_message(), 400 );
		}

		$this->log_activity( 'update_template', $request, $template );

		return $this->success_response( $template );
	}

	/**
	 * Delete a template.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function delete_template( $request ) {
		$template_id = absint( $request->get_param( 'id' ) );
		$force       = (bool) $request->get_param( 'force' );

		$result = $this->elementor_pro->delete_template( $template_id, $force );

		if ( is_wp_error( $result ) ) {
			$this->log_activity( 'delete_template', $request, null, 400 );
			return $this->error_response( $result->get_error_code(), $result->get_error_message(), 400 );
		}

		$this->log_activity( 'delete_template', $request );

		return $this->success_response( array(
			'deleted' => true,
			'id'      => $template_id,
		) );
	}

	/**
	 * Apply template to page.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function apply_template( $request ) {
		$template_id = absint( $request->get_param( 'id' ) );
		$page_id     = absint( $request->get_param( 'page_id' ) );

		if ( ! $page_id ) {
			return $this->error_response( 'missing_page_id', __( 'Page ID is required.', 'site-pilot-ai' ), 400 );
		}

		$result = $this->elementor_pro->apply_template_to_page( $page_id, $template_id );

		if ( is_wp_error( $result ) ) {
			$this->log_activity( 'apply_template', $request, null, 400 );
			return $this->error_response( $result->get_error_code(), $result->get_error_message(), 400 );
		}

		$this->log_activity( 'apply_template', $request );

		return $this->success_response( array(
			'applied'     => true,
			'template_id' => $template_id,
			'page_id'     => $page_id,
			'edit_url'    => admin_url( 'post.php?post=' . $page_id . '&action=elementor' ),
		) );
	}

	/**
	 * Clone a page.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function clone_page( $request ) {
		$source_id = absint( $request->get_param( 'source_id' ) );

		if ( ! $source_id ) {
			return $this->error_response( 'missing_source_id', __( 'Source ID is required.', 'site-pilot-ai' ), 400 );
		}

		$args = array(
			'title'  => $request->get_param( 'title' ),
			'status' => $request->get_param( 'status' ),
			'parent' => $request->get_param( 'parent' ),
		);

		$result = $this->elementor_pro->clone_page( $source_id, $args );

		if ( is_wp_error( $result ) ) {
			$this->log_activity( 'clone_page', $request, null, 400 );
			return $this->error_response( $result->get_error_code(), $result->get_error_message(), 400 );
		}

		$this->log_activity( 'clone_page', $request, $result, 201 );

		return $this->success_response( $result, 201 );
	}

	/**
	 * Create a landing page.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function create_landing_page( $request ) {
		$data = array(
			'title'          => $request->get_param( 'title' ),
			'status'         => $request->get_param( 'status' ),
			'template_id'    => $request->get_param( 'template_id' ),
			'sections'       => $request->get_param( 'sections' ),
			'elementor_data' => $request->get_param( 'elementor_data' ),
		);

		$result = $this->elementor_pro->create_landing_page( $data );

		if ( is_wp_error( $result ) ) {
			$this->log_activity( 'create_landing_page', $request, null, 400 );
			return $this->error_response( $result->get_error_code(), $result->get_error_message(), 400 );
		}

		$this->log_activity( 'create_landing_page', $request, $result, 201 );

		return $this->success_response( $result, 201 );
	}

	/**
	 * Get available widgets.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_widgets( $request ) {
		$widgets = $this->elementor_pro->get_available_widgets();

		$this->log_activity( 'get_widgets', $request );

		return $this->success_response( array(
			'widgets' => $widgets,
			'total'   => count( $widgets ),
		) );
	}

	/**
	 * Get global settings.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function get_globals( $request ) {
		$globals = $this->elementor_pro->get_globals();

		if ( is_wp_error( $globals ) ) {
			$this->log_activity( 'get_globals', $request, null, 400 );
			return $this->error_response( $globals->get_error_code(), $globals->get_error_message(), 400 );
		}

		$this->log_activity( 'get_globals', $request, $globals );

		return $this->success_response( $globals );
	}
}
