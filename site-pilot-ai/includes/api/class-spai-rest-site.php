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

		// Introspection (self-describing API / MCP metadata).
		register_rest_route(
			$this->namespace,
			'/introspect',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_introspect' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Settings (GET and PUT)
		register_rest_route(
			$this->namespace,
			'/settings',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Options (front page, blog page, reading settings)
		register_rest_route(
			$this->namespace,
			'/options',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_options' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_options' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Favicon (site icon)
		register_rest_route(
			$this->namespace,
			'/favicon',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_favicon' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_favicon' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_favicon' ),
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

		// Content search (posts/pages)
		register_rest_route(
			$this->namespace,
			'/search',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'search_content' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'query'    => array(
							'description' => __( 'Search query string.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
						'q'        => array(
							'description' => __( 'Alias for query string.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
						'type'     => array(
							'description' => __( 'Content type filter (post, page, or any).', 'site-pilot-ai' ),
							'type'        => 'string',
							'default'     => 'any',
						),
						'status'   => array(
							'description' => __( 'Post status filter.', 'site-pilot-ai' ),
							'type'        => 'string',
							'default'     => 'publish',
						),
						'per_page' => array(
							'description' => __( 'Results per page.', 'site-pilot-ai' ),
							'type'        => 'integer',
							'default'     => 10,
							'minimum'     => 1,
							'maximum'     => 50,
						),
						'page'     => array(
							'description' => __( 'Current page.', 'site-pilot-ai' ),
							'type'        => 'integer',
							'default'     => 1,
							'minimum'     => 1,
						),
					),
				),
			)
		);

		// Content fetch by ID or URL
		register_rest_route(
			$this->namespace,
			'/fetch',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'fetch_content' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'id'              => array(
							'description' => __( 'Post or page ID.', 'site-pilot-ai' ),
							'type'        => 'integer',
						),
						'url'             => array(
							'description' => __( 'Canonical post/page URL.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
						'type'            => array(
							'description' => __( 'Expected content type (post, page, or any).', 'site-pilot-ai' ),
							'type'        => 'string',
							'default'     => 'any',
						),
						'include_content' => array(
							'description' => __( 'Include full content body in response.', 'site-pilot-ai' ),
							'type'        => 'boolean',
							'default'     => true,
						),
					),
				),
			)
		);

		// Categories
		register_rest_route(
			$this->namespace,
			'/categories',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_categories' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'per_page' => array(
							'description' => __( 'Results per page.', 'site-pilot-ai' ),
							'type'        => 'integer',
							'default'     => 100,
							'minimum'     => 1,
							'maximum'     => 200,
						),
						'search'   => array(
							'description' => __( 'Search term.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
						'parent'   => array(
							'description' => __( 'Parent category ID.', 'site-pilot-ai' ),
							'type'        => 'integer',
						),
					),
				),
			)
		);

		// Tags
		register_rest_route(
			$this->namespace,
			'/tags',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_tags' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'per_page' => array(
							'description' => __( 'Results per page.', 'site-pilot-ai' ),
							'type'        => 'integer',
							'default'     => 100,
							'minimum'     => 1,
							'maximum'     => 200,
						),
						'search'   => array(
							'description' => __( 'Search term.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
					),
				),
			)
		);

		// OAuth token issuance (client credentials)
		register_rest_route(
			$this->namespace,
			'/oauth/token',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'issue_oauth_token' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'grant_type'    => array(
							'description' => __( 'OAuth grant type.', 'site-pilot-ai' ),
							'type'        => 'string',
							'default'     => 'client_credentials',
						),
						'client_id'     => array(
							'description' => __( 'OAuth client ID.', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
						),
						'client_secret' => array(
							'description' => __( 'OAuth client secret.', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
						),
						'scope'         => array(
							'description' => __( 'Space-separated scopes (read write admin).', 'site-pilot-ai' ),
							'type'        => 'string',
						),
					),
				),
			)
		);

		// Rate limit status
		register_rest_route(
			$this->namespace,
			'/rate-limit',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_rate_limit_status' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_rate_limit_settings' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'enabled'             => array(
							'description' => __( 'Enable or disable rate limiting.', 'site-pilot-ai' ),
							'type'        => 'boolean',
						),
						'requests_per_minute' => array(
							'description' => __( 'Requests allowed per minute.', 'site-pilot-ai' ),
							'type'        => 'integer',
							'minimum'     => 1,
						),
						'requests_per_hour'   => array(
							'description' => __( 'Requests allowed per hour.', 'site-pilot-ai' ),
							'type'        => 'integer',
							'minimum'     => 1,
						),
						'burst_limit'         => array(
							'description' => __( 'Requests allowed in short burst window.', 'site-pilot-ai' ),
							'type'        => 'integer',
							'minimum'     => 1,
						),
						'whitelist'           => array(
							'description' => __( 'Identifiers to bypass rate limiting.', 'site-pilot-ai' ),
							'type'        => 'array',
							'items'       => array(
								'type' => 'string',
							),
						),
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/rate-limit/reset',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'reset_rate_limit' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'identifier' => array(
							'description' => __( 'Rate-limit identifier to reset (for example: key:<id> or IP).', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
						),
					),
				),
			)
		);

		// Scoped API keys (admin scope/capability required)
		register_rest_route(
			$this->namespace,
			'/api-keys',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_api_keys' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'include_revoked' => array(
							'description' => __( 'Include revoked keys.', 'site-pilot-ai' ),
							'type'        => 'boolean',
							'default'     => false,
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_api_key' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'label'  => array(
							'description' => __( 'Key label.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
						'scopes' => array(
							'description' => __( 'Scopes for key (read, write, admin).', 'site-pilot-ai' ),
							'type'        => 'array',
							'items'       => array(
								'type' => 'string',
							),
						),
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/api-keys/(?P<id>[a-z0-9\\-]+)',
			array(
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'revoke_api_key' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Self-update check and trigger (#87)
		register_rest_route(
			$this->namespace,
			'/update',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'check_update' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'trigger_update' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Custom CSS (Additional CSS from Customizer).
		register_rest_route(
			$this->namespace,
			'/custom-css',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_custom_css' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'set_custom_css' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'css'  => array(
							'description' => __( 'CSS code to set or append.', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
						),
						'mode' => array(
							'description' => __( 'How to apply: "replace" overwrites all CSS, "append" adds to existing.', 'site-pilot-ai' ),
							'type'        => 'string',
							'default'     => 'append',
							'enum'        => array( 'replace', 'append' ),
						),
					),
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
	 * Get API/MCP introspection data to help clients self-configure.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_introspect( $request ) {
		$this->log_activity( 'introspect', $request );

		if ( ! class_exists( 'Spai_REST_MCP' ) ) {
			return $this->success_response(
				array(
					'plugin'  => array(
						'name'    => 'Site Pilot AI',
						'version' => defined( 'SPAI_VERSION' ) ? SPAI_VERSION : null,
					),
					'message' => 'MCP controller not available.',
				)
			);
		}

		$mcp = new Spai_REST_MCP();
		if ( ! method_exists( $mcp, 'get_introspection_data' ) ) {
			return $this->success_response(
				array(
					'plugin'  => array(
						'name'    => 'Site Pilot AI',
						'version' => defined( 'SPAI_VERSION' ) ? SPAI_VERSION : null,
					),
					'message' => 'Introspection is not supported in this version.',
				)
			);
		}

		return $this->success_response( $mcp->get_introspection_data() );
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

	/**
	 * Search posts/pages by query string.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function search_content( $request ) {
		$this->log_activity( 'search_content', $request );

		if ( ! class_exists( 'WP_Query' ) ) {
			return $this->error_response(
				'search_unavailable',
				__( 'Search is not available in this environment.', 'site-pilot-ai' ),
				500
			);
		}

		$query = (string) $request->get_param( 'query' );
		if ( '' === trim( $query ) ) {
			$query = (string) $request->get_param( 'q' );
		}
		$query = sanitize_text_field( $query );

		if ( '' === $query ) {
			return $this->error_response(
				'missing_query',
				__( 'Search query is required.', 'site-pilot-ai' ),
				400
			);
		}

		$type = sanitize_key( (string) $request->get_param( 'type' ) );
		if ( ! in_array( $type, array( 'post', 'page', 'any' ), true ) ) {
			$type = 'any';
		}

		$status = sanitize_key( (string) $request->get_param( 'status' ) );
		if ( '' === $status ) {
			$status = 'publish';
		}

		$per_page = min( 50, max( 1, absint( $request->get_param( 'per_page' ) ?: 10 ) ) );
		$page     = max( 1, absint( $request->get_param( 'page' ) ?: 1 ) );

		$post_types = 'any' === $type ? array( 'post', 'page' ) : array( $type );

		$search_query = new WP_Query( array(
			'post_type'           => $post_types,
			'post_status'         => $status,
			's'                   => $query,
			'posts_per_page'      => $per_page,
			'paged'               => $page,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => false,
		) );

		$items = array();
		foreach ( $search_query->posts as $post ) {
			if ( $post instanceof WP_Post ) {
				$items[] = $this->format_content_item( $post, false );
			}
		}

		return $this->success_response( array(
			'query'      => $query,
			'type'       => $type,
			'status'     => $status,
			'items'      => $items,
			'pagination' => array(
				'page'        => $page,
				'per_page'    => $per_page,
				'total'       => (int) $search_query->found_posts,
				'total_pages' => (int) $search_query->max_num_pages,
			),
		) );
	}

	/**
	 * Fetch a single post/page by ID or URL.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function fetch_content( $request ) {
		$this->log_activity( 'fetch_content', $request );

		$id  = absint( $request->get_param( 'id' ) );
		$url = esc_url_raw( (string) $request->get_param( 'url' ) );

		if ( 0 === $id && '' === $url ) {
			return $this->error_response(
				'missing_identifier',
				__( 'Provide either id or url to fetch content.', 'site-pilot-ai' ),
				400
			);
		}

		$type = sanitize_key( (string) $request->get_param( 'type' ) );
		if ( ! in_array( $type, array( 'post', 'page', 'any' ), true ) ) {
			$type = 'any';
		}

		$post = null;
		if ( $id > 0 ) {
			$post = get_post( $id );
		} elseif ( '' !== $url ) {
			$resolved_id = $this->resolve_content_id_from_url( $url, $type );
			if ( $resolved_id > 0 ) {
				$post = get_post( $resolved_id );
			}
		}

		if ( ! $post instanceof WP_Post ) {
			return $this->error_response(
				'not_found',
				__( 'Content not found.', 'site-pilot-ai' ),
				404
			);
		}

		if ( 'any' !== $type && $type !== $post->post_type ) {
			return $this->error_response(
				'not_found',
				__( 'Content not found for the requested type.', 'site-pilot-ai' ),
				404
			);
		}

		$include_content = $request->get_param( 'include_content' );
		$include_content = null === $include_content ? true : (bool) $include_content;

		return $this->success_response( array(
			'item' => $this->format_content_item( $post, $include_content ),
		) );
	}

	/**
	 * Issue OAuth access token via client credentials grant.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function issue_oauth_token( $request ) {
		$this->log_activity( 'oauth_token', $request );

		$rate_limit_check = $this->check_rate_limit( 'oauth-client:' . $this->get_client_ip() );
		if ( is_wp_error( $rate_limit_check ) ) {
			return $rate_limit_check;
		}

		$oauth_settings = $this->get_oauth_settings();
		if ( empty( $oauth_settings['oauth_enabled'] ) ) {
			return $this->error_response(
				'oauth_disabled',
				__( 'OAuth token endpoint is disabled.', 'site-pilot-ai' ),
				503
			);
		}

		$grant_type = sanitize_key( (string) $request->get_param( 'grant_type' ) );
		if ( 'client_credentials' !== $grant_type ) {
			return $this->error_response(
				'unsupported_grant_type',
				__( 'Only client_credentials grant type is supported.', 'site-pilot-ai' ),
				400
			);
		}

		$client_id     = sanitize_key( (string) $request->get_param( 'client_id' ) );
		$client_secret = (string) $request->get_param( 'client_secret' );

		if ( ! $this->verify_oauth_client_credentials( $client_id, $client_secret ) ) {
			return $this->error_response(
				'invalid_client',
				__( 'Invalid OAuth client credentials.', 'site-pilot-ai' ),
				401
			);
		}

		$scope_string = (string) $request->get_param( 'scope' );
		$scopes       = $this->parse_requested_oauth_scopes( $scope_string );
		$token_data   = $this->issue_oauth_access_token( $scopes, $oauth_settings['oauth_token_ttl'] );

		return $this->success_response( $token_data, 200 );
	}

	/**
	 * Get site settings.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_settings( $request ) {
		$this->log_activity( 'get_settings', $request );

		$settings = array(
			'title'              => get_option( 'blogname' ),
			'tagline'            => get_option( 'blogdescription' ),
			'url'                => get_option( 'siteurl' ),
			'home'               => get_option( 'home' ),
			'admin_email'        => get_option( 'admin_email' ),
			'timezone'           => get_option( 'timezone_string' ) ?: 'UTC',
			'date_format'        => get_option( 'date_format' ),
			'time_format'        => get_option( 'time_format' ),
			'language'           => get_option( 'WPLANG' ) ?: 'en_US',
			'posts_per_page'     => (int) get_option( 'posts_per_page' ),
			'permalink_structure' => get_option( 'permalink_structure' ),
			'show_on_front'      => get_option( 'show_on_front' ),
			'page_on_front'      => (int) get_option( 'page_on_front' ),
			'page_for_posts'     => (int) get_option( 'page_for_posts' ),
		);

		return $this->success_response( $settings );
	}

	/**
	 * Update site settings.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function update_settings( $request ) {
		$this->log_activity( 'update_settings', $request );

		$params = $request->get_json_params();

		if ( empty( $params ) ) {
			return $this->error_response(
				'missing_settings',
				__( 'Settings data is required.', 'site-pilot-ai' ),
				400
			);
		}

		$updated = array();

		// Allowed settings to update
		$allowed = array(
			'title'          => 'blogname',
			'tagline'        => 'blogdescription',
			'timezone'       => 'timezone_string',
			'date_format'    => 'date_format',
			'time_format'    => 'time_format',
			'admin_email'    => 'admin_email',
			'posts_per_page' => 'posts_per_page',
		);

		foreach ( $allowed as $key => $option ) {
			if ( isset( $params[ $key ] ) ) {
				$value = $params[ $key ];

				// Sanitize based on type
				if ( 'admin_email' === $key ) {
					$value = sanitize_email( $value );
					if ( ! is_email( $value ) ) {
						continue;
					}
				} elseif ( 'posts_per_page' === $key ) {
					$value = absint( $value );
				} else {
					$value = sanitize_text_field( $value );
				}

				update_option( $option, $value );
				$updated[ $key ] = $value;
			}
		}

		if ( empty( $updated ) ) {
			return $this->error_response(
				'no_valid_settings',
				__( 'No valid settings provided to update.', 'site-pilot-ai' ),
				400
			);
		}

		return $this->success_response( array(
			'updated'  => $updated,
			'settings' => $this->get_settings( $request )->get_data(),
		) );
	}

	/**
	 * Get WordPress options (front page, reading settings).
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_options( $request ) {
		$this->log_activity( 'get_options', $request );

		$options = array(
			'show_on_front'  => get_option( 'show_on_front' ),
			'page_on_front'  => (int) get_option( 'page_on_front' ),
			'page_for_posts' => (int) get_option( 'page_for_posts' ),
			'posts_per_page' => (int) get_option( 'posts_per_page' ),
			'posts_per_rss'  => (int) get_option( 'posts_per_rss' ),
			'blog_public'    => (int) get_option( 'blog_public' ),
		);

		// Include page names for context
		if ( $options['page_on_front'] ) {
			$page = get_post( $options['page_on_front'] );
			$options['page_on_front_title'] = $page ? $page->post_title : null;
		}

		if ( $options['page_for_posts'] ) {
			$page = get_post( $options['page_for_posts'] );
			$options['page_for_posts_title'] = $page ? $page->post_title : null;
		}

		return $this->success_response( $options );
	}

	/**
	 * Update WordPress options.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function update_options( $request ) {
		$this->log_activity( 'update_options', $request );

		$params = $request->get_json_params();

		if ( empty( $params ) ) {
			return $this->error_response(
				'missing_options',
				__( 'Options data is required.', 'site-pilot-ai' ),
				400
			);
		}

		$updated = array();

		// show_on_front: 'posts' or 'page'
		if ( isset( $params['show_on_front'] ) ) {
			$value = sanitize_key( $params['show_on_front'] );
			if ( in_array( $value, array( 'posts', 'page' ), true ) ) {
				update_option( 'show_on_front', $value );
				$updated['show_on_front'] = $value;
			}
		}

		// page_on_front: ID of front page
		if ( isset( $params['page_on_front'] ) ) {
			$page_id = absint( $params['page_on_front'] );
			if ( 0 === $page_id || get_post( $page_id ) ) {
				update_option( 'page_on_front', $page_id );
				$updated['page_on_front'] = $page_id;
			}
		}

		// page_for_posts: ID of posts page
		if ( isset( $params['page_for_posts'] ) ) {
			$page_id = absint( $params['page_for_posts'] );
			if ( 0 === $page_id || get_post( $page_id ) ) {
				update_option( 'page_for_posts', $page_id );
				$updated['page_for_posts'] = $page_id;
			}
		}

		// posts_per_page
		if ( isset( $params['posts_per_page'] ) ) {
			$value = absint( $params['posts_per_page'] );
			if ( $value > 0 ) {
				update_option( 'posts_per_page', $value );
				$updated['posts_per_page'] = $value;
			}
		}

		// posts_per_rss
		if ( isset( $params['posts_per_rss'] ) ) {
			$value = absint( $params['posts_per_rss'] );
			if ( $value > 0 ) {
				update_option( 'posts_per_rss', $value );
				$updated['posts_per_rss'] = $value;
			}
		}

		// blog_public (search engine visibility)
		if ( isset( $params['blog_public'] ) ) {
			$value = $params['blog_public'] ? 1 : 0;
			update_option( 'blog_public', $value );
			$updated['blog_public'] = $value;
		}

		if ( empty( $updated ) ) {
			return $this->error_response(
				'no_valid_options',
				__( 'No valid options provided to update.', 'site-pilot-ai' ),
				400
			);
		}

		return $this->success_response( array(
			'updated' => $updated,
			'options' => $this->get_options( $request )->get_data(),
		) );
	}

	/**
	 * Get site favicon (site icon).
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_favicon( $request ) {
		$this->log_activity( 'get_favicon', $request );

		$site_icon_id = get_option( 'site_icon' );

		if ( ! $site_icon_id ) {
			return $this->success_response( array(
				'has_favicon' => false,
				'id'          => null,
				'url'         => null,
				'sizes'       => array(),
			) );
		}

		$sizes = array();
		$icon_sizes = array( 32, 180, 192, 270, 512 );

		foreach ( $icon_sizes as $size ) {
			$icon_url = get_site_icon_url( $size );
			if ( $icon_url ) {
				$sizes[ $size ] = $icon_url;
			}
		}

		return $this->success_response( array(
			'has_favicon' => true,
			'id'          => (int) $site_icon_id,
			'url'         => get_site_icon_url( 512 ),
			'sizes'       => $sizes,
		) );
	}

	/**
	 * Update site favicon.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function update_favicon( $request ) {
		$this->log_activity( 'update_favicon', $request );

		$params = $request->get_json_params();

		// Option 1: Set by media ID
		if ( ! empty( $params['id'] ) ) {
			$attachment_id = absint( $params['id'] );
			$attachment    = get_post( $attachment_id );

			if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
				return $this->error_response(
					'invalid_attachment',
					__( 'Invalid media ID.', 'site-pilot-ai' ),
					400
				);
			}

			// Verify it's an image
			if ( ! wp_attachment_is_image( $attachment_id ) ) {
				return $this->error_response(
					'not_image',
					__( 'Attachment must be an image.', 'site-pilot-ai' ),
					400
				);
			}

			update_option( 'site_icon', $attachment_id );

			return $this->success_response( array(
				'updated' => true,
				'favicon' => $this->get_favicon( $request )->get_data(),
			) );
		}

		// Option 2: Upload from URL
		if ( ! empty( $params['url'] ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			$url = esc_url_raw( $params['url'] );

			// SSRF protection: block internal/private URLs.
			if ( class_exists( 'Spai_Security' ) ) {
				$ssrf_check = Spai_Security::validate_external_url( $url );
				if ( is_wp_error( $ssrf_check ) ) {
					return $ssrf_check;
				}
			}

			// Download and sideload the image
			$tmp = download_url( $url );

			if ( is_wp_error( $tmp ) ) {
				return $this->error_response(
					'download_failed',
					$tmp->get_error_message(),
					400
				);
			}

			$file_array = array(
				'name'     => basename( wp_parse_url( $url, PHP_URL_PATH ) ),
				'tmp_name' => $tmp,
			);

			$attachment_id = media_handle_sideload( $file_array, 0, __( 'Site Icon', 'site-pilot-ai' ) );

			if ( is_wp_error( $attachment_id ) ) {
				@unlink( $tmp );
				return $this->error_response(
					'upload_failed',
					$attachment_id->get_error_message(),
					400
				);
			}

			update_option( 'site_icon', $attachment_id );

			return $this->success_response( array(
				'updated'  => true,
				'uploaded' => true,
				'favicon'  => $this->get_favicon( $request )->get_data(),
			), 201 );
		}

		return $this->error_response(
			'missing_param',
			__( 'Provide either "id" (media ID) or "url" (image URL).', 'site-pilot-ai' ),
			400
		);
	}

	/**
	 * Delete (remove) site favicon.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function delete_favicon( $request ) {
		$this->log_activity( 'delete_favicon', $request );

		$site_icon_id = get_option( 'site_icon' );

		if ( ! $site_icon_id ) {
			return $this->success_response( array(
				'deleted' => false,
				'message' => __( 'No favicon was set.', 'site-pilot-ai' ),
			) );
		}

		delete_option( 'site_icon' );

		return $this->success_response( array(
			'deleted'     => true,
			'previous_id' => (int) $site_icon_id,
		) );
	}

	/**
	 * Get rate limit status for current client.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_rate_limit_status( $request ) {
		$this->log_activity( 'rate_limit_status', $request );

		if ( ! class_exists( 'Spai_Rate_Limiter' ) ) {
			return $this->success_response( array(
				'enabled' => false,
				'message' => __( 'Rate limiting is not available.', 'site-pilot-ai' ),
			) );
		}

		$limiter  = Spai_Rate_Limiter::get_instance();
		$settings = $limiter->get_settings();
		$usage    = $limiter->get_usage();

		return $this->success_response( array(
			'enabled'  => $settings['enabled'],
			'limits'   => array(
				'burst'      => $settings['burst_limit'],
				'per_minute' => $settings['requests_per_minute'],
				'per_hour'   => $settings['requests_per_hour'],
			),
			'usage'    => $usage,
		) );
	}

	/**
	 * Update rate limit settings.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function update_rate_limit_settings( $request ) {
		$this->log_activity( 'update_rate_limit_settings', $request );

		if ( ! $this->can_manage_api_keys() ) {
			return $this->error_response(
				'forbidden',
				__( 'You do not have permission to manage rate limiting.', 'site-pilot-ai' ),
				403
			);
		}

		if ( ! class_exists( 'Spai_Rate_Limiter' ) ) {
			return $this->error_response(
				'rate_limiter_unavailable',
				__( 'Rate limiting is not available.', 'site-pilot-ai' ),
				500
			);
		}

		$params = $request->get_json_params();
		if ( ! is_array( $params ) || empty( $params ) ) {
			$params = $request->get_params();
		}

		$allowed  = array( 'enabled', 'requests_per_minute', 'requests_per_hour', 'burst_limit', 'whitelist' );
		$settings = array();
		foreach ( $allowed as $key ) {
			if ( array_key_exists( $key, $params ) ) {
				$settings[ $key ] = $params[ $key ];
			}
		}

		if ( empty( $settings ) ) {
			return $this->error_response(
				'missing_settings',
				__( 'No rate-limit settings provided.', 'site-pilot-ai' ),
				400
			);
		}

		$limiter = Spai_Rate_Limiter::get_instance();
		$updated = $limiter->update_settings( $settings );

		if ( ! $updated ) {
			return $this->error_response(
				'update_failed',
				__( 'Failed to update rate-limit settings.', 'site-pilot-ai' ),
				500
			);
		}

		return $this->success_response( array(
			'updated'  => true,
			'settings' => $limiter->get_settings(),
		) );
	}

	/**
	 * Reset rate-limit counters for an identifier.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function reset_rate_limit( $request ) {
		$this->log_activity( 'reset_rate_limit', $request );

		if ( ! $this->can_manage_api_keys() ) {
			return $this->error_response(
				'forbidden',
				__( 'You do not have permission to reset rate limits.', 'site-pilot-ai' ),
				403
			);
		}

		if ( ! class_exists( 'Spai_Rate_Limiter' ) ) {
			return $this->error_response(
				'rate_limiter_unavailable',
				__( 'Rate limiting is not available.', 'site-pilot-ai' ),
				500
			);
		}

		$identifier = sanitize_text_field( (string) $request->get_param( 'identifier' ) );
		if ( '' === $identifier ) {
			return $this->error_response(
				'missing_identifier',
				__( 'Identifier is required.', 'site-pilot-ai' ),
				400
			);
		}

		Spai_Rate_Limiter::get_instance()->reset_limit( $identifier );

		return $this->success_response( array(
			'reset'      => true,
			'identifier' => $identifier,
		) );
	}

	/**
	 * List scoped API keys.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function list_api_keys( $request ) {
		$this->log_activity( 'list_api_keys', $request );

		if ( ! $this->can_manage_api_keys() ) {
			return $this->error_response(
				'forbidden',
				__( 'You do not have permission to manage API keys.', 'site-pilot-ai' ),
				403
			);
		}

		$include_revoked = (bool) $request->get_param( 'include_revoked' );
		$keys            = $this->list_scoped_api_keys( $include_revoked );

		return $this->success_response( array(
			'keys'  => $keys,
			'total' => count( $keys ),
		) );
	}

	/**
	 * Create scoped API key.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function create_api_key( $request ) {
		$this->log_activity( 'create_api_key', $request );

		if ( ! $this->can_manage_api_keys() ) {
			return $this->error_response(
				'forbidden',
				__( 'You do not have permission to manage API keys.', 'site-pilot-ai' ),
				403
			);
		}

		$label  = (string) $request->get_param( 'label' );
		$scopes = $request->get_param( 'scopes' );
		$scopes = is_array( $scopes ) ? $scopes : array();

		$created = $this->create_scoped_api_key( $label, $scopes );

		return $this->success_response( array(
			'api_key' => $created,
		), 201 );
	}

	/**
	 * Revoke scoped API key.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function revoke_api_key( $request ) {
		$this->log_activity( 'revoke_api_key', $request );

		if ( ! $this->can_manage_api_keys() ) {
			return $this->error_response(
				'forbidden',
				__( 'You do not have permission to manage API keys.', 'site-pilot-ai' ),
				403
			);
		}

		$key_id  = (string) $request->get_param( 'id' );
		$revoked = $this->revoke_scoped_api_key( $key_id );

		if ( ! $revoked ) {
			return $this->error_response(
				'not_found',
				__( 'API key not found or already revoked.', 'site-pilot-ai' ),
				404
			);
		}

		return $this->success_response( array(
			'revoked' => true,
			'id'      => sanitize_key( $key_id ),
		) );
	}

	/**
	 * Check for available plugin update.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function check_update( $request ) {
		$this->log_activity( 'check_update', $request );

		$current_version = defined( 'SPAI_VERSION' ) ? SPAI_VERSION : '0.0.0';

		// Clear Freemius SDK cache so it re-checks against the API.
		if ( function_exists( 'spa_fs' ) ) {
			$fs = spa_fs();
			delete_site_transient( 'update_plugins' );
			if ( is_object( $fs ) && method_exists( $fs, 'get_update' ) ) {
				$fs->get_update( false, false );
			}
		}

		// Force WordPress to check for updates.
		if ( function_exists( 'wp_update_plugins' ) ) {
			wp_update_plugins();
		}

		$update_plugins = get_site_transient( 'update_plugins' );
		$plugin_file    = defined( 'SPAI_PLUGIN_BASENAME' ) ? SPAI_PLUGIN_BASENAME : 'site-pilot-ai/site-pilot-ai.php';

		$update_available = false;
		$new_version      = null;
		$package          = null;

		if ( ! empty( $update_plugins->response[ $plugin_file ] ) ) {
			$plugin_update    = $update_plugins->response[ $plugin_file ];
			$new_version      = is_object( $plugin_update ) ? $plugin_update->new_version : null;
			$package          = is_object( $plugin_update ) ? $plugin_update->package : null;
			$update_available = ! empty( $new_version ) && version_compare( $new_version, $current_version, '>' );
		}

		return $this->success_response( array(
			'current_version'  => $current_version,
			'update_available' => $update_available,
			'new_version'      => $new_version,
			'has_package'      => ! empty( $package ),
		) );
	}

	/**
	 * Trigger plugin self-update.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function trigger_update( $request ) {
		$this->log_activity( 'trigger_update', $request );

		if ( ! $this->can_manage_api_keys() ) {
			return $this->error_response(
				'forbidden',
				__( 'You do not have permission to update the plugin.', 'site-pilot-ai' ),
				403
			);
		}

		$plugin_file = defined( 'SPAI_PLUGIN_BASENAME' ) ? SPAI_PLUGIN_BASENAME : 'site-pilot-ai/site-pilot-ai.php';

		// Clear Freemius SDK cache so it re-checks against the API.
		if ( function_exists( 'spa_fs' ) ) {
			$fs = spa_fs();
			delete_site_transient( 'update_plugins' );
			if ( is_object( $fs ) && method_exists( $fs, 'get_update' ) ) {
				$fs->get_update( false, false );
			}
		}

		// Force update check.
		if ( function_exists( 'wp_update_plugins' ) ) {
			wp_update_plugins();
		}

		$update_plugins = get_site_transient( 'update_plugins' );

		if ( empty( $update_plugins->response[ $plugin_file ] ) ) {
			return $this->success_response( array(
				'updated' => false,
				'message' => __( 'No update available.', 'site-pilot-ai' ),
				'version' => defined( 'SPAI_VERSION' ) ? SPAI_VERSION : null,
			) );
		}

		$plugin_update = $update_plugins->response[ $plugin_file ];
		if ( empty( $plugin_update->package ) ) {
			return $this->error_response(
				'no_package',
				__( 'Update package URL is not available.', 'site-pilot-ai' ),
				400
			);
		}

		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
		$result   = $upgrader->upgrade( $plugin_file );

		if ( is_wp_error( $result ) ) {
			return $this->error_response(
				'update_failed',
				$result->get_error_message(),
				500
			);
		}

		if ( true !== $result ) {
			return $this->error_response(
				'update_failed',
				__( 'Plugin update failed.', 'site-pilot-ai' ),
				500
			);
		}

		// Reactivate if needed.
		if ( ! is_plugin_active( $plugin_file ) ) {
			activate_plugin( $plugin_file );
		}

		return $this->success_response( array(
			'updated'     => true,
			'new_version' => is_object( $plugin_update ) ? $plugin_update->new_version : null,
			'message'     => __( 'Plugin updated successfully.', 'site-pilot-ai' ),
		) );
	}

	/**
	 * List categories.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function list_categories( $request ) {
		$this->log_activity( 'list_categories', $request );

		$args = array(
			'taxonomy'   => 'category',
			'number'     => min( 200, max( 1, absint( $request->get_param( 'per_page' ) ?: 100 ) ) ),
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		);

		$search = $request->get_param( 'search' );
		if ( ! empty( $search ) ) {
			$args['search'] = sanitize_text_field( $search );
		}

		$parent = $request->get_param( 'parent' );
		if ( null !== $parent ) {
			$args['parent'] = absint( $parent );
		}

		$terms = get_terms( $args );
		if ( is_wp_error( $terms ) ) {
			$terms = array();
		}

		$items = array_map( array( $this, 'format_term' ), $terms );

		return $this->success_response( array(
			'categories' => $items,
			'total'      => count( $items ),
		) );
	}

	/**
	 * List tags.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function list_tags( $request ) {
		$this->log_activity( 'list_tags', $request );

		$args = array(
			'taxonomy'   => 'post_tag',
			'number'     => min( 200, max( 1, absint( $request->get_param( 'per_page' ) ?: 100 ) ) ),
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		);

		$search = $request->get_param( 'search' );
		if ( ! empty( $search ) ) {
			$args['search'] = sanitize_text_field( $search );
		}

		$terms = get_terms( $args );
		if ( is_wp_error( $terms ) ) {
			$terms = array();
		}

		$items = array_map( array( $this, 'format_term' ), $terms );

		return $this->success_response( array(
			'tags'  => $items,
			'total' => count( $items ),
		) );
	}

	/**
	 * Format a term for API response.
	 *
	 * @param WP_Term $term Term object.
	 * @return array Formatted term.
	 */
	private function format_term( $term ) {
		return array(
			'id'          => $term->term_id,
			'name'        => $term->name,
			'slug'        => $term->slug,
			'description' => $term->description,
			'count'       => $term->count,
			'parent'      => $term->parent,
		);
	}

	/**
	 * Resolve a content ID from a canonical URL.
	 *
	 * @param string $url  Canonical URL.
	 * @param string $type Expected content type (post|page|any).
	 * @return int Resolved content ID or 0.
	 */
	private function resolve_content_id_from_url( $url, $type ) {
		$post_id = function_exists( 'url_to_postid' ) ? absint( url_to_postid( $url ) ) : 0;
		if ( $post_id > 0 ) {
			return $post_id;
		}

		$path = function_exists( 'wp_parse_url' )
			? wp_parse_url( $url, PHP_URL_PATH )
			: parse_url( $url, PHP_URL_PATH );

		if ( ! is_string( $path ) || '' === trim( $path ) ) {
			return 0;
		}

		$path = trim( $path, '/' );
		if ( '' === $path || ! function_exists( 'get_page_by_path' ) ) {
			return 0;
		}

		$post_types = 'any' === $type ? array( 'post', 'page' ) : array( $type );
		$post       = get_page_by_path( $path, OBJECT, $post_types );

		return $post instanceof WP_Post ? (int) $post->ID : 0;
	}

	/**
	 * Format a post/page record for search/fetch responses.
	 *
	 * @param WP_Post $post            Post object.
	 * @param bool    $include_content Whether to include full content payload.
	 * @return array Formatted record.
	 */
	private function format_content_item( $post, $include_content ) {
		$excerpt = (string) $post->post_excerpt;
		if ( '' === trim( $excerpt ) ) {
			$excerpt = function_exists( 'wp_trim_words' )
				? wp_trim_words( wp_strip_all_tags( (string) $post->post_content ), 40, '...' )
				: '';
		}

		$item = array(
			'id'           => (int) $post->ID,
			'type'         => (string) $post->post_type,
			'status'       => (string) $post->post_status,
			'slug'         => (string) $post->post_name,
			'title'        => get_the_title( $post ),
			'url'          => (string) get_permalink( $post ),
			'excerpt'      => $excerpt,
			'date_gmt'     => (string) $post->post_date_gmt,
			'modified_gmt' => (string) $post->post_modified_gmt,
		);

		if ( $include_content ) {
			$raw_content = (string) $post->post_content;
			$item['content'] = array(
				'raw'      => $raw_content,
				'rendered' => apply_filters( 'the_content', $raw_content ),
			);
		}

		return $item;
	}

	/**
	 * Parse requested OAuth scope string.
	 *
	 * @param string $scope_string Space-separated scope string.
	 * @return array Sanitized scope list.
	 */
	private function parse_requested_oauth_scopes( $scope_string ) {
		$scope_string = trim( (string) $scope_string );
		if ( '' === $scope_string ) {
			return array( 'read' );
		}

		$requested = preg_split( '/\s+/', $scope_string );
		$requested = array_map( 'sanitize_key', (array) $requested );
		$requested = array_values( array_intersect( $requested, array( 'read', 'write', 'admin' ) ) );

		if ( empty( $requested ) ) {
			return array( 'read' );
		}

		if ( in_array( 'admin', $requested, true ) ) {
			return array( 'read', 'write', 'admin' );
		}

		if ( in_array( 'write', $requested, true ) && ! in_array( 'read', $requested, true ) ) {
			$requested[] = 'read';
		}

		return array_values( array_unique( $requested ) );
	}

	/**
	 * Check capability for managing scoped API keys.
	 *
	 * @return bool True if current user can manage keys.
	 */
	private function can_manage_api_keys() {
		return function_exists( 'current_user_can' ) && current_user_can( 'spai_manage_settings' );
	}

	/**
	 * Get the Additional CSS from the Customizer.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_custom_css( $request ) {
		$this->log_activity( 'get_custom_css', $request );

		$css = wp_get_custom_css();

		return $this->success_response( array(
			'css'    => $css,
			'length' => strlen( $css ),
		) );
	}

	/**
	 * Set or append to the Additional CSS in the Customizer.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function set_custom_css( $request ) {
		$this->log_activity( 'set_custom_css', $request );

		$new_css = $request->get_param( 'css' );
		$mode    = $request->get_param( 'mode' ) ?: 'append';

		if ( 'append' === $mode ) {
			$existing = wp_get_custom_css();
			$css      = $existing . "\n\n" . $new_css;
		} else {
			$css = $new_css;
		}

		$result = wp_update_custom_css_post( $css );

		if ( is_wp_error( $result ) ) {
			return $this->error_response(
				'css_update_failed',
				$result->get_error_message(),
				500
			);
		}

		return $this->success_response( array(
			'css'    => $css,
			'length' => strlen( $css ),
			'mode'   => $mode,
		) );
	}
}
