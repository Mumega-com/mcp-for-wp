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
}
