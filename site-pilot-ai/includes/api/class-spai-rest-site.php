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

		// AI onboarding briefing.
		register_rest_route(
			$this->namespace,
			'/onboard',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_onboard' ),
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

		// Plugin settings (GET and PUT).
		register_rest_route(
			$this->namespace,
			'/plugin-settings',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_plugin_settings' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_plugin_settings' ),
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

		// Guides (list topics or get specific topic via ?topic=...)
		register_rest_route(
			$this->namespace,
			'/guides',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_guides' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'topic' => array(
							'description' => __( 'Guide topic slug. Omit to list all available topics.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
					),
				),
			)
		);

		// Guides (get specific topic via path)
		register_rest_route(
			$this->namespace,
			'/guides/(?P<topic>[a-z_]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_guide_topic' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'topic' => array(
							'description' => __( 'Guide topic slug.', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
						),
					),
				),
			)
		);

		// Workflows (get specific workflow)
		register_rest_route(
			$this->namespace,
			'/workflows/(?P<name>[a-z_]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_workflow' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'name' => array(
							'description' => __( 'Workflow name.', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
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
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_custom_css' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Custom CSS length (lightweight check without full CSS body).
		register_rest_route(
			$this->namespace,
			'/custom-css/length',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_css_length' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Post meta
		register_rest_route(
			$this->namespace,
			'/post-meta/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_post_meta_handler' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'id'  => array(
							'description' => __( 'Post or page ID.', 'site-pilot-ai' ),
							'type'        => 'integer',
							'required'    => true,
						),
						'key' => array(
							'description' => __( 'Specific meta key to retrieve.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'set_post_meta_handler' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'id'    => array(
							'description' => __( 'Post or page ID.', 'site-pilot-ai' ),
							'type'        => 'integer',
							'required'    => true,
						),
						'key'   => array(
							'description' => __( 'Meta key to set.', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
						),
						'value' => array(
							'description' => __( 'Meta value to set.', 'site-pilot-ai' ),
							'required'    => true,
						),
					),
				),
			)
		);

		// Site Context (AI brief / style guide).
		register_rest_route(
			$this->namespace,
			'/site-context',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_site_context' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'set_site_context' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'context' => array(
							'description' => __( 'Site context markdown text (AI brief, style guide, rules).', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
						),
					),
				),
			)
		);

		// Theme info
		register_rest_route(
			$this->namespace,
			'/theme-info',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_theme_info' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Flush permalinks
		register_rest_route(
			$this->namespace,
			'/flush-permalinks',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'flush_permalinks' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Site health
		register_rest_route(
			$this->namespace,
			'/site-health',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_site_health' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Taxonomy terms CRUD
		register_rest_route(
			$this->namespace,
			'/terms',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_term' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'taxonomy'    => array(
							'description' => __( 'Taxonomy name.', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
						),
						'name'        => array(
							'description' => __( 'Term name.', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
						),
						'slug'        => array(
							'description' => __( 'Term slug.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
						'description' => array(
							'description' => __( 'Term description.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
						'parent'      => array(
							'description' => __( 'Parent term ID.', 'site-pilot-ai' ),
							'type'        => 'integer',
						),
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/terms/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_term' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'taxonomy'    => array(
							'description' => __( 'Taxonomy name.', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
						),
						'name'        => array(
							'description' => __( 'New term name.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
						'slug'        => array(
							'description' => __( 'New term slug.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
						'description' => array(
							'description' => __( 'New term description.', 'site-pilot-ai' ),
							'type'        => 'string',
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_term' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'taxonomy' => array(
							'description' => __( 'Taxonomy name.', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
						),
					),
				),
			)
		);

		// Rendered HTML (fetch what the browser sees).
		register_rest_route(
			$this->namespace,
			'/rendered-html',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_rendered_html' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'id'        => array(
							'description' => __( 'Post or page ID to fetch rendered HTML for.', 'site-pilot-ai' ),
							'type'        => 'integer',
						),
						'url'       => array(
							'description' => __( 'URL to fetch (same-host only for SSRF safety).', 'site-pilot-ai' ),
							'type'        => 'string',
						),
						'selector'  => array(
							'description' => __( 'CSS selector to extract (tag, .class, or #id).', 'site-pilot-ai' ),
							'type'        => 'string',
						),
						'max_bytes' => array(
							'description' => __( 'Maximum response size in bytes (default 51200, max 204800).', 'site-pilot-ai' ),
							'type'        => 'integer',
							'default'     => 51200,
						),
					),
				),
			)
		);

		// Single option get/update (whitelisted keys only).
		register_rest_route(
			$this->namespace,
			'/option',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_option_handler' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'key' => array(
							'description' => __( 'Option key to retrieve.', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_option_handler' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'key'   => array(
							'description' => __( 'Option key to update.', 'site-pilot-ai' ),
							'type'        => 'string',
							'required'    => true,
						),
						'value' => array(
							'description' => __( 'Option value to set.', 'site-pilot-ai' ),
							'required'    => true,
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

		$data = $mcp->get_introspection_data();

		// Include site context if set.
		$site_context = get_option( 'spai_site_context', '' );
		if ( '' !== $site_context ) {
			$data['site_context'] = $site_context;
		}

		return $this->success_response( $data );
	}

	/**
	 * Get AI onboarding briefing.
	 *
	 * Returns a comprehensive first-connection package including site identity,
	 * content inventory, active integrations, available tools, site context,
	 * recommended first actions, and a quick reference card.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_onboard( $request ) {
		$this->log_activity( 'onboard', $request );

		$site_info    = $this->core->get_site_info();
		$capabilities = $site_info['capabilities'] ?? array();

		// 1. Site identity.
		$identity = array(
			'name'        => $site_info['name'] ?? get_bloginfo( 'name' ),
			'description' => $site_info['description'] ?? get_bloginfo( 'description' ),
			'url'         => $site_info['url'] ?? home_url(),
			'admin_url'   => $site_info['admin_url'] ?? admin_url(),
			'language'    => $site_info['language'] ?? get_locale(),
			'timezone'    => $site_info['timezone'] ?? wp_timezone_string(),
			'is_rtl'      => $site_info['is_rtl'] ?? false,
			'wp_version'  => $site_info['wp_version'] ?? $GLOBALS['wp_version'],
			'theme'       => $site_info['theme'] ?? array(),
			'plugin'      => $site_info['plugin'] ?? array(
				'name'    => 'Site Pilot AI',
				'version' => defined( 'SPAI_VERSION' ) ? SPAI_VERSION : null,
			),
		);

		// 2. Content inventory.
		$counts     = wp_count_posts( 'post' );
		$page_counts = wp_count_posts( 'page' );

		$inventory = array(
			'posts'   => array(
				'published' => (int) ( $counts->publish ?? 0 ),
				'drafts'    => (int) ( $counts->draft ?? 0 ),
				'total'     => (int) ( ( $counts->publish ?? 0 ) + ( $counts->draft ?? 0 ) + ( $counts->private ?? 0 ) ),
			),
			'pages'   => array(
				'published' => (int) ( $page_counts->publish ?? 0 ),
				'drafts'    => (int) ( $page_counts->draft ?? 0 ),
				'total'     => (int) ( ( $page_counts->publish ?? 0 ) + ( $page_counts->draft ?? 0 ) + ( $page_counts->private ?? 0 ) ),
			),
			'media'   => array_sum( array_map( 'intval', (array) wp_count_attachments() ) ),
			'categories' => (int) wp_count_terms( 'category' ),
			'tags'       => (int) wp_count_terms( 'post_tag' ),
		);

		// Add product count if WooCommerce is active.
		if ( ! empty( $capabilities['woocommerce'] ) && post_type_exists( 'product' ) ) {
			$product_counts          = wp_count_posts( 'product' );
			$inventory['products'] = array(
				'published' => (int) ( $product_counts->publish ?? 0 ),
				'drafts'    => (int) ( $product_counts->draft ?? 0 ),
			);
		}

		// Recent updates (last 5 modified posts/pages).
		$recent_posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'orderby'        => 'modified',
				'order'          => 'DESC',
				'posts_per_page' => 5,
			)
		);

		$recent_updates = array();
		foreach ( $recent_posts as $p ) {
			$recent_updates[] = array(
				'id'       => $p->ID,
				'title'    => $p->post_title,
				'type'     => $p->post_type,
				'status'   => $p->post_status,
				'modified' => $p->post_modified,
			);
		}
		$inventory['recent_updates'] = $recent_updates;

		// 3. Active integrations.
		$integrations = array();

		if ( ! empty( $capabilities['elementor'] ) ) {
			$integrations['elementor'] = array(
				'active'      => true,
				'version'     => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : 'unknown',
				'pro'         => ! empty( $capabilities['elementor_pro'] ),
				'layout_mode' => $capabilities['elementor_layout_mode'] ?? 'section',
			);
		}

		if ( ! empty( $capabilities['gutenberg'] ) ) {
			$integrations['gutenberg'] = array(
				'active' => true,
			);
		}

		// SEO plugin detection.
		$seo_plugins = array(
			'yoast'    => 'Yoast SEO',
			'rankmath' => 'RankMath',
			'aioseo'   => 'AIOSEO',
			'seopress' => 'SEOPress',
		);
		foreach ( $seo_plugins as $key => $label ) {
			if ( ! empty( $capabilities[ $key ] ) ) {
				$integrations['seo'] = array(
					'active' => true,
					'plugin' => $label,
				);
				break;
			}
		}

		// Forms plugin detection.
		$form_plugins = array(
			'cf7'          => 'Contact Form 7',
			'wpforms'      => 'WPForms',
			'gravityforms' => 'Gravity Forms',
			'ninjaforms'   => 'Ninja Forms',
		);
		foreach ( $form_plugins as $key => $label ) {
			if ( ! empty( $capabilities[ $key ] ) ) {
				$integrations['forms'] = array(
					'active' => true,
					'plugin' => $label,
				);
				break;
			}
		}

		if ( ! empty( $capabilities['woocommerce'] ) ) {
			$integrations['woocommerce'] = array(
				'active' => true,
			);
		}

		// Pro status.
		$is_pro = false;
		if ( function_exists( 'spai_license' ) ) {
			try {
				$license = spai_license();
				$is_pro  = $license && method_exists( $license, 'is_pro' ) && $license->is_pro();
			} catch ( \Exception $e ) {
				$is_pro = false;
			}
		}
		if ( ! $is_pro ) {
			$is_pro = function_exists( 'spai_pro' ) || defined( 'SPAI_PRO_VERSION' );
		}

		// 4. Available tools grouped by category.
		$tools_by_category = array();
		if ( class_exists( 'Spai_REST_MCP' ) ) {
			$mcp       = new Spai_REST_MCP();
			$all_tools = method_exists( $mcp, 'get_introspection_data' )
				? ( $mcp->get_introspection_data()['tools'] ?? array() )
				: array();

			foreach ( $all_tools as $tool ) {
				$cat  = $tool['annotations']['category'] ?? 'site';
				$name = $tool['name'] ?? '';
				if ( '' === $name ) {
					continue;
				}
				if ( ! isset( $tools_by_category[ $cat ] ) ) {
					$tools_by_category[ $cat ] = array();
				}
				$tools_by_category[ $cat ][] = array(
					'name'        => $name,
					'description' => $tool['description'] ?? '',
					'tier'        => $is_pro ? 'pro' : 'free',
				);
			}
		}

		// 5. Site context.
		$site_context    = get_option( 'spai_site_context', '' );
		$context_section = array(
			'configured' => '' !== $site_context,
		);
		if ( '' !== $site_context ) {
			$context_section['context'] = $site_context;
		} else {
			$context_section['hint'] = 'No site context configured. Use wp_set_site_context to define design rules, color palette, typography, and layout guidelines.';
		}

		// 6. Recommended first actions.
		$actions = array();

		if ( '' === $site_context ) {
			$actions[] = array(
				'action'      => 'Set up site context',
				'tool'        => 'wp_set_site_context',
				'description' => 'Define your site design rules, color palette, typography, and layout guidelines so AI assistants follow your brand.',
			);
		}

		if ( $inventory['pages']['published'] === 0 ) {
			$actions[] = array(
				'action'      => 'Create your first page',
				'tool'        => 'wp_create_page',
				'description' => 'No published pages found. Create a homepage or landing page to get started.',
			);
		}

		if ( ! empty( $capabilities['elementor'] ) ) {
			$actions[] = array(
				'action'      => 'Review Elementor status',
				'tool'        => 'wp_elementor_status',
				'description' => 'Check which pages use Elementor and the current layout mode.',
			);
		}

		if ( isset( $integrations['seo'] ) ) {
			$actions[] = array(
				'action'      => 'Audit SEO metadata',
				'tool'        => 'wp_seo_status',
				'description' => 'Check SEO coverage across your pages and identify missing meta descriptions.',
			);
		}

		if ( $inventory['posts']['published'] > 0 ) {
			$actions[] = array(
				'action'      => 'Review recent content',
				'tool'        => 'wp_list_posts',
				'description' => 'Browse existing blog posts to understand current content.',
			);
		}

		// 7. Quick reference card — top 10 most-used tools.
		$quick_reference = array(
			array( 'tool' => 'wp_onboard', 'use' => 'First-connection site briefing (you are here)' ),
			array( 'tool' => 'wp_get_site_context', 'use' => 'Read site design rules and style guide' ),
			array( 'tool' => 'wp_list_pages', 'use' => 'List all pages with status and IDs' ),
			array( 'tool' => 'wp_list_posts', 'use' => 'List blog posts with filters' ),
			array( 'tool' => 'wp_create_page', 'use' => 'Create a new page' ),
			array( 'tool' => 'wp_search', 'use' => 'Search posts and pages by keyword' ),
			array( 'tool' => 'wp_fetch', 'use' => 'Get full content for a post or page by ID' ),
			array( 'tool' => 'wp_upload_media_from_url', 'use' => 'Upload an image from a URL' ),
			array( 'tool' => 'wp_detect_plugins', 'use' => 'Discover active plugins and capabilities' ),
			array( 'tool' => 'wp_site_info', 'use' => 'Get site name, URL, theme, and version info' ),
		);

		// Add Elementor tools to quick reference if active.
		if ( ! empty( $capabilities['elementor'] ) ) {
			$quick_reference[] = array( 'tool' => 'wp_get_elementor', 'use' => 'Read Elementor page builder data' );
			$quick_reference[] = array( 'tool' => 'wp_set_elementor', 'use' => 'Update Elementor page builder data' );
		}

		$data = array(
			'site_identity'       => $identity,
			'content_inventory'   => $inventory,
			'active_integrations' => $integrations,
			'available_tools'     => $tools_by_category,
			'site_context'        => $context_section,
			'recommended_actions' => $actions,
			'quick_reference'     => $quick_reference,
			'pro_active'          => $is_pro,
		);

		return $this->success_response( $data );
	}

	/**
	 * Get site context (AI brief / style guide).
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_site_context( $request ) {
		$this->log_activity( 'get_site_context', $request );

		$context = get_option( 'spai_site_context', '' );

		return $this->success_response(
			array(
				'context'    => $context,
				'updated_at' => get_option( 'spai_site_context_updated', '' ),
				'hint'       => '' === $context
					? 'No site context configured. Use wp_set_site_context to define your site style guide, header/footer rules, predefined sections, and page structure guidelines. This will be included in wp_introspect so AI assistants automatically follow your design rules.'
					: null,
			)
		);
	}

	/**
	 * Set site context (AI brief / style guide).
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function set_site_context( $request ) {
		$this->log_activity( 'set_site_context', $request );

		$context = $request->get_param( 'context' );

		if ( null === $context ) {
			return $this->error_response( 'missing_context', 'The context parameter is required.', 400 );
		}

		// Limit to 50KB to prevent abuse.
		if ( strlen( $context ) > 51200 ) {
			return $this->error_response( 'context_too_large', 'Site context must be under 50KB.', 400 );
		}

		update_option( 'spai_site_context', $context );
		update_option( 'spai_site_context_updated', gmdate( 'Y-m-d H:i:s' ) );

		return $this->success_response(
			array(
				'success'    => true,
				'length'     => strlen( $context ),
				'updated_at' => get_option( 'spai_site_context_updated' ),
			)
		);
	}

	/**
	 * Get analytics.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_analytics( $request ) {
		$this->log_activity( 'analytics', $request );

		$days      = $request->get_param( 'days' );
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

		return $this->success_response(
			array(
				'plugins'      => $plugins,
				'capabilities' => $this->core->get_capabilities(),
			)
		);
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

		$search_query = new WP_Query(
			array(
				'post_type'           => $post_types,
				'post_status'         => $status,
				's'                   => $query,
				'posts_per_page'      => $per_page,
				'paged'               => $page,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => false,
			)
		);

		$items = array();
		foreach ( $search_query->posts as $post ) {
			if ( $post instanceof WP_Post ) {
				$items[] = $this->format_content_item( $post, false );
			}
		}

		return $this->success_response(
			array(
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
			)
		);
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

		return $this->success_response(
			$this->format_content_item( $post, $include_content )
		);
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
			'title'               => get_option( 'blogname' ),
			'tagline'             => get_option( 'blogdescription' ),
			'url'                 => get_option( 'siteurl' ),
			'home'                => get_option( 'home' ),
			'admin_email'         => get_option( 'admin_email' ),
			'timezone'            => get_option( 'timezone_string' ) ?: 'UTC',
			'date_format'         => get_option( 'date_format' ),
			'time_format'         => get_option( 'time_format' ),
			'language'            => get_option( 'WPLANG' ) ?: 'en_US',
			'posts_per_page'      => (int) get_option( 'posts_per_page' ),
			'permalink_structure' => get_option( 'permalink_structure' ),
			'show_on_front'       => get_option( 'show_on_front' ),
			'page_on_front'       => (int) get_option( 'page_on_front' ),
			'page_for_posts'      => (int) get_option( 'page_for_posts' ),
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

		return $this->success_response(
			array(
				'updated'  => $updated,
				'settings' => $this->get_settings( $request )->get_data(),
			)
		);
	}

	/**
	 * Get Site Pilot AI plugin settings.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_plugin_settings( $request ) {
		$this->log_activity( 'get_plugin_settings', $request );

		$settings = get_option( 'spai_settings', array() );
		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		// Redact sensitive values — show presence but not the value.
		$safe = array();
		$secret_keys = array( 'oauth_client_secret_hash', 'github_token', 'screenshot_worker_token' );
		foreach ( $settings as $key => $value ) {
			if ( in_array( $key, $secret_keys, true ) ) {
				$safe[ $key ] = ! empty( $value ) ? '***configured***' : '';
			} else {
				$safe[ $key ] = $value;
			}
		}

		return $this->success_response( $safe );
	}

	/**
	 * Update Site Pilot AI plugin settings.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function update_plugin_settings( $request ) {
		$this->log_activity( 'update_plugin_settings', $request );

		$params = $request->get_json_params();
		if ( empty( $params ) ) {
			// Fallback to body params (for internal MCP dispatch).
			$params = $request->get_body_params();
		}
		if ( empty( $params ) ) {
			return $this->error_response(
				'missing_settings',
				__( 'Settings data is required.', 'site-pilot-ai' ),
				400
			);
		}

		$current = get_option( 'spai_settings', array() );
		if ( ! is_array( $current ) ) {
			$current = array();
		}

		$updated = array();

		// Allowed keys with their sanitization rules.
		$allowed = array(
			'enable_logging'          => 'bool',
			'log_retention_days'      => 'int:1:365',
			'log_store_response_data' => 'bool',
			'allowed_origins'         => 'text',
			'oauth_enabled'           => 'bool',
			'oauth_client_id'         => 'key',
			'oauth_client_secret'     => 'secret',
			'oauth_token_ttl'         => 'int:60:86400',
			'alerts_enabled'          => 'bool',
			'alerts_window_minutes'   => 'int:1:60',
			'alerts_cooldown_minutes' => 'int:1:1440',
			'alerts_5xx_threshold'    => 'int:1:10000',
			'alerts_auth_threshold'   => 'int:1:10000',
			'github_token'            => 'secret',
			'github_repo'             => 'text',
		);

		foreach ( $allowed as $key => $rule ) {
			if ( ! isset( $params[ $key ] ) ) {
				continue;
			}

			$value = $params[ $key ];

			if ( 'bool' === $rule ) {
				$current[ $key ] = (bool) $value;
			} elseif ( 'text' === $rule ) {
				$current[ $key ] = sanitize_text_field( (string) $value );
			} elseif ( 'key' === $rule ) {
				$current[ $key ] = sanitize_key( (string) $value );
			} elseif ( 'secret' === $rule ) {
				$val = trim( (string) $value );
				if ( '' !== $val ) {
					if ( 'oauth_client_secret' === $key ) {
						$current['oauth_client_secret_hash'] = wp_hash_password( $val );
					} else {
						$current[ $key ] = sanitize_text_field( $val );
					}
				}
			} elseif ( 0 === strpos( $rule, 'int:' ) ) {
				$parts = explode( ':', $rule );
				$min   = (int) $parts[1];
				$max   = (int) $parts[2];
				$current[ $key ] = max( $min, min( $max, absint( $value ) ) );
			}

			$updated[] = $key;
		}

		if ( empty( $updated ) ) {
			return $this->error_response(
				'no_valid_settings',
				sprintf(
					'No valid settings provided. Allowed keys: %s',
					implode( ', ', array_keys( $allowed ) )
				),
				400
			);
		}

		update_option( 'spai_settings', $current );

		return $this->success_response( array(
			'updated' => $updated,
			'message' => sprintf( 'Updated %d setting(s).', count( $updated ) ),
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
			$page                           = get_post( $options['page_on_front'] );
			$options['page_on_front_title'] = $page ? $page->post_title : null;
		}

		if ( $options['page_for_posts'] ) {
			$page                            = get_post( $options['page_for_posts'] );
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

		return $this->success_response(
			array(
				'updated' => $updated,
				'options' => $this->get_options( $request )->get_data(),
			)
		);
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
			return $this->success_response(
				array(
					'has_favicon' => false,
					'id'          => null,
					'url'         => null,
					'sizes'       => array(),
				)
			);
		}

		$sizes      = array();
		$icon_sizes = array( 32, 180, 192, 270, 512 );

		foreach ( $icon_sizes as $size ) {
			$icon_url = get_site_icon_url( $size );
			if ( $icon_url ) {
				$sizes[ $size ] = $icon_url;
			}
		}

		return $this->success_response(
			array(
				'has_favicon' => true,
				'id'          => (int) $site_icon_id,
				'url'         => get_site_icon_url( 512 ),
				'sizes'       => $sizes,
			)
		);
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

			return $this->success_response(
				array(
					'updated' => true,
					'favicon' => $this->get_favicon( $request )->get_data(),
				)
			);
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
				wp_delete_file( $tmp );
				return $this->error_response(
					'upload_failed',
					$attachment_id->get_error_message(),
					400
				);
			}

			update_option( 'site_icon', $attachment_id );

			return $this->success_response(
				array(
					'updated'  => true,
					'uploaded' => true,
					'favicon'  => $this->get_favicon( $request )->get_data(),
				),
				201
			);
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
			return $this->success_response(
				array(
					'deleted' => false,
					'message' => __( 'No favicon was set.', 'site-pilot-ai' ),
				)
			);
		}

		delete_option( 'site_icon' );

		return $this->success_response(
			array(
				'deleted'     => true,
				'previous_id' => (int) $site_icon_id,
			)
		);
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
			return $this->success_response(
				array(
					'enabled' => false,
					'message' => __( 'Rate limiting is not available.', 'site-pilot-ai' ),
				)
			);
		}

		$limiter  = Spai_Rate_Limiter::get_instance();
		$settings = $limiter->get_settings();
		$usage    = $limiter->get_usage();

		return $this->success_response(
			array(
				'enabled' => $settings['enabled'],
				'limits'  => array(
					'burst'      => $settings['burst_limit'],
					'per_minute' => $settings['requests_per_minute'],
					'per_hour'   => $settings['requests_per_hour'],
				),
				'usage'   => $usage,
			)
		);
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

		return $this->success_response(
			array(
				'updated'  => true,
				'settings' => $limiter->get_settings(),
			)
		);
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

		return $this->success_response(
			array(
				'reset'      => true,
				'identifier' => $identifier,
			)
		);
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

		return $this->success_response(
			array(
				'keys'  => $keys,
				'total' => count( $keys ),
			)
		);
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

		$label           = (string) $request->get_param( 'label' );
		$scopes          = $request->get_param( 'scopes' );
		$scopes          = is_array( $scopes ) ? $scopes : array();
		$role            = (string) $request->get_param( 'role' );
		$tool_categories = $request->get_param( 'tool_categories' );
		$tool_categories = is_array( $tool_categories ) ? $tool_categories : array();

		if ( '' === $role ) {
			$role = 'admin';
		}

		$created = $this->create_scoped_api_key( $label, $scopes, $role, $tool_categories );

		return $this->success_response(
			array(
				'api_key' => $created,
			),
			201
		);
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

		return $this->success_response(
			array(
				'revoked' => true,
				'id'      => sanitize_key( $key_id ),
			)
		);
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

		// Clear ALL update caches: Freemius SDK internal options + WP transient.
		delete_site_transient( 'update_plugins' );

		// Freemius SDK stores update data in options like fs_updates, fs_accounts, etc.
		// Delete them to force a fresh API check.
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $wpdb->options is safe.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'fs_updates%'" );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $wpdb->options is safe.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_fs_sdk%'" );
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}

		if ( function_exists( 'spa_fs' ) ) {
			$fs = spa_fs();
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

		return $this->success_response(
			array(
				'current_version'  => $current_version,
				'update_available' => $update_available,
				'new_version'      => $new_version,
				'has_package'      => ! empty( $package ),
			)
		);
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

		// Clear ALL update caches: Freemius SDK internal options + WP transient.
		delete_site_transient( 'update_plugins' );

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $wpdb->options is safe.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'fs_updates%'" );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $wpdb->options is safe.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_fs_sdk%'" );
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}

		if ( function_exists( 'spa_fs' ) ) {
			$fs = spa_fs();
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
			return $this->success_response(
				array(
					'updated' => false,
					'message' => __( 'No update available.', 'site-pilot-ai' ),
					'version' => defined( 'SPAI_VERSION' ) ? SPAI_VERSION : null,
				)
			);
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

		return $this->success_response(
			array(
				'updated'     => true,
				'new_version' => is_object( $plugin_update ) ? $plugin_update->new_version : null,
				'message'     => __( 'Plugin updated successfully.', 'site-pilot-ai' ),
			)
		);
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

		return $this->success_response(
			array(
				'categories' => $items,
				'total'      => count( $items ),
			)
		);
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

		return $this->success_response(
			array(
				'tags'  => $items,
				'total' => count( $items ),
			)
		);
	}

	/**
	 * Get post meta.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function get_post_meta_handler( $request ) {
		$this->log_activity( 'get_post_meta', $request );

		$post_id = absint( $request->get_param( 'id' ) );
		$post    = get_post( $post_id );

		if ( ! $post instanceof WP_Post ) {
			return $this->error_response( 'not_found', __( 'Post not found.', 'site-pilot-ai' ), 404 );
		}

		$key = $request->get_param( 'key' );

		if ( ! empty( $key ) ) {
			$key = sanitize_key( $key );

			if ( $this->is_blocked_meta_key( $key ) ) {
				return $this->error_response( 'forbidden_meta_key', __( 'This meta key is not accessible via API.', 'site-pilot-ai' ), 403 );
			}

			$value = get_post_meta( $post_id, $key, true );

			return $this->success_response(
				array(
					'id'    => $post_id,
					'key'   => $key,
					'value' => $value,
				)
			);
		}

		// Return all non-blocked meta
		$all_meta  = get_post_meta( $post_id );
		$safe_meta = array();

		foreach ( $all_meta as $meta_key => $meta_values ) {
			if ( ! $this->is_blocked_meta_key( $meta_key ) ) {
				$safe_meta[ $meta_key ] = count( $meta_values ) === 1 ? $meta_values[0] : $meta_values;
			}
		}

		return $this->success_response(
			array(
				'id'   => $post_id,
				'meta' => $safe_meta,
			)
		);
	}

	/**
	 * Set post meta.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function set_post_meta_handler( $request ) {
		$this->log_activity( 'set_post_meta', $request );

		$post_id = absint( $request->get_param( 'id' ) );
		$post    = get_post( $post_id );

		if ( ! $post instanceof WP_Post ) {
			return $this->error_response( 'not_found', __( 'Post not found.', 'site-pilot-ai' ), 404 );
		}

		$key   = sanitize_key( (string) $request->get_param( 'key' ) );
		$value = $request->get_param( 'value' );

		if ( '' === $key ) {
			return $this->error_response( 'missing_key', __( 'Meta key is required.', 'site-pilot-ai' ), 400 );
		}

		if ( $this->is_blocked_meta_key( $key ) ) {
			// Provide specific guidance for Elementor meta keys.
			if ( '_elementor_data' === $key ) {
				return $this->error_response(
					'use_elementor_endpoint',
					__( 'Use wp_set_elementor (POST /elementor/{id}) to set Elementor page data.', 'site-pilot-ai' ),
					400
				);
			}
			if ( '_elementor_page_settings' === $key ) {
				return $this->error_response(
					'use_elementor_endpoint',
					__( 'Use wp_set_elementor with the page_settings parameter to update Elementor page settings.', 'site-pilot-ai' ),
					400
				);
			}
			if ( 0 === strpos( $key, '_elementor' ) ) {
				return $this->error_response(
					'use_elementor_endpoint',
					__( 'Elementor meta keys cannot be set via wp_set_post_meta. Use the /elementor/{id} endpoints instead.', 'site-pilot-ai' ),
					400
				);
			}
			return $this->error_response( 'forbidden_meta_key', __( 'This meta key is not accessible via API.', 'site-pilot-ai' ), 403 );
		}

		// Sanitize value — decode JSON objects/arrays to PHP arrays so WordPress
		// serializes them properly (instead of storing raw JSON strings).
		if ( is_string( $value ) ) {
			$trimmed = ltrim( $value );
			if ( ( '{' === substr( $trimmed, 0, 1 ) || '[' === substr( $trimmed, 0, 1 ) ) && null !== json_decode( $value ) ) {
				// Decode JSON to a PHP array — WordPress will auto-serialize it.
				$value = json_decode( $value, true );
			} else {
				$value = sanitize_text_field( $value );
			}
		}

		$result = update_post_meta( $post_id, $key, $value );

		return $this->success_response(
			array(
				'id'      => $post_id,
				'key'     => $key,
				'value'   => $value,
				'updated' => false !== $result,
			)
		);
	}

	/**
	 * Check if a meta key is blocked from API access.
	 *
	 * @param string $meta_key Meta key to check.
	 * @return bool True if blocked.
	 */
	private function is_blocked_meta_key( $meta_key ) {
		$blocked_prefixes = array( '_wp_', 'spai_api_key', '_edit_lock', '_edit_last', '_elementor_' );
		$blocked_keys     = array( '_wp_page_template', '_thumbnail_id', '_elementor_data', '_elementor_page_settings', '_elementor_css', '_elementor_edit_mode' );

		// Allow these specific WordPress keys (non-Elementor).
		$allowed_keys = array( '_wp_page_template', '_thumbnail_id' );

		if ( in_array( $meta_key, $allowed_keys, true ) ) {
			return false;
		}

		if ( in_array( $meta_key, $blocked_keys, true ) ) {
			return true;
		}

		foreach ( $blocked_prefixes as $prefix ) {
			if ( 0 === strpos( $meta_key, $prefix ) ) {
				return true;
			}
		}

		// Block secret-looking keys
		if ( preg_match( '/(password|secret|token|auth|credential)/i', $meta_key ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Whitelist of option keys allowed via API.
	 *
	 * @return array Allowed option keys.
	 */
	private function get_allowed_option_keys() {
		return array(
			// WordPress core.
			'blogname',
			'blogdescription',
			'siteurl',
			'home',
			'admin_email',
			'timezone_string',
			'date_format',
			'time_format',
			'WPLANG',
			'posts_per_page',
			'posts_per_rss',
			'show_on_front',
			'page_on_front',
			'page_for_posts',
			'blog_public',
			'permalink_structure',
			'default_category',
			'default_post_format',
			'thumbnail_size_w',
			'thumbnail_size_h',
			'medium_size_w',
			'medium_size_h',
			'large_size_w',
			'large_size_h',
			'site_icon',
			// Site Pilot AI.
			'spai_site_context',
			'spai_site_context_updated',
			// Elementor.
			'elementor_pro_theme_builder_conditions',
		);
	}

	/**
	 * Allowed option key prefixes.
	 *
	 * Any option starting with one of these prefixes is allowed unless
	 * it matches a sensitive pattern from get_blocked_option_patterns().
	 *
	 * @return array Prefix strings.
	 */
	private function get_allowed_option_prefixes() {
		return array(
			'elementor_',
			'wpseo_',
			'rank_math_',
			'astra_',
			'ocean_',
			'theme_mods_',
			'widget_',
			'nav_menu_',
			'sidebars_',
			'spai_',
			'woocommerce_',
			'wp_page_for_privacy_policy',
		);
	}

	/**
	 * Blocked option key patterns (substrings).
	 *
	 * Options matching any of these patterns are NEVER accessible,
	 * even if they match an allowed prefix.
	 *
	 * @return array Blocked substring patterns.
	 */
	private function get_blocked_option_patterns() {
		return array(
			'password',
			'secret',
			'token',
			'auth_key',
			'auth_salt',
			'logged_in_key',
			'logged_in_salt',
			'nonce_key',
			'nonce_salt',
			'secure_auth',
			'credential',
			'api_key',
			'private_key',
			'license_key',
			'stripe_',
			'paypal_',
			'_session',
		);
	}

	/**
	 * Check if an option key is allowed via API.
	 *
	 * Matches exact keys, then allowed prefixes, then blocks sensitive patterns.
	 *
	 * @param string $key Option key.
	 * @return bool True if allowed.
	 */
	private function is_option_allowed( $key ) {
		// Exact match always wins.
		if ( in_array( $key, $this->get_allowed_option_keys(), true ) ) {
			return true;
		}

		// Check blocked patterns first — these override prefix matches.
		$lower_key = strtolower( $key );
		foreach ( $this->get_blocked_option_patterns() as $pattern ) {
			if ( false !== strpos( $lower_key, $pattern ) ) {
				return false;
			}
		}

		// Check allowed prefixes.
		foreach ( $this->get_allowed_option_prefixes() as $prefix ) {
			if ( 0 === strpos( $key, $prefix ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get a single WordPress option by key.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function get_option_handler( $request ) {
		$this->log_activity( 'get_option', $request );

		$key = sanitize_key( (string) $request->get_param( 'key' ) );

		if ( ! $this->is_option_allowed( $key ) ) {
			return $this->error_response(
				'forbidden_option',
				/* translators: %s: option key */
				sprintf( __( 'Option "%s" is not accessible via API. Allowed: core WP options and prefixes: elementor_*, wpseo_*, rank_math_*, astra_*, theme_mods_*, widget_*, woocommerce_*, spai_*. Sensitive keys (passwords, tokens, secrets) are always blocked.', 'site-pilot-ai' ), $key ),
				403
			);
		}

		$value = get_option( $key );

		return $this->success_response(
			array(
				'key'   => $key,
				'value' => $value,
			)
		);
	}

	/**
	 * Update a single WordPress option by key.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function update_option_handler( $request ) {
		$this->log_activity( 'update_option', $request );

		$key   = sanitize_key( (string) $request->get_param( 'key' ) );
		$value = $request->get_param( 'value' );

		if ( ! $this->is_option_allowed( $key ) ) {
			return $this->error_response(
				'forbidden_option',
				/* translators: %s: option key */
				sprintf( __( 'Option "%s" is not accessible via API. Allowed: core WP options and prefixes: elementor_*, wpseo_*, rank_math_*, astra_*, theme_mods_*, widget_*, woocommerce_*, spai_*. Sensitive keys (passwords, tokens, secrets) are always blocked.', 'site-pilot-ai' ), $key ),
				403
			);
		}

		// Type-specific sanitization
		if ( 'admin_email' === $key ) {
			$value = sanitize_email( $value );
			if ( ! is_email( $value ) ) {
				return $this->error_response( 'invalid_email', __( 'Invalid email address.', 'site-pilot-ai' ), 400 );
			}
		} elseif ( in_array( $key, array( 'posts_per_page', 'posts_per_rss', 'page_on_front', 'page_for_posts', 'default_category', 'thumbnail_size_w', 'thumbnail_size_h', 'medium_size_w', 'medium_size_h', 'large_size_w', 'large_size_h', 'site_icon' ), true ) ) {
			$value = absint( $value );
		} elseif ( 'blog_public' === $key ) {
			$value = $value ? 1 : 0;
		} elseif ( is_string( $value ) ) {
			$value = sanitize_text_field( $value );
		}

		$old_value = get_option( $key );

		// Wrap in try/catch: plugins like Elementor hook into update_option
		// and may throw exceptions in REST context (e.g. AJAX auth checks).
		try {
			update_option( $key, $value );
		} catch ( \Exception $e ) {
			// Option was likely still updated before the hook threw.
			// Verify by re-reading.
			$current = get_option( $key );
			if ( $current !== $value ) {
				return $this->error_response(
					'update_failed',
					$e->getMessage(),
					500
				);
			}
		}

		return $this->success_response(
			array(
				'key'       => $key,
				'value'     => get_option( $key ),
				'old_value' => $old_value,
				'updated'   => true,
			)
		);
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

		$path = wp_parse_url( $url, PHP_URL_PATH );

		if ( ! is_string( $path ) || '' === trim( $path ) ) {
			return 0;
		}

		$path = trim( $path, '/' );
		if ( '' === $path || ! function_exists( 'get_page_by_path' ) ) {
			return 0;
		}

		$post_types = 'any' === $type ? array( 'post', 'page' ) : array( $type );

		// First try published posts (default behavior).
		$post = get_page_by_path( $path, OBJECT, $post_types );
		if ( $post instanceof WP_Post ) {
			return (int) $post->ID;
		}

		// Also check private, draft, and pending posts (admin API key has full access).
		$non_public_statuses = array( 'private', 'draft', 'pending' );
		foreach ( $non_public_statuses as $status ) {
			$found = get_posts(
				array(
					'name'           => basename( $path ),
					'post_type'      => $post_types,
					'post_status'    => $status,
					'posts_per_page' => 1,
					'fields'         => 'ids',
				)
			);
			if ( ! empty( $found ) ) {
				return (int) $found[0];
			}
		}

		return 0;
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
			$raw_content     = (string) $post->post_content;
			$item['content'] = array(
				'raw'      => $raw_content,
				'rendered' => apply_filters( 'the_content', $raw_content ),
			);

			// Flag Elementor pages so callers know to use wp_get_elementor.
			$elementor_data = get_post_meta( $post->ID, '_elementor_data', true );
			if ( ! empty( $elementor_data ) ) {
				$item['elementor'] = true;
				if ( '' === trim( $raw_content ) ) {
					$item['content']['note'] = 'This page is built with Elementor. Use wp_get_elementor to retrieve the layout data.';
				}
			}
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

		return $this->success_response(
			array(
				'css'    => $css,
				'length' => strlen( $css ),
			)
		);
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

		$response = array(
			'css'    => $css,
			'length' => strlen( $css ),
			'mode'   => $mode,
		);

		// Check for themes with known custom CSS systems that may override Customizer CSS.
		$theme = wp_get_theme();
		$theme_name = $theme->get( 'Name' );
		$theme_warning = null;

		// Eduma / ThimPress themes use their own CSS option.
		if ( get_option( 'thim_custom_css' ) !== false || false !== stripos( $theme_name, 'eduma' ) || false !== stripos( $theme_name, 'thimpress' ) ) {
			$theme_warning = sprintf(
				/* translators: %s: theme name */
				__( "Theme '%s' may use its own CSS system (thim_custom_css). CSS saved via WordPress Customizer but may not render. Check theme settings.", 'site-pilot-ai' ),
				$theme_name
			);
		} elseif ( false !== stripos( $theme_name, 'flavor' ) ) {
			$theme_warning = sprintf(
				/* translators: %s: theme name */
				__( "Theme '%s' may use its own CSS system. CSS saved via WordPress Customizer but may not render. Check theme settings.", 'site-pilot-ai' ),
				$theme_name
			);
		}

		if ( $theme_warning ) {
			$response['theme_warning'] = $theme_warning;
		}

		// Detect if the theme has removed the wp_custom_css_cb callback from wp_head.
		// This callback is what outputs the <style id="wp-custom-css"> tag on the frontend.
		$css_callback_hooked = has_action( 'wp_head', 'wp_custom_css_cb' );
		$response['wp_custom_css_cb_active'] = (bool) $css_callback_hooked;

		if ( ! $css_callback_hooked ) {
			$response['warning'] = __(
				'CSS saved but may not render on this theme. The active theme does not have the wp_custom_css_cb callback hooked to wp_head, which means WordPress Additional CSS will not be output. Consider using Elementor Custom CSS or a code snippets plugin as an alternative.',
				'site-pilot-ai'
			);
		}

		// CSS rendering verification via loopback.
		$verification = array( 'checked' => false );
		$loopback     = wp_remote_get(
			home_url( '/' ),
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $loopback ) ) {
			$verification['reason'] = $loopback->get_error_message();
		} else {
			$body = wp_remote_retrieve_body( $loopback );
			$verification['checked']         = true;
			$verification['style_tag_found'] = false !== strpos( $body, '<style id="wp-custom-css">' );
			$snippet                         = substr( trim( $css ), 0, 50 );
			$verification['snippet_found']   = ! empty( $snippet ) && false !== strpos( $body, $snippet );
			$verification['verified']        = $verification['style_tag_found'] && $verification['snippet_found'];

			if ( ! $verification['verified'] && $css_callback_hooked ) {
				$verification['warning'] = __( 'CSS was saved but could not be confirmed in the rendered page. It may be overridden by theme or caching.', 'site-pilot-ai' );
			} elseif ( ! $verification['verified'] && ! $css_callback_hooked ) {
				$verification['warning'] = __(
					'CSS saved but may not render on this theme. The active theme may not support WordPress Additional CSS (wp_custom_css_cb is not hooked). Consider using Elementor Custom CSS or a code snippets plugin as an alternative.',
					'site-pilot-ai'
				);
			}
		}

		$response['verification'] = $verification;

		return $this->success_response( $response );
	}

	/**
	 * Delete all custom CSS (clear the Customizer Additional CSS).
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function delete_custom_css( $request ) {
		$this->log_activity( 'delete_custom_css', $request );

		$previous_length = strlen( wp_get_custom_css() );
		$result          = wp_update_custom_css_post( '' );

		if ( is_wp_error( $result ) ) {
			return $this->error_response(
				'css_delete_failed',
				$result->get_error_message(),
				500
			);
		}

		return $this->success_response(
			array(
				'deleted'         => true,
				'previous_length' => $previous_length,
				'message'         => __( 'All custom CSS has been removed.', 'site-pilot-ai' ),
			)
		);
	}

	/**
	 * Get the length of custom CSS without returning the full body.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_css_length( $request ) {
		$this->log_activity( 'get_css_length', $request );

		$css = wp_get_custom_css();

		return $this->success_response(
			array(
				'length'     => strlen( $css ),
				'line_count' => $css ? substr_count( $css, "\n" ) + 1 : 0,
				'has_css'    => strlen( $css ) > 0,
			)
		);
	}

	/**
	 * Get detailed theme information.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_theme_info( $request ) {
		$this->log_activity( 'get_theme_info', $request );

		$theme  = wp_get_theme();
		$parent = $theme->parent();

		$info = array(
			'name'           => $theme->get( 'Name' ),
			'version'        => $theme->get( 'Version' ),
			'author'         => $theme->get( 'Author' ),
			'author_uri'     => $theme->get( 'AuthorURI' ),
			'theme_uri'      => $theme->get( 'ThemeURI' ),
			'description'    => $theme->get( 'Description' ),
			'text_domain'    => $theme->get( 'TextDomain' ),
			'is_child'       => (bool) $parent,
			'parent'         => $parent ? array(
				'name'    => $parent->get( 'Name' ),
				'version' => $parent->get( 'Version' ),
			) : null,
			'is_block_theme' => function_exists( 'wp_is_block_theme' ) && wp_is_block_theme(),
			'template'       => $theme->get_template(),
			'stylesheet'     => $theme->get_stylesheet(),
		);

		// Page templates
		$templates              = $theme->get_page_templates();
		$info['page_templates'] = array();
		foreach ( $templates as $slug => $name ) {
			$info['page_templates'][] = array(
				'slug' => $slug,
				'name' => $name,
			);
		}

		// Elementor compatibility
		$info['elementor'] = array(
			'active'      => defined( 'ELEMENTOR_VERSION' ),
			'version'     => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : null,
			'layout_mode' => 'section',
		);

		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$experiment = get_option( 'elementor_experiment-container' );
			if ( 'active' === $experiment || 'default' === $experiment ) {
				$info['elementor']['layout_mode'] = 'container';
			}
		}

		return $this->success_response( $info );
	}

	/**
	 * Flush permalink rewrite rules.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function flush_permalinks( $request ) {
		$this->log_activity( 'flush_permalinks', $request );

		flush_rewrite_rules();

		return $this->success_response(
			array(
				'success'   => true,
				'message'   => __( 'Permalink rewrite rules flushed.', 'site-pilot-ai' ),
				'structure' => get_option( 'permalink_structure' ),
			)
		);
	}

	/**
	 * Get site health snapshot.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_site_health( $request ) {
		$this->log_activity( 'get_site_health', $request );

		// Content counts by status.
		$post_counts = array();
		foreach ( array( 'post', 'page' ) as $type ) {
			$counts               = (array) wp_count_posts( $type );
			$post_counts[ $type ] = array();
			foreach ( $counts as $status => $count ) {
				if ( (int) $count > 0 ) {
					$post_counts[ $type ][ $status ] = (int) $count;
				}
			}
		}

		// Pages not in any menu.
		$menu_page_ids = array();
		$menus         = wp_get_nav_menus();
		foreach ( $menus as $menu ) {
			$items = wp_get_nav_menu_items( $menu->term_id );
			if ( $items ) {
				foreach ( $items as $item ) {
					if ( 'page' === $item->object ) {
						$menu_page_ids[] = (int) $item->object_id;
					}
				}
			}
		}
		$menu_page_ids = array_unique( $menu_page_ids );

		$all_pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		$orphan_pages = array_diff( $all_pages, $menu_page_ids );
		$orphan_list  = array();
		foreach ( array_slice( $orphan_pages, 0, 20 ) as $pid ) {
			$orphan_list[] = array(
				'id'    => $pid,
				'title' => get_the_title( $pid ),
				'slug'  => get_post_field( 'post_name', $pid ),
			);
		}

		// Pages missing featured images.
		$no_thumb    = array();
		$pages_query = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => '_thumbnail_id',
					'compare' => 'NOT EXISTS',
				),
				),
			)
		);
		foreach ( array_slice( $pages_query, 0, 20 ) as $pid ) {
			$no_thumb[] = array(
				'id'    => $pid,
				'title' => get_the_title( $pid ),
			);
		}

		// Active plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$plugins_list   = array();
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = get_plugins();
		foreach ( $active_plugins as $plugin_file ) {
			if ( isset( $all_plugins[ $plugin_file ] ) ) {
				$plugins_list[] = array(
					'name'    => $all_plugins[ $plugin_file ]['Name'],
					'version' => $all_plugins[ $plugin_file ]['Version'],
				);
			}
		}

		return $this->success_response(
			array(
				'content_counts'          => $post_counts,
				'orphan_pages'            => array(
					'count' => count( $orphan_pages ),
					'items' => $orphan_list,
				),
				'pages_missing_thumbnail' => array(
					'count' => count( $pages_query ),
					'items' => $no_thumb,
				),
				'active_plugins'          => $plugins_list,
				'wp_version'              => get_bloginfo( 'version' ),
				'php_version'             => PHP_VERSION,
				'permalink_structure'     => get_option( 'permalink_structure' ),
			)
		);
	}

	/**
	 * Create a taxonomy term.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function create_term( $request ) {
		$this->log_activity( 'create_term', $request );

		$taxonomy = sanitize_key( $request->get_param( 'taxonomy' ) );
		$name     = sanitize_text_field( $request->get_param( 'name' ) );

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return $this->error_response( 'invalid_taxonomy', __( 'Taxonomy does not exist.', 'site-pilot-ai' ), 400 );
		}

		$args = array();
		if ( $request->get_param( 'slug' ) ) {
			$args['slug'] = sanitize_title( $request->get_param( 'slug' ) );
		}
		if ( $request->get_param( 'description' ) ) {
			$args['description'] = sanitize_textarea_field( $request->get_param( 'description' ) );
		}
		if ( $request->get_param( 'parent' ) ) {
			$args['parent'] = absint( $request->get_param( 'parent' ) );
		}

		$result = wp_insert_term( $name, $taxonomy, $args );

		if ( is_wp_error( $result ) ) {
			return $this->error_response( $result->get_error_code(), $result->get_error_message(), 400 );
		}

		$term = get_term( $result['term_id'], $taxonomy );

		return $this->success_response(
			array(
				'id'          => $term->term_id,
				'name'        => $term->name,
				'slug'        => $term->slug,
				'taxonomy'    => $term->taxonomy,
				'description' => $term->description,
				'parent'      => $term->parent,
				'count'       => $term->count,
			),
			201
		);
	}

	/**
	 * Update a taxonomy term.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function update_term( $request ) {
		$this->log_activity( 'update_term', $request );

		$term_id  = absint( $request->get_param( 'id' ) );
		$taxonomy = sanitize_key( $request->get_param( 'taxonomy' ) );

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return $this->error_response( 'invalid_taxonomy', __( 'Taxonomy does not exist.', 'site-pilot-ai' ), 400 );
		}

		$term = get_term( $term_id, $taxonomy );
		if ( ! $term || is_wp_error( $term ) ) {
			return $this->error_response( 'not_found', __( 'Term not found.', 'site-pilot-ai' ), 404 );
		}

		$args = array();
		if ( $request->get_param( 'name' ) ) {
			$args['name'] = sanitize_text_field( $request->get_param( 'name' ) );
		}
		if ( $request->get_param( 'slug' ) ) {
			$args['slug'] = sanitize_title( $request->get_param( 'slug' ) );
		}
		if ( null !== $request->get_param( 'description' ) ) {
			$args['description'] = sanitize_textarea_field( $request->get_param( 'description' ) );
		}

		if ( empty( $args ) ) {
			return $this->error_response( 'no_changes', __( 'No fields to update.', 'site-pilot-ai' ), 400 );
		}

		$result = wp_update_term( $term_id, $taxonomy, $args );

		if ( is_wp_error( $result ) ) {
			return $this->error_response( $result->get_error_code(), $result->get_error_message(), 400 );
		}

		$term = get_term( $result['term_id'], $taxonomy );

		return $this->success_response(
			array(
				'id'          => $term->term_id,
				'name'        => $term->name,
				'slug'        => $term->slug,
				'taxonomy'    => $term->taxonomy,
				'description' => $term->description,
				'parent'      => $term->parent,
				'count'       => $term->count,
			)
		);
	}

	/**
	 * Delete a taxonomy term.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function delete_term( $request ) {
		$this->log_activity( 'delete_term', $request );

		$term_id  = absint( $request->get_param( 'id' ) );
		$taxonomy = sanitize_key( $request->get_param( 'taxonomy' ) );

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return $this->error_response( 'invalid_taxonomy', __( 'Taxonomy does not exist.', 'site-pilot-ai' ), 400 );
		}

		$term = get_term( $term_id, $taxonomy );
		if ( ! $term || is_wp_error( $term ) ) {
			return $this->error_response( 'not_found', __( 'Term not found.', 'site-pilot-ai' ), 404 );
		}

		$result = wp_delete_term( $term_id, $taxonomy );

		if ( is_wp_error( $result ) ) {
			return $this->error_response( $result->get_error_code(), $result->get_error_message(), 400 );
		}

		if ( false === $result ) {
			return $this->error_response( 'delete_failed', __( 'Failed to delete term.', 'site-pilot-ai' ), 500 );
		}

		return $this->success_response(
			array(
				'deleted' => true,
				'term_id' => $term_id,
			)
		);
	}

	/**
	 * Get rendered HTML for a page or URL (same-host only).
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function get_rendered_html( $request ) {
		$this->log_activity( 'get_rendered_html', $request );

		$post_id   = $request->get_param( 'id' );
		$url       = $request->get_param( 'url' );
		$selector  = $request->get_param( 'selector' );
		$max_bytes = min( absint( $request->get_param( 'max_bytes' ) ?: 51200 ), 204800 );

		// Resolve URL from post ID.
		if ( $post_id ) {
			$post = get_post( absint( $post_id ) );
			if ( ! $post ) {
				return $this->error_response( 'not_found', __( 'Post not found.', 'site-pilot-ai' ), 404 );
			}
			if ( 'publish' === $post->post_status ) {
				$url = get_permalink( $post_id );
			} else {
				// Draft/pending — use Elementor preview URL or plain preview.
				$url = add_query_arg( 'elementor-preview', $post_id, get_permalink( $post_id ) );
			}
		}

		if ( empty( $url ) ) {
			return $this->error_response( 'missing_param', __( 'Either id or url is required.', 'site-pilot-ai' ), 400 );
		}

		// SSRF guard: only allow same-host URLs.
		$site_host    = wp_parse_url( home_url(), PHP_URL_HOST );
		$request_host = wp_parse_url( $url, PHP_URL_HOST );

		if ( ! $request_host || strtolower( $request_host ) !== strtolower( $site_host ) ) {
			return $this->error_response(
				'ssrf_blocked',
				sprintf(
					/* translators: %s: allowed host */
					__( 'Only same-host URLs are allowed. Expected host: %s', 'site-pilot-ai' ),
					$site_host
				),
				403
			);
		}

		// Fetch the page.
		$response = wp_remote_get(
			$url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $this->error_response(
				'fetch_failed',
				$response->get_error_message(),
				502
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$html        = wp_remote_retrieve_body( $response );

		$selector_used  = null;
		$selector_found = true;

		// Extract by CSS selector if provided.
		if ( $selector && $html ) {
			$extracted = $this->extract_html_by_selector( $html, $selector );
			if ( null !== $extracted ) {
				$html           = $extracted;
				$selector_used  = $selector;
			} else {
				$selector_found = false;
				$selector_used  = $selector;
			}
		}

		// Truncate if needed.
		$truncated = false;
		if ( strlen( $html ) > $max_bytes ) {
			$html      = substr( $html, 0, $max_bytes );
			$truncated = true;
		}

		return $this->success_response(
			array(
				'url'            => $url,
				'status_code'    => $status_code,
				'html'           => $html,
				'length'         => strlen( $html ),
				'truncated'      => $truncated,
				'selector_used'  => $selector_used,
				'selector_found' => $selector_found,
			)
		);
	}

	/**
	 * Extract HTML content by a simple CSS selector (tag, .class, #id).
	 *
	 * @param string $html     Full HTML string.
	 * @param string $selector CSS selector (tag name, .class, or #id).
	 * @return string|null Extracted HTML or null if not found.
	 */
	private function extract_html_by_selector( $html, $selector ) {
		$doc = new DOMDocument();
		// Suppress warnings for malformed HTML.
		$prev = libxml_use_internal_errors( true );
		$doc->loadHTML( '<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		libxml_use_internal_errors( $prev );

		$xpath = new DOMXPath( $doc );

		// Build XPath from simple CSS selector.
		if ( '#' === $selector[0] ) {
			// ID selector.
			$id         = substr( $selector, 1 );
			$xpath_expr = sprintf( '//*[@id="%s"]', $id );
		} elseif ( '.' === $selector[0] ) {
			// Class selector.
			$class      = substr( $selector, 1 );
			$xpath_expr = sprintf( '//*[contains(concat(" ", normalize-space(@class), " "), " %s ")]', $class );
		} else {
			// Tag selector.
			$xpath_expr = '//' . $selector;
		}

		$nodes = $xpath->query( $xpath_expr );

		if ( ! $nodes || 0 === $nodes->length ) {
			return null;
		}

		$result = '';
		foreach ( $nodes as $node ) {
			$result .= $doc->saveHTML( $node );
		}

		return $result;
	}

	/**
	 * Get available guide topics or a specific guide.
	 *
	 * When called with a topic parameter, returns the full guide.
	 * When called without, returns the list of available topics.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object.
	 */
	public function get_guides( $request ) {
		$topic = $request->get_param( 'topic' );

		if ( ! empty( $topic ) ) {
			$guide = Spai_Guides::get_guide( $topic );
			if ( is_wp_error( $guide ) ) {
				return $guide;
			}
			return $this->success_response( $guide );
		}

		$topics = Spai_Guides::get_topics();
		return $this->success_response( array(
			'description' => 'Available guide topics. Use wp_get_guide(topic="...") to get the full guide for a topic.',
			'topics'      => $topics,
		) );
	}

	/**
	 * Get a specific guide by topic.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object.
	 */
	public function get_guide_topic( $request ) {
		$topic = $request->get_param( 'topic' );
		$guide = Spai_Guides::get_guide( $topic );

		if ( is_wp_error( $guide ) ) {
			return $guide;
		}

		return $this->success_response( $guide );
	}

	/**
	 * Get a specific workflow by name.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object.
	 */
	public function get_workflow( $request ) {
		$name     = $request->get_param( 'name' );
		$workflow = Spai_Workflows::get_workflow( $name );

		if ( is_wp_error( $workflow ) ) {
			return $workflow;
		}

		return $this->success_response( $workflow );
	}
}
