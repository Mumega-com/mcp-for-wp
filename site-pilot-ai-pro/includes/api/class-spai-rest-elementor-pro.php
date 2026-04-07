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

		register_rest_route(
			$this->namespace,
			'/elementor/archetypes',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_archetypes' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_archetype' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/elementor/archetypes/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_archetype' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_archetype' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/elementor/archetypes/(?P<id>\d+)/apply',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'apply_archetype' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/elementor/parts',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_parts' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_part' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/elementor/parts/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_part' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_part' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/elementor/parts/(?P<id>\d+)/apply',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'apply_part' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/elementor/parts/from-section',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_part_from_section' ),
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
			return $this->error_response( 'missing_page_id', __( 'Page ID is required.', 'site-pilot-ai-pro' ), 400 );
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
	 * List reusable Elementor archetypes.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_archetypes( $request ) {
		$args = array(
			'per_page'        => $request->get_param( 'per_page' ) ?: 50,
			'page'            => $request->get_param( 'page' ) ?: 1,
			'scope'           => $request->get_param( 'scope' ),
			'archetype_class' => $request->get_param( 'archetype_class' ),
			'style'           => $request->get_param( 'style' ),
			'search'          => $request->get_param( 'search' ),
		);

		$archetypes = $this->elementor_pro->get_archetypes( $args );

		$this->log_activity( 'get_archetypes', $request, $archetypes );

		return $this->success_response(
			array(
				'archetypes' => $archetypes,
				'total'      => count( $archetypes ),
			)
		);
	}

	/**
	 * Get a reusable Elementor archetype.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function get_archetype( $request ) {
		$archetype_id = absint( $request->get_param( 'id' ) );
		$archetype    = $this->elementor_pro->get_archetype( $archetype_id );

		if ( is_wp_error( $archetype ) ) {
			$this->log_activity( 'get_archetype', $request, null, 404 );
			return $this->error_response( $archetype->get_error_code(), $archetype->get_error_message(), 404 );
		}

		$this->log_activity( 'get_archetype', $request, $archetype );

		return $this->success_response( $archetype );
	}

	/**
	 * Create a reusable Elementor archetype.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function create_archetype( $request ) {
		$data = array(
			'title'           => $request->get_param( 'title' ),
			'type'            => $request->get_param( 'type' ),
			'elementor_data'  => $request->get_param( 'elementor_data' ),
			'archetype_scope' => $request->get_param( 'archetype_scope' ),
			'archetype_class' => $request->get_param( 'archetype_class' ),
			'archetype_style' => $request->get_param( 'archetype_style' ),
		);

		$archetype = $this->elementor_pro->create_archetype( $data );

		if ( is_wp_error( $archetype ) ) {
			$this->log_activity( 'create_archetype', $request, null, 400 );
			return $this->error_response( $archetype->get_error_code(), $archetype->get_error_message(), 400 );
		}

		$this->log_activity( 'create_archetype', $request, $archetype, 201 );

		return $this->success_response( $archetype, 201 );
	}

	/**
	 * Update a reusable Elementor archetype.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function update_archetype( $request ) {
		$archetype_id = absint( $request->get_param( 'id' ) );
		$data         = array(
			'title'           => $request->get_param( 'title' ),
			'elementor_data'  => $request->get_param( 'elementor_data' ),
			'archetype_scope' => $request->get_param( 'archetype_scope' ),
			'archetype_class' => $request->get_param( 'archetype_class' ),
			'archetype_style' => $request->get_param( 'archetype_style' ),
			'is_archetype'    => true,
		);

		$archetype = $this->elementor_pro->update_template( $archetype_id, $data );

		if ( is_wp_error( $archetype ) ) {
			$this->log_activity( 'update_archetype', $request, null, 400 );
			return $this->error_response( $archetype->get_error_code(), $archetype->get_error_message(), 400 );
		}

		$this->log_activity( 'update_archetype', $request, $archetype );

		return $this->success_response( $archetype );
	}

	/**
	 * Apply a reusable archetype to a page.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function apply_archetype( $request ) {
		$archetype_id = absint( $request->get_param( 'id' ) );
		$page_id      = absint( $request->get_param( 'page_id' ) );

		if ( ! $page_id ) {
			return $this->error_response( 'missing_page_id', __( 'Page ID is required.', 'site-pilot-ai-pro' ), 400 );
		}

		$result = $this->elementor_pro->apply_archetype_to_page( $archetype_id, $page_id );

		if ( is_wp_error( $result ) ) {
			$this->log_activity( 'apply_archetype', $request, null, 400 );
			return $this->error_response( $result->get_error_code(), $result->get_error_message(), 400 );
		}

		$this->log_activity( 'apply_archetype', $request );

		return $this->success_response(
			array(
				'applied'      => true,
				'archetype_id' => $archetype_id,
				'page_id'      => $page_id,
				'edit_url'     => admin_url( 'post.php?post=' . $page_id . '&action=elementor' ),
			)
		);
	}

	/**
	 * List reusable Elementor parts.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_parts( $request ) {
		$args = array(
			'per_page' => $request->get_param( 'per_page' ) ?: 50,
			'page'     => $request->get_param( 'page' ) ?: 1,
			'kind'     => $request->get_param( 'kind' ),
			'style'    => $request->get_param( 'style' ),
			'tag'      => $request->get_param( 'tag' ),
			'search'   => $request->get_param( 'search' ),
		);

		$parts = $this->elementor_pro->get_parts( $args );

		$this->log_activity( 'get_parts', $request, $parts );

		return $this->success_response(
			array(
				'parts' => $parts,
				'total' => count( $parts ),
			)
		);
	}

	/**
	 * Get a reusable Elementor part.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function get_part( $request ) {
		$part_id = absint( $request->get_param( 'id' ) );
		$part    = $this->elementor_pro->get_part( $part_id );

		if ( is_wp_error( $part ) ) {
			$this->log_activity( 'get_part', $request, null, 404 );
			return $this->error_response( $part->get_error_code(), $part->get_error_message(), 404 );
		}

		$this->log_activity( 'get_part', $request, $part );

		return $this->success_response( $part );
	}

	/**
	 * Create a reusable Elementor part.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function create_part( $request ) {
		$data = array(
			'title'          => $request->get_param( 'title' ),
			'type'           => $request->get_param( 'type' ),
			'elementor_data' => $request->get_param( 'elementor_data' ),
			'part_kind'      => $request->get_param( 'part_kind' ),
			'part_style'     => $request->get_param( 'part_style' ),
			'part_tags'      => $request->get_param( 'part_tags' ),
		);

		$part = $this->elementor_pro->create_part( $data );

		if ( is_wp_error( $part ) ) {
			$this->log_activity( 'create_part', $request, null, 400 );
			return $this->error_response( $part->get_error_code(), $part->get_error_message(), 400 );
		}

		$this->log_activity( 'create_part', $request, $part, 201 );

		return $this->success_response( $part, 201 );
	}

	/**
	 * Update a reusable Elementor part.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function update_part( $request ) {
		$part_id = absint( $request->get_param( 'id' ) );
		$data    = array(
			'title'          => $request->get_param( 'title' ),
			'elementor_data' => $request->get_param( 'elementor_data' ),
			'part_kind'      => $request->get_param( 'part_kind' ),
			'part_style'     => $request->get_param( 'part_style' ),
			'part_tags'      => $request->get_param( 'part_tags' ),
			'is_part'        => true,
		);

		$part = $this->elementor_pro->update_template( $part_id, $data );

		if ( is_wp_error( $part ) ) {
			$this->log_activity( 'update_part', $request, null, 400 );
			return $this->error_response( $part->get_error_code(), $part->get_error_message(), 400 );
		}

		$this->log_activity( 'update_part', $request, $part );

		return $this->success_response( $part );
	}

	/**
	 * Create a reusable part from a live section.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function create_part_from_section( $request ) {
		$page_id    = (int) $request->get_param( 'page_id' );
		$element_id = $request->get_param( 'element_id' );

		if ( ! $page_id || ! $element_id ) {
			return $this->error_response( 'missing_params', __( 'page_id and element_id are required.', 'site-pilot-ai-pro' ), 400 );
		}

		$data = array(
			'title'      => $request->get_param( 'title' ),
			'part_kind'  => $request->get_param( 'part_kind' ),
			'part_style' => $request->get_param( 'part_style' ),
			'part_tags'  => $request->get_param( 'part_tags' ),
		);

		$part = $this->elementor_pro->create_part_from_section( $page_id, $element_id, $data );

		if ( is_wp_error( $part ) ) {
			$this->log_activity( 'create_part_from_section', $request, null, 400 );
			return $this->error_response( $part->get_error_code(), $part->get_error_message(), 400 );
		}

		$this->log_activity( 'create_part_from_section', $request, $part, 201 );

		return $this->success_response( $part, 201 );
	}

	/**
	 * Apply a reusable part to a page.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function apply_part( $request ) {
		$part_id = absint( $request->get_param( 'id' ) );
		$page_id = absint( $request->get_param( 'page_id' ) );
		$mode    = sanitize_key( (string) ( $request->get_param( 'mode' ) ?: 'replace' ) );
		$position = (string) ( $request->get_param( 'position' ) ?: 'end' );

		if ( ! $page_id ) {
			return $this->error_response( 'missing_page_id', __( 'Page ID is required.', 'site-pilot-ai-pro' ), 400 );
		}

		if ( 'insert' === $mode ) {
			$result = $this->elementor_pro->insert_part_into_page( $part_id, $page_id, $position );
		} else {
			$result = $this->elementor_pro->apply_part_to_page( $part_id, $page_id );
		}

		if ( is_wp_error( $result ) ) {
			$this->log_activity( 'apply_part', $request, null, 400 );
			return $this->error_response( $result->get_error_code(), $result->get_error_message(), 400 );
		}

		$this->log_activity( 'apply_part', $request );

		return $this->success_response(
			array(
				'applied'  => true,
				'part_id'  => $part_id,
				'page_id'  => $page_id,
				'mode'     => $mode,
				'position' => 'insert' === $mode ? $position : null,
				'details'  => $result,
				'edit_url' => admin_url( 'post.php?post=' . $page_id . '&action=elementor' ),
			)
		);
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
			return $this->error_response( 'missing_source_id', __( 'Source ID is required.', 'site-pilot-ai-pro' ), 400 );
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
