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
	 * Check capability for managing scoped API keys.
	 *
	 * @return bool True if current user can manage keys.
	 */
	private function can_manage_api_keys() {
		return function_exists( 'current_user_can' ) && current_user_can( 'spai_manage_settings' );
	}
}
