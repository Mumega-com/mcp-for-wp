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

		// Bulk get Elementor data (register BEFORE /elementor/{id} to avoid route conflicts).
		register_rest_route(
			$this->namespace,
			'/elementor/bulk',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_elementor_data_bulk' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'ids' => array(
							'description' => __( 'Comma-separated page IDs (max 25).', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
						),
					),
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
						'dry_run' => array(
							'description' => __( 'If true, validate only — no changes are saved.', 'site-pilot-ai' ),
							'type'        => 'boolean',
							'default'     => false,
						),
					),
				),
			)
		);

		// Get Elementor summary (lightweight).
		register_rest_route(
			$this->namespace,
			'/elementor/(?P<id>\d+)/summary',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_elementor_summary' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Get rendered HTML preview.
		register_rest_route(
			$this->namespace,
			'/elementor/(?P<id>\d+)/preview',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_elementor_preview' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Edit a single Elementor element (surgical patch).
		register_rest_route(
			$this->namespace,
			'/elementor/(?P<id>\d+)/edit-section',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'edit_section' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'element_id'      => array(
							'description' => __( 'Elementor element ID to edit.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
						'section_index'   => array(
							'description' => __( 'Top-level section index (0-based).', 'site-pilot-ai' ),
							'type'        => 'integer',
						),
						'find'            => array(
							'description' => __( 'Search criteria: {widgetType, "settings.key": value}.', 'site-pilot-ai' ),
							'type'        => 'object',
						),
						'settings'        => array(
							'description' => __( 'Settings to merge into the element.', 'site-pilot-ai' ),
							'type'        => 'object',
						),
						'delete_settings' => array(
							'description' => __( 'Setting keys to remove from the element.', 'site-pilot-ai' ),
							'type'        => 'array',
							'items'       => array( 'type' => 'string' ),
						),
					),
				),
			)
		);

		// Edit a single widget's settings by widget ID (lightweight patch).
		register_rest_route(
			$this->namespace,
			'/elementor/(?P<id>\d+)/edit-widget',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'edit_widget' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'widget_id'       => array(
							'description' => __( 'Elementor widget element ID (8-char alphanumeric).', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
						),
						'settings'        => array(
							'description' => __( 'Settings to merge into the widget.', 'site-pilot-ai' ),
							'type'        => 'object',
						),
						'delete_settings' => array(
							'description' => __( 'Setting keys to remove from the widget.', 'site-pilot-ai' ),
							'type'        => 'array',
							'items'       => array( 'type' => 'string' ),
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

		// Find and replace in Elementor data
		register_rest_route(
			$this->namespace,
			'/elementor/(?P<id>\d+)/find-replace',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'find_replace' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'search'  => array(
							'description' => __( 'Text to search for.', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
						),
						'replace' => array(
							'description' => __( 'Replacement text.', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
						),
					),
				),
			)
		);

		// Widget schema — list all widget types.
		register_rest_route(
			$this->namespace,
			'/elementor/widgets',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_widget_schema' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Widget schema — get schema for a specific widget type.
		register_rest_route(
			$this->namespace,
			'/elementor/widgets/(?P<type>[\w-]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_widget_schema' ),
					'permission_callback' => array( $this, 'check_permission' ),
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
						'force' => array(
							'description' => __( 'Delete existing CSS files before regenerating.', 'site-pilot-ai' ),
							'type'        => 'boolean',
							'default'     => false,
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
			'pro'  => array(
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
		$result  = $this->elementor->get_elementor_data( $page_id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result );
	}

	/**
	 * Get Elementor data for multiple pages in bulk.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function get_elementor_data_bulk( $request ) {
		$this->log_activity( 'get_elementor_bulk', $request );

		$ids_raw = $request->get_param( 'ids' );
		if ( empty( $ids_raw ) ) {
			return $this->error_response( 'missing_param', __( 'ids parameter is required.', 'site-pilot-ai' ), 400 );
		}

		$page_ids = array_filter( array_map( 'absint', explode( ',', $ids_raw ) ) );

		if ( count( $page_ids ) > 25 ) {
			return $this->error_response( 'too_many_ids', __( 'Maximum 25 page IDs per request.', 'site-pilot-ai' ), 400 );
		}

		if ( empty( $page_ids ) ) {
			return $this->error_response( 'invalid_ids', __( 'No valid page IDs provided.', 'site-pilot-ai' ), 400 );
		}

		$result = $this->elementor->get_elementor_data_bulk( $page_ids );

		return $this->success_response( $result );
	}

	/**
	 * Get lightweight Elementor summary for page.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function get_elementor_summary( $request ) {
		$this->log_activity( 'get_elementor_summary', $request );

		$page_id = $request->get_param( 'id' );
		$result  = $this->elementor->get_elementor_summary( $page_id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result );
	}

	/**
	 * Get rendered HTML preview of Elementor content.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function get_elementor_preview( $request ) {
		$this->log_activity( 'get_elementor_preview', $request );

		$page_id = $request->get_param( 'id' );
		$result  = $this->elementor->get_rendered_content( $page_id );

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
		$dry_run = (bool) $request->get_param( 'dry_run' );
		$data    = array(
			'elementor_data' => $request->get_param( 'elementor_data' ),
			'elementor_json' => $request->get_param( 'elementor_json' ),
		);

		$result = $this->elementor->set_elementor_data( $page_id, $data, $dry_run );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result );
	}

	/**
	 * Edit a single Elementor element (surgical patch).
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function edit_section( $request ) {
		$this->log_activity( 'edit_elementor_section', $request );

		$page_id = absint( $request->get_param( 'id' ) );
		$args    = array(
			'element_id'      => $request->get_param( 'element_id' ),
			'section_index'   => $request->get_param( 'section_index' ),
			'find'            => $request->get_param( 'find' ),
			'settings'        => $request->get_param( 'settings' ),
			'delete_settings' => $request->get_param( 'delete_settings' ),
		);

		$result = $this->elementor->edit_section( $page_id, $args );

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
	/**
	 * Edit a single widget's settings by widget ID.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function edit_widget( $request ) {
		$this->log_activity( 'edit_elementor_widget', $request );

		$page_id         = absint( $request->get_param( 'id' ) );
		$widget_id       = (string) $request->get_param( 'widget_id' );
		$settings        = $request->get_param( 'settings' );
		$delete_settings = $request->get_param( 'delete_settings' );

		$result = $this->elementor->edit_widget(
			$page_id,
			$widget_id,
			is_array( $settings ) ? $settings : array(),
			is_array( $delete_settings ) ? $delete_settings : array()
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result );
	}

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
	 * Get widget schema (list all or schema for a specific type).
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function get_widget_schema( $request ) {
		$this->log_activity( 'get_widget_schema', $request );

		$widget_type = $request->get_param( 'type' );
		$result      = $this->elementor->get_widget_schema( $widget_type ? (string) $widget_type : '' );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result );
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
		$force   = (bool) $request->get_param( 'force' );
		$result  = $this->elementor->regenerate_css( $page_id ? absint( $page_id ) : null, $force );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success_response( $result );
	}

	/**
	 * Find and replace text in Elementor data.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function find_replace( $request ) {
		$this->log_activity( 'elementor_find_replace', $request );

		$page_id = absint( $request->get_param( 'id' ) );
		$search  = (string) $request->get_param( 'search' );
		$replace = (string) $request->get_param( 'replace' );

		// Validate the post.
		$post = $this->elementor->validate_post( $page_id );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		// Get current Elementor data.
		$raw = get_post_meta( $page_id, '_elementor_data', true );
		if ( empty( $raw ) ) {
			return $this->error_response(
				'no_elementor_data',
				__( 'No Elementor data found for this post.', 'site-pilot-ai' ),
				404
			);
		}

		// Perform replacement and count occurrences.
		$updated = str_replace( $search, $replace, $raw, $count );

		if ( 0 === $count ) {
			return $this->success_response(
				array(
					'replacements' => 0,
					'message'      => __( 'Search text not found in Elementor data.', 'site-pilot-ai' ),
				)
			);
		}

		// Save updated data.
		update_post_meta( $page_id, '_elementor_data', wp_slash( $updated ) );

		// Clear Elementor CSS cache for this post.
		if ( class_exists( '\Elementor\Plugin' ) ) {
			$post_css = \Elementor\Core\Files\CSS\Post::create( $page_id );
			if ( $post_css ) {
				$post_css->delete();
			}
		}

		return $this->success_response(
			array(
				'replacements' => $count,
				'post_id'      => $page_id,
				'search'       => $search,
				'replace'      => $replace,
			)
		);
	}
}
