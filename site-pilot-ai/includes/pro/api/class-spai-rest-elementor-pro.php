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
	 * Elementor Pro Custom Code post type.
	 *
	 * Elementor Pro registers this CPT for Custom Code snippets.
	 *
	 * @var string
	 */
	private $custom_code_cpt = 'elementor_snippet';

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

		// Reusable parts library.
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
				'args'                => array(
					'widget' => array(
						'description' => __( 'Widget type name to get full controls schema.', 'site-pilot-ai' ),
						'type'        => 'string',
					),
				),
			)
		);

		// Globals.
		register_rest_route(
			$this->namespace,
			'/elementor/globals',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_globals' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'set_globals' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Elementor Pro Custom Code (snippets).
		register_rest_route(
			$this->namespace,
			'/elementor/custom-code',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_custom_code' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => $this->get_pagination_args(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/elementor/custom-code/(?P<id>\\d+)/disable',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'disable_custom_code' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/elementor/custom-code/(?P<id>\\d+)/enable',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'enable_custom_code' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/elementor/custom-code/(?P<id>\\d+)/sanitize',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'sanitize_custom_code' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Build page from section blueprints.
		register_rest_route(
			$this->namespace,
			'/elementor/build-page',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'build_page' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Blueprint catalog.
		register_rest_route(
			$this->namespace,
			'/elementor/blueprints',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'list_blueprints' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Single blueprint builder.
		register_rest_route(
			$this->namespace,
			'/elementor/blueprints/build',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'get_blueprint' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Save section as template.
		register_rest_route(
			$this->namespace,
			'/elementor/save-section-as-template',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'save_section_as_template' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
	}

	/**
	 * List Elementor Pro Custom Code snippets.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function list_custom_code( $request ) {
		$args = $this->sanitize_query_args(
			array(
				'per_page' => $request->get_param( 'per_page' ) ?: 50,
				'page'     => $request->get_param( 'page' ) ?: 1,
				'status'   => $request->get_param( 'status' ) ?: 'any',
				'search'   => $request->get_param( 'search' ),
			)
		);

		$args['post_type']      = $this->custom_code_cpt;
		$args['posts_per_page'] = isset( $args['posts_per_page'] ) ? $args['posts_per_page'] : 50;
		$args['paged']          = isset( $args['paged'] ) ? $args['paged'] : 1;

		$query = new WP_Query( $args );

		$snippets = array();
		foreach ( $query->posts as $post ) {
			$snippets[] = $this->format_custom_code_snippet( $post );
		}

		$this->log_activity( 'list_elementor_custom_code', $request, array( 'count' => count( $snippets ) ) );

		return $this->success_response(
			array(
				'snippets'  => $snippets,
				'total'     => (int) $query->found_posts,
				'page'      => (int) $args['paged'],
				'per_page'  => (int) $args['posts_per_page'],
				'post_type' => $this->custom_code_cpt,
			)
		);
	}

	/**
	 * Disable a Custom Code snippet (set to draft).
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function disable_custom_code( $request ) {
		return $this->set_custom_code_status( $request, 'draft' );
	}

	/**
	 * Enable a Custom Code snippet (set to publish).
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function enable_custom_code( $request ) {
		return $this->set_custom_code_status( $request, 'publish' );
	}

	/**
	 * Sanitize a Custom Code snippet by stripping invalid wrapper tags.
	 *
	 * Scans all string post meta values and removes <html>/<head>/<body> tags.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function sanitize_custom_code( $request ) {
		$id   = absint( $request->get_param( 'id' ) );
		$post = get_post( $id );

		if ( ! $post || $this->custom_code_cpt !== $post->post_type ) {
			$this->log_activity( 'sanitize_elementor_custom_code', $request, null, 404 );
			return $this->error_response( 'not_found', __( 'Custom Code snippet not found.', 'site-pilot-ai' ), 404 );
		}

		$meta    = get_post_meta( $id );
		$changed = 0;
		$matches = array();

		foreach ( $meta as $key => $values ) {
			if ( ! is_array( $values ) ) {
				continue;
			}

			foreach ( $values as $value ) {
				if ( ! is_string( $value ) || '' === $value ) {
					continue;
				}

				if ( ! $this->contains_wrapper_html_tags( $value ) ) {
					continue;
				}

				$sanitized = $this->strip_wrapper_html_tags( $value );
				if ( empty( $sanitized['changed'] ) ) {
					continue;
				}

				update_post_meta( $id, $key, $sanitized['content'], $value );
				$changed++;
				$matches[] = $key;
			}
		}

		$result = array(
			'id'                 => $id,
			'changed_meta_count' => $changed,
			'matching_meta_keys' => array_values( array_unique( $matches ) ),
			'snippet'            => $this->format_custom_code_snippet( get_post( $id ) ),
		);

		$this->log_activity( 'sanitize_elementor_custom_code', $request, $result );

		return $this->success_response( $result );
	}

	/**
	 * Set the post status of a snippet.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @param string          $status Desired status.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	private function set_custom_code_status( $request, $status ) {
		$id   = absint( $request->get_param( 'id' ) );
		$post = get_post( $id );

		if ( ! $post || $this->custom_code_cpt !== $post->post_type ) {
			$this->log_activity( 'set_elementor_custom_code_status', $request, null, 404 );
			return $this->error_response( 'not_found', __( 'Custom Code snippet not found.', 'site-pilot-ai' ), 404 );
		}

		$status = $this->validate_post_status( $status, array( 'publish', 'draft' ) );
		$update = wp_update_post(
			array(
				'ID'          => $id,
				'post_status' => $status,
			),
			true
		);

		if ( is_wp_error( $update ) ) {
			$this->log_activity( 'set_elementor_custom_code_status', $request, null, 400 );
			return $this->error_response( $update->get_error_code(), $update->get_error_message(), 400 );
		}

		$data = array(
			'id'      => $id,
			'status'  => $status,
			'snippet' => $this->format_custom_code_snippet( get_post( $id ) ),
		);

		$this->log_activity( 'set_elementor_custom_code_status', $request, $data );

		return $this->success_response( $data );
	}

	/**
	 * Format a Custom Code snippet post for API response.
	 *
	 * @param WP_Post $post Post object.
	 * @return array Formatted snippet.
	 */
	private function format_custom_code_snippet( $post ) {
		$meta = get_post_meta( $post->ID );

		$matching_meta_keys = array();
		foreach ( $meta as $key => $values ) {
			if ( ! is_array( $values ) ) {
				continue;
			}

			foreach ( $values as $value ) {
				if ( is_string( $value ) && $this->contains_wrapper_html_tags( $value ) ) {
					$matching_meta_keys[] = $key;
					break;
				}
			}
		}

		$debug_meta = array();
		foreach ( $meta as $key => $values ) {
			if ( ! is_array( $values ) || empty( $values ) ) {
				continue;
			}

			if ( preg_match( '/(code|snippet|location|condition)/i', (string) $key ) ) {
				$first = $values[0];
				if ( is_string( $first ) ) {
					$debug_meta[ $key ] = substr( $first, 0, 200 );
				} else {
					$debug_meta[ $key ] = $first;
				}
			}
		}

		return array(
			'id'                => (int) $post->ID,
			'title'             => (string) $post->post_title,
			'status'            => (string) $post->post_status,
			'modified_gmt'       => (string) $post->post_modified_gmt,
			'has_wrapper_tags'   => ! empty( $matching_meta_keys ),
			'matching_meta_keys' => array_values( array_unique( $matching_meta_keys ) ),
			'debug_meta_excerpt' => $debug_meta,
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
			'elementor_data' => $this->get_elementor_payload_from_request( $request ),
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
		$data        = array();

		if ( $request->has_param( 'title' ) ) {
			$data['title'] = $request->get_param( 'title' );
		}

		if ( $this->has_elementor_payload_in_request( $request ) ) {
			$data['elementor_data'] = $this->get_elementor_payload_from_request( $request );
		}

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
			'elementor_data'  => $this->get_elementor_payload_from_request( $request ),
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
			'is_archetype' => true,
		);

		if ( $request->has_param( 'title' ) ) {
			$data['title'] = $request->get_param( 'title' );
		}

		if ( $this->has_elementor_payload_in_request( $request ) ) {
			$data['elementor_data'] = $this->get_elementor_payload_from_request( $request );
		}

		foreach ( array( 'archetype_scope', 'archetype_class', 'archetype_style' ) as $field ) {
			if ( $request->has_param( $field ) ) {
				$data[ $field ] = $request->get_param( $field );
			}
		}

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
			return $this->error_response( 'missing_page_id', __( 'Page ID is required.', 'site-pilot-ai' ), 400 );
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
			'elementor_data' => $this->get_elementor_payload_from_request( $request ),
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
			'is_part' => true,
		);

		if ( $request->has_param( 'title' ) ) {
			$data['title'] = $request->get_param( 'title' );
		}

		if ( $this->has_elementor_payload_in_request( $request ) ) {
			$data['elementor_data'] = $this->get_elementor_payload_from_request( $request );
		}

		foreach ( array( 'part_kind', 'part_style', 'part_tags' ) as $field ) {
			if ( $request->has_param( $field ) ) {
				$data[ $field ] = $request->get_param( $field );
			}
		}

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
			return $this->error_response( 'missing_params', __( 'page_id and element_id are required.', 'site-pilot-ai' ), 400 );
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
	 * Resolve Elementor payload from request params.
	 *
	 * Shared-host WAFs often reject large nested JSON POST bodies. For those
	 * environments we accept `elementor_data_base64` in addition to the normal
	 * `elementor_data` field so clients can safely submit form-encoded payloads.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return array|string|null
	 */
	private function get_elementor_payload_from_request( $request ) {
		$base64 = $request->get_param( 'elementor_data_base64' );
		if ( is_string( $base64 ) && '' !== $base64 ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			$decoded = base64_decode( $base64, true );
			if ( false !== $decoded && '' !== $decoded ) {
				return $decoded;
			}
		}

		return $request->get_param( 'elementor_data' );
	}

	/**
	 * Check whether the request explicitly includes Elementor payload data.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool
	 */
	private function has_elementor_payload_in_request( $request ) {
		return $request->has_param( 'elementor_data_base64' ) || $request->has_param( 'elementor_data' );
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
			return $this->error_response( 'missing_page_id', __( 'Page ID is required.', 'site-pilot-ai' ), 400 );
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
		$widget_name = $request->get_param( 'widget' );
		$result      = $this->elementor_pro->get_available_widgets( $widget_name );

		if ( is_wp_error( $result ) ) {
			$this->log_activity( 'get_widgets', $request, null, 404 );
			return $this->error_response( $result->get_error_code(), $result->get_error_message(), 404 );
		}

		$this->log_activity( 'get_widgets', $request );

		// Single widget returns the widget object directly.
		if ( $widget_name ) {
			return $this->success_response( $result );
		}

		return $this->success_response( array(
			'widgets' => $result,
			'total'   => count( $result ),
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

	/**
	 * Set Elementor global settings (colors, fonts, etc.).
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function set_globals( $request ) {
		$this->log_activity( 'set_elementor_globals', $request );

		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return $this->error_response( 'elementor_not_active', __( 'Elementor is not active.', 'site-pilot-ai' ), 400 );
		}

		$params = $request->get_json_params();
		if ( empty( $params ) ) {
			$params = $request->get_params();
		}

		if ( empty( $params ) || ! is_array( $params ) ) {
			return $this->error_response( 'missing_params', __( 'Globals data is required.', 'site-pilot-ai' ), 400 );
		}

		// Get the active kit
		$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
		if ( ! $kit || ! $kit->get_id() ) {
			return $this->error_response( 'no_kit', __( 'No active Elementor kit found.', 'site-pilot-ai' ), 500 );
		}

		$kit_id       = $kit->get_id();
		$existing     = get_post_meta( $kit_id, '_elementor_page_settings', true );
		if ( ! is_array( $existing ) ) {
			$existing = array();
		}

		// Merge provided settings
		$updated = array_replace_recursive( $existing, $params );

		update_post_meta( $kit_id, '_elementor_page_settings', $updated );

		// Clear Elementor caches
		if ( method_exists( \Elementor\Plugin::$instance->files_manager, 'clear_cache' ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}

		return $this->success_response( array(
			'kit_id'   => $kit_id,
			'settings' => $updated,
			'message'  => __( 'Elementor globals updated.', 'site-pilot-ai' ),
		) );
	}

	/**
	 * Build a page from section blueprints.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function build_page( $request ) {
		$this->log_activity( 'build_page', $request );

		$title    = $request->get_param( 'title' );
		$sections = $request->get_param( 'sections' );
		$status   = $request->get_param( 'status' ) ?: 'draft';

		$builder = new Spai_Page_Builder();
		$result  = $builder->build( $title, $sections, $status );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result, 201 );
	}

	/**
	 * List available blueprint types with parameter schemas.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function list_blueprints( $request ) {
		$this->log_activity( 'list_blueprints', $request );

		$catalog = Spai_Page_Builder::get_blueprint_catalog();

		return $this->success_response( array(
			'count'      => count( $catalog ),
			'blueprints' => $catalog,
		) );
	}

	/**
	 * Build a single section from a blueprint type.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function get_blueprint( $request ) {
		$this->log_activity( 'get_blueprint', $request );

		$type   = $request->get_param( 'type' );
		$params = $request->get_params();

		if ( empty( $type ) ) {
			return new WP_Error( 'missing_type', 'Blueprint type is required.', array( 'status' => 400 ) );
		}

		$builder = new Spai_Page_Builder();
		$result  = $builder->build_single_section( $type, $params );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result );
	}

	/**
	 * Save a section from a live page as a reusable Elementor template.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function save_section_as_template( $request ) {
		$this->log_activity( 'save_section_as_template', $request );

		$page_id    = (int) $request->get_param( 'page_id' );
		$element_id = $request->get_param( 'element_id' );
		$title      = $request->get_param( 'title' );

		if ( ! $page_id || ! $element_id ) {
			return new WP_Error( 'missing_params', 'page_id and element_id are required.', array( 'status' => 400 ) );
		}

		$result = $this->elementor_pro->save_section_as_template( $page_id, $element_id, $title );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result, 201 );
	}
}
