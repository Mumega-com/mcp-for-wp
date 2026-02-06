<?php
/**
 * MCP (Model Context Protocol) REST Controller
 *
 * Implements a native MCP endpoint for direct Claude Desktop connection.
 * Receives JSON-RPC 2.0 requests and translates them to internal REST API calls.
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MCP REST controller.
 *
 * Provides a streamable HTTP endpoint that follows the MCP specification.
 * Allows AI assistants like Claude to connect directly to WordPress without
 * needing external middleware or Cloudflare Workers.
 */
class Spai_REST_MCP extends Spai_REST_API {

	/**
	 * MCP protocol version.
	 *
	 * @var string
	 */
	private $protocol_version = '2024-11-05';

	/**
	 * Server name.
	 *
	 * @var string
	 */
	private $server_name = 'site-pilot-ai';

	/**
	 * Server version.
	 *
	 * @var string
	 */
	private $server_version;

	/**
	 * Tool definitions cache.
	 *
	 * @var array|null
	 */
	private $tools_cache = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->server_version = defined( 'SPAI_VERSION' ) ? SPAI_VERSION : '1.0.0';
	}

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Main MCP endpoint
		register_rest_route(
			$this->namespace,
			'/mcp',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'handle_mcp' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => 'OPTIONS',
					'callback'            => array( $this, 'handle_options' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * Handle OPTIONS request (CORS preflight).
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function handle_options( $request ) {
		$response = new WP_REST_Response( null, 200 );
		$this->add_cors_headers( $response );
		return $response;
	}

	/**
	 * Handle MCP request.
	 *
	 * Processes JSON-RPC 2.0 requests and returns appropriate responses.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function handle_mcp( $request ) {
		$body = $request->get_json_params();

		if ( empty( $body ) ) {
			return $this->jsonrpc_error_response(
				null,
				-32700,
				'Parse error: Invalid JSON'
			);
		}

		// Handle batch requests (array of requests) — limit batch size to prevent abuse.
		if ( isset( $body[0] ) ) {
			$max_batch = 10;
			if ( count( $body ) > $max_batch ) {
				return $this->jsonrpc_error_response(
					null,
					-32600,
					sprintf( 'Batch too large: maximum %d requests per batch.', $max_batch )
				);
			}

			$responses = array();
			foreach ( $body as $single_request ) {
				$response = $this->process_single_request( $single_request, $request );
				// Notifications return null, don't include in response
				if ( $response !== null ) {
					$responses[] = $response;
				}
			}

			$rest_response = new WP_REST_Response( array_values( $responses ), 200 );
			$this->add_cors_headers( $rest_response );
			return $rest_response;
		}

		// Single request
		$response = $this->process_single_request( $body, $request );

		if ( $response === null ) {
			// Notification - no response
			$rest_response = new WP_REST_Response( null, 204 );
			$this->add_cors_headers( $rest_response );
			return $rest_response;
		}

		$rest_response = new WP_REST_Response( $response, 200 );
		$this->add_cors_headers( $rest_response );
		return $rest_response;
	}

	/**
	 * Process a single JSON-RPC request.
	 *
	 * @param array           $body    JSON-RPC request body.
	 * @param WP_REST_Request $request Original REST request.
	 * @return array|null Response array or null for notifications.
	 */
	private function process_single_request( $body, $request ) {
		$method = isset( $body['method'] ) ? $body['method'] : '';
		$id     = isset( $body['id'] ) ? $body['id'] : null;
		$params = isset( $body['params'] ) ? $body['params'] : array();

		// Notifications have no id - don't respond
		if ( $id === null && 0 === strpos( $method, 'notifications/' ) ) {
			return null;
		}

		// Route to appropriate handler
		switch ( $method ) {
			case 'initialize':
				return $this->handle_initialize( $id, $params );

			case 'notifications/initialized':
				// Acknowledged, no response needed
				return null;

			case 'tools/list':
				return $this->handle_tools_list( $id );

			case 'tools/call':
				return $this->handle_tools_call( $id, $params, $request );

			case 'resources/list':
				return $this->handle_resources_list( $id );

			case 'resources/read':
				return $this->handle_resources_read( $id, $params );

			case 'ping':
				return $this->handle_ping( $id );

			default:
				return $this->jsonrpc_error(
					$id,
					-32601,
					'Method not found: ' . $method
				);
		}
	}

	/**
	 * Handle initialize method.
	 *
	 * @param mixed $id     Request ID.
	 * @param array $params Request parameters.
	 * @return array JSON-RPC response.
	 */
	private function handle_initialize( $id, $params ) {
		$client_info = isset( $params['clientInfo'] ) ? $params['clientInfo'] : array();

		$this->log_mcp_activity(
			'mcp_initialize',
			array(
				'client' => $client_info,
			)
		);

		return array(
			'jsonrpc' => '2.0',
			'id'      => $id,
			'result'  => array(
				'protocolVersion' => $this->protocol_version,
				'serverInfo'      => array(
					'name'    => $this->server_name,
					'version' => $this->server_version,
				),
				'capabilities'    => array(
					'tools'     => (object) array(), // Empty object indicates tools are supported
					'resources' => array(
						'subscribe'   => false,
						'listChanged' => false,
					),
				),
			),
		);
	}

	/**
	 * Handle ping method.
	 *
	 * @param mixed $id Request ID.
	 * @return array JSON-RPC response.
	 */
	private function handle_ping( $id ) {
		return array(
			'jsonrpc' => '2.0',
			'id'      => $id,
			'result'  => array(
				'pong' => true,
			),
		);
	}

	/**
	 * Handle tools/list method.
	 *
	 * @param mixed $id Request ID.
	 * @return array JSON-RPC response.
	 */
	private function handle_tools_list( $id ) {
		$this->log_mcp_activity( 'mcp_tools_list', array() );

		$tools = $this->get_tool_definitions();

		return array(
			'jsonrpc' => '2.0',
			'id'      => $id,
			'result'  => array(
				'tools' => $tools,
			),
		);
	}

	/**
	 * Handle resources/list method.
	 *
	 * @param mixed $id Request ID.
	 * @return array JSON-RPC response.
	 */
	private function handle_resources_list( $id ) {
		return array(
			'jsonrpc' => '2.0',
			'id'      => $id,
			'result'  => array(
				'resources' => array(),
			),
		);
	}

	/**
	 * Handle resources/read method.
	 *
	 * @param mixed $id     Request ID.
	 * @param array $params Request parameters.
	 * @return array JSON-RPC response.
	 */
	private function handle_resources_read( $id, $params ) {
		$uri = isset( $params['uri'] ) ? (string) $params['uri'] : '';

		if ( '' === $uri ) {
			return $this->jsonrpc_error( $id, -32602, 'Missing resource URI' );
		}

		return $this->jsonrpc_error( $id, -32002, 'Resource not found', array( 'uri' => $uri ) );
	}

	/**
	 * Handle tools/call method.
	 *
	 * Executes the requested tool by dispatching to internal REST API.
	 *
	 * @param mixed           $id      Request ID.
	 * @param array           $params  Tool parameters.
	 * @param WP_REST_Request $request Original REST request.
	 * @return array JSON-RPC response.
	 */
	private function handle_tools_call( $id, $params, $request ) {
		$tool_name = isset( $params['name'] ) ? $params['name'] : '';
		$arguments = isset( $params['arguments'] ) ? $params['arguments'] : array();

		if ( empty( $tool_name ) ) {
			return $this->jsonrpc_error( $id, -32602, 'Missing tool name' );
		}

		$this->log_mcp_activity(
			'mcp_tool_call',
			array(
				'tool' => $tool_name,
				'args' => $arguments,
			)
		);

		$tool_map = $this->get_tool_map();

		if ( ! isset( $tool_map[ $tool_name ] ) ) {
			return $this->jsonrpc_error( $id, -32602, 'Unknown tool: ' . $tool_name );
		}

		$mapping = $tool_map[ $tool_name ];
		$route   = $mapping['route'];
		$method  = $mapping['method'];

		// Substitute path parameters (e.g., {id})
		foreach ( $arguments as $key => $value ) {
			$placeholder = '{' . $key . '}';
			if ( false !== strpos( $route, $placeholder ) ) {
				$route = str_replace( $placeholder, $value, $route );
				unset( $arguments[ $key ] );
			}
		}

		// Build internal REST request
		$internal_request = new WP_REST_Request( $method, '/site-pilot-ai/v1' . $route );

		// Set remaining arguments as params
		foreach ( $arguments as $key => $value ) {
			$internal_request->set_param( $key, $value );
		}

		// Copy authentication from current request
		$api_key = $this->get_api_key_from_request( $request );
		if ( $api_key ) {
			$internal_request->set_header( 'X-API-Key', $api_key );
		}

		// Dispatch internally
		$response = rest_do_request( $internal_request );
		$data     = $response->get_data();
		$status   = $response->get_status();

		// Check for errors
		$is_error = $status >= 400;

		if ( $is_error && isset( $data['code'] ) && isset( $data['message'] ) ) {
			// WordPress REST error format
			return $this->jsonrpc_error(
				$id,
				-32000,
				'Tool execution failed: ' . $data['message'],
				$data
			);
		}

		// Return successful result
		return array(
			'jsonrpc' => '2.0',
			'id'      => $id,
			'result'  => array(
				'content' => array(
					array(
						'type' => 'text',
						'text' => wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ),
					),
				),
				'isError' => $is_error,
			),
		);
	}

	/**
	 * Get tool definitions for all available tools.
	 *
	 * @return array Tool definitions.
	 */
	private function get_tool_definitions() {
		if ( $this->tools_cache !== null ) {
			return $this->tools_cache;
		}

		$tools = array();

		// Add FREE tools (always available)
		$tools[] = $this->define_tool(
			'wp_site_info',
			'Get WordPress site information including name, URL, version, theme, active plugins, and content counts',
			array()
		);

		$tools[] = $this->define_tool(
			'wp_analytics',
			'Get site analytics including post counts, page counts, comment counts, and user counts',
			array(
				'days' => array(
					'type'        => 'number',
					'description' => 'Number of days for analytics period',
					'default'     => 30,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_detect_plugins',
			'Detect active plugins and available capabilities (Elementor, WooCommerce, SEO plugins, etc.)',
			array()
		);

		$tools[] = $this->define_tool(
			'wp_list_posts',
			'List blog posts with optional filters for status, category, search, and pagination',
			array(
				'per_page' => array(
					'type'        => 'number',
					'description' => 'Number of posts per page (1-100)',
					'default'     => 10,
				),
				'page'     => array(
					'type'        => 'number',
					'description' => 'Current page number',
					'default'     => 1,
				),
				'status'   => array(
					'type'        => 'string',
					'description' => 'Post status filter (publish, draft, pending, private)',
					'default'     => 'publish',
				),
				'category' => array(
					'type'        => 'number',
					'description' => 'Category ID filter',
				),
				'search'   => array(
					'type'        => 'string',
					'description' => 'Search term',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_create_post',
			'Create a new blog post. Defaults to draft status. Set status to "publish" to publish immediately.',
			array(
				'title'   => array(
					'type'        => 'string',
					'description' => 'Post title',
					'required'    => true,
				),
				'content' => array(
					'type'        => 'string',
					'description' => 'Post content (HTML)',
					'default'     => '',
				),
				'status'  => array(
					'type'        => 'string',
					'description' => 'Post status (publish, draft, pending, private)',
					'default'     => 'draft',
				),
				'excerpt' => array(
					'type'        => 'string',
					'description' => 'Post excerpt',
					'default'     => '',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_update_post',
			'Update an existing blog post',
			array(
				'id'      => array(
					'type'        => 'number',
					'description' => 'Post ID',
					'required'    => true,
				),
				'title'   => array(
					'type'        => 'string',
					'description' => 'Post title',
				),
				'content' => array(
					'type'        => 'string',
					'description' => 'Post content (HTML)',
				),
				'status'  => array(
					'type'        => 'string',
					'description' => 'Post status (publish, draft, pending, private)',
				),
				'excerpt' => array(
					'type'        => 'string',
					'description' => 'Post excerpt',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_delete_post',
			'Delete a blog post',
			array(
				'id'    => array(
					'type'        => 'number',
					'description' => 'Post ID',
					'required'    => true,
				),
				'force' => array(
					'type'        => 'boolean',
					'description' => 'Force permanent deletion (bypass trash)',
					'default'     => false,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_list_pages',
			'List pages with optional filters for status, search, and pagination',
			array(
				'per_page' => array(
					'type'        => 'number',
					'description' => 'Number of pages per page (1-100)',
					'default'     => 10,
				),
				'page'     => array(
					'type'        => 'number',
					'description' => 'Current page number',
					'default'     => 1,
				),
				'status'   => array(
					'type'        => 'string',
					'description' => 'Page status filter (publish, draft, pending, private)',
					'default'     => 'publish',
				),
				'search'   => array(
					'type'        => 'string',
					'description' => 'Search term',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_create_page',
			'Create a new page. Defaults to draft status.',
			array(
				'title'   => array(
					'type'        => 'string',
					'description' => 'Page title',
					'required'    => true,
				),
				'content' => array(
					'type'        => 'string',
					'description' => 'Page content (HTML)',
					'default'     => '',
				),
				'status'  => array(
					'type'        => 'string',
					'description' => 'Page status (publish, draft, pending, private)',
					'default'     => 'draft',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_update_page',
			'Update an existing page',
			array(
				'id'      => array(
					'type'        => 'number',
					'description' => 'Page ID',
					'required'    => true,
				),
				'title'   => array(
					'type'        => 'string',
					'description' => 'Page title',
				),
				'content' => array(
					'type'        => 'string',
					'description' => 'Page content (HTML)',
				),
				'status'  => array(
					'type'        => 'string',
					'description' => 'Page status (publish, draft, pending, private)',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_upload_media',
			'Upload a media file (image, video, etc.) to the WordPress media library',
			array(
				'file' => array(
					'type'        => 'string',
					'description' => 'Base64-encoded file content or file URL',
					'required'    => true,
				),
				'name' => array(
					'type'        => 'string',
					'description' => 'File name',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_upload_media_from_url',
			'Upload a media file from a URL',
			array(
				'url' => array(
					'type'        => 'string',
					'description' => 'URL of the file to upload',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_list_drafts',
			'List all draft posts and pages',
			array(
				'type' => array(
					'type'        => 'string',
					'description' => 'Post type filter (post, page, all)',
					'default'     => 'all',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_delete_all_drafts',
			'Delete all draft posts and pages (use with caution)',
			array(
				'type'  => array(
					'type'        => 'string',
					'description' => 'Post type filter (post, page, all)',
					'default'     => 'all',
				),
				'force' => array(
					'type'        => 'boolean',
					'description' => 'Force permanent deletion',
					'default'     => false,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_get_elementor',
			'Get Elementor page data for a specific page or post',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Page or post ID',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_set_elementor',
			'Set Elementor page data for a specific page or post',
			array(
				'id'             => array(
					'type'        => 'number',
					'description' => 'Page or post ID',
					'required'    => true,
				),
				'elementor_data' => array(
					'type'        => 'string',
					'description' => 'Elementor JSON data',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_elementor_status',
			'Check if Elementor is active and get Elementor status information',
			array()
		);

		$tools[] = $this->define_tool(
			'wp_list_api_keys',
			'List scoped API keys (metadata only)',
			array(
				'include_revoked' => array(
					'type'        => 'boolean',
					'description' => 'Include revoked keys in results',
					'default'     => false,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_create_api_key',
			'Create a scoped API key and return plaintext value once',
			array(
				'label' => array(
					'type'        => 'string',
					'description' => 'Human-readable key label',
				),
				'scopes' => array(
					'type'        => 'array',
					'description' => 'Key scopes (read, write, admin)',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_revoke_api_key',
			'Revoke a scoped API key by id',
			array(
				'id' => array(
					'type'        => 'string',
					'description' => 'Scoped API key id',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_rate_limit_status',
			'Get current rate-limit settings and usage for the calling identifier',
			array()
		);

		$tools[] = $this->define_tool(
			'wp_update_rate_limit',
			'Update rate-limit settings (admin only)',
			array(
				'enabled'             => array(
					'type'        => 'boolean',
					'description' => 'Enable or disable rate limiting',
				),
				'requests_per_minute' => array(
					'type'        => 'number',
					'description' => 'Requests allowed per minute',
				),
				'requests_per_hour'   => array(
					'type'        => 'number',
					'description' => 'Requests allowed per hour',
				),
				'burst_limit'         => array(
					'type'        => 'number',
					'description' => 'Requests allowed in short burst window',
				),
				'whitelist'           => array(
					'type'        => 'array',
					'description' => 'Identifiers to bypass rate limiting',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_reset_rate_limit',
			'Reset rate-limit counters for an identifier (admin only)',
			array(
				'identifier' => array(
					'type'        => 'string',
					'description' => 'Identifier to reset (for example key:<id> or IP)',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_list_webhook_events',
			'List available webhook event names',
			array()
		);

		$tools[] = $this->define_tool(
			'wp_list_webhooks',
			'List webhooks with optional filters',
			array(
				'status'   => array(
					'type'        => 'string',
					'description' => 'Status filter (active, disabled, all)',
				),
				'per_page' => array(
					'type'        => 'number',
					'description' => 'Results per page',
					'default'     => 50,
				),
				'page'     => array(
					'type'        => 'number',
					'description' => 'Page number',
					'default'     => 1,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_create_webhook',
			'Create a webhook endpoint subscription',
			array(
				'name'   => array(
					'type'        => 'string',
					'description' => 'Webhook display name',
					'required'    => true,
				),
				'url'    => array(
					'type'        => 'string',
					'description' => 'Webhook target URL',
					'required'    => true,
				),
				'events' => array(
					'type'        => 'array',
					'description' => 'Events to subscribe to',
					'required'    => true,
				),
				'secret' => array(
					'type'        => 'string',
					'description' => 'Optional signing secret',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_update_webhook',
			'Update an existing webhook',
			array(
				'id'     => array(
					'type'        => 'number',
					'description' => 'Webhook ID',
					'required'    => true,
				),
				'name'   => array(
					'type'        => 'string',
					'description' => 'Webhook display name',
				),
				'url'    => array(
					'type'        => 'string',
					'description' => 'Webhook target URL',
				),
				'events' => array(
					'type'        => 'array',
					'description' => 'Updated event list',
				),
				'status' => array(
					'type'        => 'string',
					'description' => 'Webhook status (active or disabled)',
				),
				'secret' => array(
					'type'        => 'string',
					'description' => 'Webhook signing secret',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_delete_webhook',
			'Delete a webhook',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Webhook ID',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_test_webhook',
			'Send a test delivery for a webhook',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Webhook ID',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_list_webhook_logs',
			'List delivery logs for a webhook',
			array(
				'id'       => array(
					'type'        => 'number',
					'description' => 'Webhook ID',
					'required'    => true,
				),
				'per_page' => array(
					'type'        => 'number',
					'description' => 'Results per page',
					'default'     => 50,
				),
				'page'     => array(
					'type'        => 'number',
					'description' => 'Page number',
					'default'     => 1,
				),
			)
		);

		// Add PRO tools if pro version is active
		if ( $this->is_pro_active() ) {
			$tools = array_merge( $tools, $this->get_pro_tool_definitions() );
		}

		$this->tools_cache = $tools;
		return $tools;
	}

	/**
	 * Get PRO tool definitions.
	 *
	 * @return array PRO tool definitions.
	 */
	private function get_pro_tool_definitions() {
		$pro_tools = array();

		// SEO Tools
		$pro_tools[] = $this->define_tool(
			'wp_get_seo',
			'Get SEO metadata for a specific page or post (Yoast, Rank Math, etc.)',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Page or post ID',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_set_seo',
			'Set SEO metadata for a specific page or post',
			array(
				'id'              => array(
					'type'        => 'number',
					'description' => 'Page or post ID',
					'required'    => true,
				),
				'seo_title'       => array(
					'type'        => 'string',
					'description' => 'SEO title',
				),
				'seo_description' => array(
					'type'        => 'string',
					'description' => 'SEO meta description',
				),
				'focus_keyword'   => array(
					'type'        => 'string',
					'description' => 'Focus keyword',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_analyze_seo',
			'Analyze SEO for a specific page or post',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Page or post ID',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_bulk_seo',
			'Update SEO metadata for multiple posts/pages',
			array(
				'items' => array(
					'type'        => 'array',
					'description' => 'Array of items with id and SEO data',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_seo_status',
			'Get SEO plugin status and configuration',
			array()
		);

		// Form Tools (Read-only)
		$pro_tools[] = $this->define_tool(
			'wp_list_forms',
			'List all forms from supported plugins (Contact Form 7, WPForms, Gravity Forms)',
			array()
		);

		$pro_tools[] = $this->define_tool(
			'wp_get_form',
			'Get form details from a specific form plugin',
			array(
				'plugin' => array(
					'type'        => 'string',
					'description' => 'Form plugin (cf7, wpforms, gravityforms)',
					'required'    => true,
				),
				'id'     => array(
					'type'        => 'number',
					'description' => 'Form ID',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_get_form_entries',
			'Get form entries/submissions from a specific form',
			array(
				'plugin' => array(
					'type'        => 'string',
					'description' => 'Form plugin (cf7, wpforms, gravityforms)',
					'required'    => true,
				),
				'id'     => array(
					'type'        => 'number',
					'description' => 'Form ID',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_forms_status',
			'Get status of all installed form plugins',
			array()
		);

		// Elementor Pro Tools
		$pro_tools[] = $this->define_tool(
			'wp_list_elementor_templates',
			'List all Elementor templates',
			array()
		);

		$pro_tools[] = $this->define_tool(
			'wp_apply_elementor_template',
			'Apply an Elementor template to a page',
			array(
				'template_id' => array(
					'type'        => 'number',
					'description' => 'Template ID',
					'required'    => true,
				),
				'page_id'     => array(
					'type'        => 'number',
					'description' => 'Page ID to apply template to',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_create_landing_page',
			'Create a new landing page with Elementor',
			array(
				'title'       => array(
					'type'        => 'string',
					'description' => 'Page title',
					'required'    => true,
				),
				'template_id' => array(
					'type'        => 'number',
					'description' => 'Optional template ID to use',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_clone_elementor_page',
			'Clone an Elementor page',
			array(
				'source_id' => array(
					'type'        => 'number',
					'description' => 'Source page ID to clone',
					'required'    => true,
				),
				'title'     => array(
					'type'        => 'string',
					'description' => 'Title for the new cloned page',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_get_elementor_globals',
			'Get Elementor global settings (colors, fonts, etc.)',
			array()
		);

		$pro_tools[] = $this->define_tool(
			'wp_get_elementor_widgets',
			'Get list of available Elementor widgets',
			array()
		);

		return $pro_tools;
	}

	/**
	 * Define a tool for MCP.
	 *
	 * @param string $name        Tool name.
	 * @param string $description Tool description.
	 * @param array  $input_props Input schema properties.
	 * @return array Tool definition.
	 */
	private function define_tool( $name, $description, $input_props ) {
		$properties = array();
		$required   = array();

		foreach ( $input_props as $prop_name => $prop_def ) {
			$properties[ $prop_name ] = array(
				'type'        => isset( $prop_def['type'] ) ? $prop_def['type'] : 'string',
				'description' => isset( $prop_def['description'] ) ? $prop_def['description'] : '',
			);

			if ( isset( $prop_def['default'] ) ) {
				$properties[ $prop_name ]['default'] = $prop_def['default'];
			}

			if ( ! empty( $prop_def['required'] ) ) {
				$required[] = $prop_name;
			}
		}

		$schema = array(
			'type'       => 'object',
			'properties' => $properties,
		);

		if ( ! empty( $required ) ) {
			$schema['required'] = $required;
		}

		return array(
			'name'        => $name,
			'description' => $description,
			'inputSchema' => $schema,
			'annotations' => $this->get_tool_annotations( $name ),
		);
	}

	/**
	 * Get tool annotations for MCP compatibility.
	 *
	 * @param string $name Tool name.
	 * @return array<string,bool> Tool annotation hints.
	 */
	private function get_tool_annotations( $name ) {
		return array(
			'readOnlyHint'    => $this->is_read_only_tool( $name ),
			'openWorldHint'   => $this->is_open_world_tool( $name ),
			'destructiveHint' => $this->is_destructive_tool( $name ),
		);
	}

	/**
	 * Determine whether a tool is read-only.
	 *
	 * @param string $name Tool name.
	 * @return bool True when tool does not modify data.
	 */
	private function is_read_only_tool( $name ) {
		$tool_map = $this->get_tool_map();
		if ( empty( $tool_map[ $name ]['method'] ) ) {
			return false;
		}

		$method = strtoupper( (string) $tool_map[ $name ]['method'] );
		return in_array( $method, array( 'GET', 'HEAD', 'OPTIONS' ), true );
	}

	/**
	 * Determine whether a tool can access external systems.
	 *
	 * @param string $name Tool name.
	 * @return bool True when tool may interact with external services.
	 */
	private function is_open_world_tool( $name ) {
		$open_world_tools = array(
			'wp_upload_media_from_url',
			'wp_test_webhook',
		);

		return in_array( $name, $open_world_tools, true );
	}

	/**
	 * Determine whether a tool performs destructive actions.
	 *
	 * @param string $name Tool name.
	 * @return bool True when tool can delete/revoke/reset data.
	 */
	private function is_destructive_tool( $name ) {
		$destructive_tools = array(
			'wp_delete_post',
			'wp_delete_all_drafts',
			'wp_revoke_api_key',
			'wp_reset_rate_limit',
			'wp_delete_webhook',
		);

		if ( in_array( $name, $destructive_tools, true ) ) {
			return true;
		}

		$tool_map = $this->get_tool_map();
		if ( empty( $tool_map[ $name ]['method'] ) ) {
			return false;
		}

		return 'DELETE' === strtoupper( (string) $tool_map[ $name ]['method'] );
	}

	/**
	 * Get tool mapping (tool name -> REST route).
	 *
	 * @return array Tool mapping.
	 */
	private function get_tool_map() {
		$map = array(
			// Site & Analytics
			'wp_site_info'      => array(
				'method' => 'GET',
				'route'  => '/site-info',
			),
			'wp_analytics'      => array(
				'method' => 'GET',
				'route'  => '/analytics',
			),
			'wp_detect_plugins' => array(
				'method' => 'GET',
				'route'  => '/plugins',
			),

			// Posts
			'wp_list_posts'     => array(
				'method' => 'GET',
				'route'  => '/posts',
			),
			'wp_create_post'    => array(
				'method' => 'POST',
				'route'  => '/posts',
			),
			'wp_update_post'    => array(
				'method' => 'POST',
				'route'  => '/posts/{id}',
			),
			'wp_delete_post'    => array(
				'method' => 'DELETE',
				'route'  => '/posts/{id}',
			),

			// Pages
			'wp_list_pages'     => array(
				'method' => 'GET',
				'route'  => '/pages',
			),
			'wp_create_page'    => array(
				'method' => 'POST',
				'route'  => '/pages',
			),
			'wp_update_page'    => array(
				'method' => 'POST',
				'route'  => '/pages/{id}',
			),

			// Media
			'wp_upload_media'          => array(
				'method' => 'POST',
				'route'  => '/media',
			),
			'wp_upload_media_from_url' => array(
				'method' => 'POST',
				'route'  => '/media/from-url',
			),

			// Drafts
			'wp_list_drafts'           => array(
				'method' => 'GET',
				'route'  => '/drafts',
			),
			'wp_delete_all_drafts'     => array(
				'method' => 'DELETE',
				'route'  => '/drafts/delete-all',
			),

			// Elementor Basic
			'wp_get_elementor'         => array(
				'method' => 'GET',
				'route'  => '/elementor/{id}',
			),
			'wp_set_elementor'         => array(
				'method' => 'POST',
				'route'  => '/elementor/{id}',
			),
			'wp_elementor_status'      => array(
				'method' => 'GET',
				'route'  => '/elementor/status',
			),
			'wp_list_api_keys'        => array(
				'method' => 'GET',
				'route'  => '/api-keys',
			),
			'wp_create_api_key'       => array(
				'method' => 'POST',
				'route'  => '/api-keys',
			),
			'wp_revoke_api_key'       => array(
				'method' => 'DELETE',
				'route'  => '/api-keys/{id}',
			),
			'wp_rate_limit_status'    => array(
				'method' => 'GET',
				'route'  => '/rate-limit',
			),
			'wp_update_rate_limit'    => array(
				'method' => 'POST',
				'route'  => '/rate-limit',
			),
			'wp_reset_rate_limit'     => array(
				'method' => 'POST',
				'route'  => '/rate-limit/reset',
			),
			'wp_list_webhook_events'  => array(
				'method' => 'GET',
				'route'  => '/webhooks/events',
			),
			'wp_list_webhooks'        => array(
				'method' => 'GET',
				'route'  => '/webhooks',
			),
			'wp_create_webhook'       => array(
				'method' => 'POST',
				'route'  => '/webhooks',
			),
			'wp_update_webhook'       => array(
				'method' => 'POST',
				'route'  => '/webhooks/{id}',
			),
			'wp_delete_webhook'       => array(
				'method' => 'DELETE',
				'route'  => '/webhooks/{id}',
			),
			'wp_test_webhook'         => array(
				'method' => 'POST',
				'route'  => '/webhooks/{id}/test',
			),
			'wp_list_webhook_logs'    => array(
				'method' => 'GET',
				'route'  => '/webhooks/{id}/logs',
			),
		);

		// Add PRO routes if pro is active
		if ( $this->is_pro_active() ) {
			$map = array_merge( $map, $this->get_pro_tool_map() );
		}

		return $map;
	}

	/**
	 * Get PRO tool mapping.
	 *
	 * @return array PRO tool mapping.
	 */
	private function get_pro_tool_map() {
		return array(
			// SEO
			'wp_get_seo'                     => array(
				'method' => 'GET',
				'route'  => '/seo/{id}',
			),
			'wp_set_seo'                     => array(
				'method' => 'POST',
				'route'  => '/seo/{id}',
			),
			'wp_analyze_seo'                 => array(
				'method' => 'GET',
				'route'  => '/seo/{id}/analyze',
			),
			'wp_bulk_seo'                    => array(
				'method' => 'POST',
				'route'  => '/seo/bulk',
			),
			'wp_seo_status'                  => array(
				'method' => 'GET',
				'route'  => '/seo/status',
			),

			// Forms
			'wp_list_forms'                  => array(
				'method' => 'GET',
				'route'  => '/forms',
			),
			'wp_get_form'                    => array(
				'method' => 'GET',
				'route'  => '/forms/{plugin}/{id}',
			),
			'wp_get_form_entries'            => array(
				'method' => 'GET',
				'route'  => '/forms/{plugin}/{id}/entries',
			),
			'wp_forms_status'                => array(
				'method' => 'GET',
				'route'  => '/forms/status',
			),

			// Elementor Pro
			'wp_list_elementor_templates'    => array(
				'method' => 'GET',
				'route'  => '/elementor/templates',
			),
			'wp_apply_elementor_template'    => array(
				'method' => 'POST',
				'route'  => '/elementor/templates/{template_id}/apply',
			),
			'wp_create_landing_page'         => array(
				'method' => 'POST',
				'route'  => '/elementor/landing-page',
			),
			'wp_clone_elementor_page'        => array(
				'method' => 'POST',
				'route'  => '/elementor/clone',
			),
			'wp_get_elementor_globals'       => array(
				'method' => 'GET',
				'route'  => '/elementor/globals',
			),
			'wp_get_elementor_widgets'       => array(
				'method' => 'GET',
				'route'  => '/elementor/widgets',
			),
		);
	}

	/**
	 * Check if PRO version is active.
	 *
	 * @return bool True if PRO is active.
	 */
	private function is_pro_active() {
		// Check if PRO plugin is active
		// This can be customized based on how the PRO plugin is detected
		return function_exists( 'spai_pro' ) || defined( 'SPAI_PRO_VERSION' );
	}

	/**
	 * Create a JSON-RPC error response.
	 *
	 * @param mixed  $id      Request ID.
	 * @param int    $code    Error code.
	 * @param string $message Error message.
	 * @param mixed  $data    Optional error data.
	 * @return array JSON-RPC error response.
	 */
	private function jsonrpc_error( $id, $code, $message, $data = null ) {
		$error = array(
			'code'    => $code,
			'message' => $message,
		);

		if ( $data !== null ) {
			$error['data'] = $data;
		}

		return array(
			'jsonrpc' => '2.0',
			'id'      => $id,
			'error'   => $error,
		);
	}

	/**
	 * Create a JSON-RPC error response (legacy method for backward compatibility).
	 *
	 * @param mixed  $id      Request ID.
	 * @param int    $code    Error code.
	 * @param string $message Error message.
	 * @return WP_REST_Response Error response.
	 */
	private function jsonrpc_error_response( $id, $code, $message ) {
		$response = new WP_REST_Response(
			$this->jsonrpc_error( $id, $code, $message ),
			200 // JSON-RPC errors return 200 with error in body
		);
		$this->add_cors_headers( $response );
		return $response;
	}

	/**
	 * Add CORS headers to response.
	 *
	 * @param WP_REST_Response $response Response object.
	 */
	private function add_cors_headers( $response ) {
		// Use configured allowed origins; fall back to site URL only.
		$settings = get_option( 'spai_settings', array() );
		$allowed  = ! empty( $settings['allowed_origins'] )
			? array_map( 'trim', explode( ',', $settings['allowed_origins'] ) )
			: array();

		$origin = isset( $_SERVER['HTTP_ORIGIN'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_ORIGIN'] ) ) : '';

		if ( ! empty( $origin ) && in_array( $origin, $allowed, true ) ) {
			$response->header( 'Access-Control-Allow-Origin', $origin );
			$response->header( 'Vary', 'Origin' );
		} elseif ( empty( $allowed ) ) {
			// No origins configured: allow all (MCP clients are non-browser).
			// Site owners can restrict via Settings > Allowed Origins.
			$response->header( 'Access-Control-Allow-Origin', '*' );
		}
		// If origins are configured but request origin doesn't match, no CORS header is sent.

		$response->header( 'Access-Control-Allow-Methods', 'POST, OPTIONS' );
		$response->header( 'Access-Control-Allow-Headers', 'Content-Type, X-API-Key, Mcp-Session-Id, Authorization' );
		$response->header( 'Access-Control-Max-Age', '86400' );
	}

	/**
	 * Log MCP activity.
	 *
	 * @param string $action  Action name.
	 * @param mixed  $context Context data.
	 */
	private function log_mcp_activity( $action, $context ) {
		$settings = get_option( 'spai_settings', array() );
		if ( empty( $settings['enable_logging'] ) ) {
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'spai_activity_log';

		$wpdb->insert(
			$table,
			array(
				'action'       => sanitize_key( $action ),
				'endpoint'     => '/site-pilot-ai/v1/mcp',
				'method'       => 'POST',
				'status_code'  => 200,
				'ip_address'   => $this->get_client_ip_for_logging(),
				'user_agent'   => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
				'request_data' => wp_json_encode( $context ),
				'created_at'   => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s' )
		);
	}
}
