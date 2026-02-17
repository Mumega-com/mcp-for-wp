<?php
/**
 * MCP Free Tools Registry
 *
 * Contains all free (always available) MCP tool definitions and route mappings.
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Free tools registry for MCP.
 *
 * Provides tool definitions and route mappings for all free tier tools.
 */
class Spai_MCP_Free_Tools extends Spai_MCP_Tool_Registry {

	/**
	 * Get destructive tool names for free tier.
	 *
	 * @return array Destructive tool names.
	 */
	protected function get_destructive_tools() {
		return array(
			'wp_delete_post',
			'wp_delete_page',
			'wp_delete_all_drafts',
			'wp_delete_menu',
			'wp_delete_menu_item',
			'wp_revoke_api_key',
			'wp_reset_rate_limit',
			'wp_delete_webhook',
		);
	}

	/**
	 * Get open world tool names for free tier.
	 *
	 * @return array Open world tool names.
	 */
	protected function get_open_world_tools() {
		return array(
			'wp_upload_media_from_url',
			'wp_test_webhook',
			'wp_screenshot_url',
		);
	}

	/**
	 * Get required capabilities for free tools.
	 *
	 * @return array Map of tool_name => capability_key.
	 */
	public function get_required_capabilities() {
		return array(
			'wp_get_elementor'            => 'elementor',
			'wp_set_elementor'            => 'elementor',
			'wp_elementor_status'         => 'elementor',
			'wp_regenerate_elementor_css' => 'elementor',
			'wp_bulk_find_replace'        => 'elementor',
		);
	}

	/**
	 * Get tool definitions for free tier.
	 *
	 * @return array Tool definitions.
	 */
	public function get_tools() {
		$tools = array();

		// Site & Analytics
		$tools[] = $this->define_tool(
			'wp_site_info',
			'Get WordPress site information including name, URL, version, theme, active plugins, and content counts',
			array()
		);

		$tools[] = $this->define_tool(
			'wp_introspect',
			'Get a machine-readable description of this plugin (auth, endpoints, tools, capabilities) so AI clients can self-configure instead of guessing',
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
			'wp_get_options',
			'Get WordPress reading options (front page, posts page, and related settings)',
			array()
		);

		$tools[] = $this->define_tool(
			'wp_update_options',
			'Update WordPress reading options (set static homepage, posts page, visibility)',
			array(
				'show_on_front' => array(
					'type'        => 'string',
					'description' => "Reading setting: 'posts' or 'page'",
				),
				'page_on_front' => array(
					'type'        => 'number',
					'description' => 'Front page ID (0 to unset)',
				),
				'page_for_posts' => array(
					'type'        => 'number',
					'description' => 'Posts page ID (0 to unset)',
				),
				'blog_public' => array(
					'type'        => 'boolean',
					'description' => 'Search engine visibility (true to allow indexing)',
				),
			)
		);

		// Custom CSS
		$tools[] = $this->define_tool(
			'wp_get_custom_css',
			'Get the Additional CSS from the WordPress Customizer. Returns the full CSS string currently applied to the site.',
			array()
		);

		$tools[] = $this->define_tool(
			'wp_set_custom_css',
			'Set or append CSS to the WordPress Customizer Additional CSS. Use mode "append" to add new rules without removing existing ones, or "replace" to overwrite all custom CSS. CSS is applied site-wide immediately.',
			array(
				'css' => array(
					'type'        => 'string',
					'description' => 'CSS code to set or append',
					'required'    => true,
				),
				'mode' => array(
					'type'        => 'string',
					'description' => 'How to apply: "replace" overwrites all CSS, "append" adds to existing (default)',
					'enum'        => array( 'replace', 'append' ),
					'default'     => 'append',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_list_menus',
			'List all navigation menus (including unassigned ones) with id, name, slug, and item count',
			array()
		);

		$tools[] = $this->define_tool(
			'wp_list_menu_locations',
			'List theme menu locations and which menus are assigned',
			array()
		);

		$tools[] = $this->define_tool(
			'wp_setup_menu',
			'Create a menu, add page links, and assign it to a theme menu location',
			array(
				'name' => array(
					'type'        => 'string',
					'description' => 'Menu name',
					'required'    => true,
				),
				'location' => array(
					'type'        => 'string',
					'description' => 'Theme menu location key (e.g., primary)',
				),
				'page_ids' => array(
					'type'        => 'array',
					'description' => 'Array of page IDs to add as menu items',
					'items'       => array( 'type' => 'number' ),
					'default'     => array(),
				),
				'overwrite' => array(
					'type'        => 'boolean',
					'description' => 'If true, creates a new menu even if name exists',
					'default'     => false,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_list_menu_items',
			'List all items in a menu with their titles, URLs, types, and parent/child relationships',
			array(
				'menu_id' => array(
					'type'        => 'number',
					'description' => 'Menu ID to list items for',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_add_menu_item',
			'Add a menu item: custom link (any URL), post type (page, post, product), or taxonomy (category, tag). Supports sub-menus via parent_id.',
			array(
				'menu_id'   => array(
					'type'        => 'number',
					'description' => 'Menu ID to add item to',
					'required'    => true,
				),
				'title'     => array(
					'type'        => 'string',
					'description' => 'Menu item label',
					'required'    => true,
				),
				'type'      => array(
					'type'        => 'string',
					'description' => "Item type: 'custom' (URL link), 'post_type' (page/post/product), or 'taxonomy' (category/tag)",
					'default'     => 'custom',
				),
				'url'       => array(
					'type'        => 'string',
					'description' => 'URL for custom link items',
				),
				'object'    => array(
					'type'        => 'string',
					'description' => 'Object type for post_type/taxonomy items (e.g., page, product, category)',
				),
				'object_id' => array(
					'type'        => 'number',
					'description' => 'Object ID for post_type/taxonomy items',
				),
				'parent_id' => array(
					'type'        => 'number',
					'description' => 'Parent menu item ID to create a sub-menu item',
					'default'     => 0,
				),
				'position'  => array(
					'type'        => 'number',
					'description' => 'Menu order position',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_update_menu_item',
			'Update an existing menu item: rename its title, change URL, move to different parent, or reposition',
			array(
				'menu_id'   => array(
					'type'        => 'number',
					'description' => 'Menu ID',
					'required'    => true,
				),
				'item_id'   => array(
					'type'        => 'number',
					'description' => 'Menu item ID to update',
					'required'    => true,
				),
				'title'     => array(
					'type'        => 'string',
					'description' => 'New menu item label',
				),
				'url'       => array(
					'type'        => 'string',
					'description' => 'New URL (for custom link items)',
				),
				'parent_id' => array(
					'type'        => 'number',
					'description' => 'New parent menu item ID (0 for top level)',
				),
				'position'  => array(
					'type'        => 'number',
					'description' => 'New menu order position',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_delete_menu_item',
			'Remove a single item from a menu',
			array(
				'menu_id' => array(
					'type'        => 'number',
					'description' => 'Menu ID',
					'required'    => true,
				),
				'item_id' => array(
					'type'        => 'number',
					'description' => 'Menu item ID to delete',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_reorder_menu_items',
			'Bulk reorder and reparent menu items in a single call',
			array(
				'menu_id' => array(
					'type'        => 'number',
					'description' => 'Menu ID',
					'required'    => true,
				),
				'items'   => array(
					'type'        => 'array',
					'description' => 'Array of {id, position, parent_id} objects',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_delete_menu',
			'Delete an entire navigation menu and all its items',
			array(
				'menu_id' => array(
					'type'        => 'number',
					'description' => 'Menu ID to delete',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_assign_menu_location',
			'Assign a menu to a theme menu location without modifying menu items',
			array(
				'menu_id'  => array(
					'type'        => 'number',
					'description' => 'Menu ID to assign',
					'required'    => true,
				),
				'location' => array(
					'type'        => 'string',
					'description' => 'Theme menu location key (e.g., primary, footer)',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_update_page_template',
			'Change a page template (e.g., default, elementor_header_footer, elementor_canvas)',
			array(
				'id'       => array(
					'type'        => 'number',
					'description' => 'Page ID',
					'required'    => true,
				),
				'template' => array(
					'type'        => 'string',
					'description' => 'Template slug (e.g., default, elementor_header_footer, elementor_canvas)',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_list_page_templates',
			'List all available page templates for the active theme',
			array()
		);

		$tools[] = $this->define_tool(
			'wp_bulk_find_replace',
			'Search and replace text in Elementor data for a given post or page',
			array(
				'id'      => array(
					'type'        => 'number',
					'description' => 'Post or page ID',
					'required'    => true,
				),
				'search'  => array(
					'type'        => 'string',
					'description' => 'Text to search for in Elementor JSON data',
					'required'    => true,
				),
				'replace' => array(
					'type'        => 'string',
					'description' => 'Replacement text',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_list_media',
			'List media library items with URLs, titles, and MIME types. Supports pagination and filtering.',
			array(
				'per_page'  => array(
					'type'        => 'number',
					'description' => 'Items per page (1-100)',
					'default'     => 20,
				),
				'page'      => array(
					'type'        => 'number',
					'description' => 'Page number',
					'default'     => 1,
				),
				'mime_type' => array(
					'type'        => 'string',
					'description' => "Filter by MIME type (e.g., 'image', 'image/png', 'video')",
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_list_content',
			'List content for any post type (e.g., WooCommerce products) with search and pagination',
			array(
				'post_type' => array(
					'type'        => 'string',
					'description' => 'Post type to list (e.g., product, lp_course)',
					'required'    => true,
				),
				'status' => array(
					'type'        => 'string',
					'description' => 'Status filter (publish, draft, any, etc.)',
					'default'     => 'any',
				),
				'search' => array(
					'type'        => 'string',
					'description' => 'Search term',
				),
				'per_page' => array(
					'type'        => 'number',
					'description' => 'Items per page (1-100)',
					'default'     => 10,
				),
				'page' => array(
					'type'        => 'number',
					'description' => 'Page number',
					'default'     => 1,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_delete_content',
			'Delete a single content item by post type and ID (supports CPT like product)',
			array(
				'post_type' => array(
					'type'        => 'string',
					'description' => 'Post type (e.g., product)',
					'required'    => true,
				),
				'id' => array(
					'type'        => 'number',
					'description' => 'Post ID',
					'required'    => true,
				),
				'force' => array(
					'type'        => 'boolean',
					'description' => 'Force permanent deletion',
					'default'     => false,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_search',
			'Search posts and pages by query string with pagination and status filters',
			array(
				'query' => array(
					'type'        => 'string',
					'description' => 'Search query',
					'required'    => true,
				),
				'type'  => array(
					'type'        => 'string',
					'description' => 'Content type filter (post, page, any)',
					'default'     => 'any',
				),
				'status' => array(
					'type'        => 'string',
					'description' => 'Status filter (publish, draft, pending, private, any)',
					'default'     => 'publish',
				),
				'per_page' => array(
					'type'        => 'number',
					'description' => 'Results per page (1-50)',
					'default'     => 10,
				),
				'page' => array(
					'type'        => 'number',
					'description' => 'Page number',
					'default'     => 1,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_fetch',
			'Fetch a single post or page by ID or URL',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Post or page ID (use id or url)',
				),
				'url' => array(
					'type'        => 'string',
					'description' => 'Canonical URL (use id or url)',
				),
				'type' => array(
					'type'        => 'string',
					'description' => 'Expected type filter (post, page, any)',
					'default'     => 'any',
				),
				'include_content' => array(
					'type'        => 'boolean',
					'description' => 'Include full content in response',
					'default'     => true,
				),
			)
		);

		// Posts
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

		// Pages
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
			'wp_delete_page',
			'Delete a page (moves to trash by default, use force for permanent deletion)',
			array(
				'id'    => array(
					'type'        => 'number',
					'description' => 'Page ID',
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
			'wp_clone_page',
			'Duplicate a page including its content, Elementor data, template, and featured image',
			array(
				'id'     => array(
					'type'        => 'number',
					'description' => 'Page ID to clone',
					'required'    => true,
				),
				'title'  => array(
					'type'        => 'string',
					'description' => 'Title for the cloned page (defaults to original with Copy suffix)',
				),
				'status' => array(
					'type'        => 'string',
					'description' => 'Status for cloned page (publish, draft, pending, private)',
					'default'     => 'draft',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_get_page_by_slug',
			'Fetch a page by its URL slug (e.g., "about", "contact")',
			array(
				'slug' => array(
					'type'        => 'string',
					'description' => 'Page URL slug',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_set_featured_image',
			'Set or remove the featured image (thumbnail) for a post or page',
			array(
				'id'       => array(
					'type'        => 'number',
					'description' => 'Post or page ID',
					'required'    => true,
				),
				'media_id' => array(
					'type'        => 'number',
					'description' => 'Media attachment ID. Use 0 to remove featured image.',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_list_categories',
			'List post categories with IDs, names, slugs, and post counts',
			array(
				'per_page' => array(
					'type'        => 'number',
					'description' => 'Results per page (1-200)',
					'default'     => 100,
				),
				'search'   => array(
					'type'        => 'string',
					'description' => 'Search term',
				),
				'parent'   => array(
					'type'        => 'number',
					'description' => 'Parent category ID to list children',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_list_tags',
			'List post tags with IDs, names, slugs, and post counts',
			array(
				'per_page' => array(
					'type'        => 'number',
					'description' => 'Results per page (1-200)',
					'default'     => 100,
				),
				'search'   => array(
					'type'        => 'string',
					'description' => 'Search term',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_batch_update',
			'Execute multiple REST API operations in a single request (max 25). Each operation specifies method, path, and body.',
			array(
				'operations' => array(
					'type'        => 'array',
					'description' => 'Array of {method, path, body} objects. method: GET/POST/PUT/DELETE. path: relative to /site-pilot-ai/v1/',
					'required'    => true,
				),
			)
		);

		// Media
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
			'wp_upload_media_b64',
			'Upload a media file from Base64 encoded data. Safer than multipart uploads on shared hosting (bypasses ModSecurity).',
			array(
				'data' => array(
					'type'        => 'string',
					'description' => 'Base64-encoded file content (optionally with data URI prefix)',
					'required'    => true,
				),
				'filename' => array(
					'type'        => 'string',
					'description' => 'Filename with extension (e.g., logo.png)',
					'required'    => true,
				),
				'title' => array(
					'type'        => 'string',
					'description' => 'Media title',
				),
				'alt' => array(
					'type'        => 'string',
					'description' => 'Alt text',
				),
			)
		);

		// Drafts
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

		// Elementor Basic
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
			'wp_regenerate_elementor_css',
			'Regenerate Elementor CSS for a specific page or the entire site. Use after updating Elementor data via API to ensure styles are applied.',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Page ID to regenerate CSS for. Omit to regenerate all site CSS.',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_screenshot_url',
			'Take a screenshot of a URL. Uses Cloudflare Browser Rendering (headless Chromium) if configured, otherwise falls back to WordPress mshots. Returns base64 PNG from Cloudflare or a URL from mshots. Optionally saves to media library.',
			array(
				'url' => array(
					'type'        => 'string',
					'description' => 'URL to screenshot',
					'required'    => true,
				),
				'width' => array(
					'type'        => 'number',
					'description' => 'Screenshot width (320-1920)',
					'default'     => 1280,
				),
				'height' => array(
					'type'        => 'number',
					'description' => 'Screenshot height (240-1440)',
					'default'     => 960,
				),
				'save_to_media' => array(
					'type'        => 'boolean',
					'description' => 'Also save screenshot to WordPress media library',
					'default'     => false,
				),
			)
		);

		// API Keys
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

		// Rate Limiting
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

		// Webhooks
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

		return $tools;
	}

	/**
	 * Get tool map for free tier.
	 *
	 * @return array Tool mappings.
	 */
	public function get_tool_map() {
		return array(
			// Site & Analytics
			'wp_site_info'      => array(
				'method' => 'GET',
				'route'  => '/site-info',
			),
			'wp_introspect'     => array(
				'method' => 'GET',
				'route'  => '/introspect',
			),
			'wp_analytics'      => array(
				'method' => 'GET',
				'route'  => '/analytics',
			),
			'wp_detect_plugins' => array(
				'method' => 'GET',
				'route'  => '/plugins',
			),
			'wp_get_options'    => array(
				'method' => 'GET',
				'route'  => '/options',
			),
			'wp_update_options' => array(
				'method' => 'POST',
				'route'  => '/options',
			),
			'wp_get_custom_css' => array(
				'method' => 'GET',
				'route'  => '/custom-css',
			),
			'wp_set_custom_css' => array(
				'method' => 'POST',
				'route'  => '/custom-css',
			),
			'wp_list_menus'          => array(
				'method' => 'GET',
				'route'  => '/menus',
			),
			'wp_list_menu_locations' => array(
				'method' => 'GET',
				'route'  => '/menus/locations',
			),
			'wp_setup_menu'          => array(
				'method' => 'POST',
				'route'  => '/menus/setup',
			),
			'wp_list_menu_items'     => array(
				'method' => 'GET',
				'route'  => '/menus/{menu_id}/items',
			),
			'wp_add_menu_item'       => array(
				'method' => 'POST',
				'route'  => '/menus/{menu_id}/items',
			),
			'wp_update_menu_item'    => array(
				'method' => 'POST',
				'route'  => '/menus/{menu_id}/items/{item_id}',
			),
			'wp_delete_menu_item'    => array(
				'method' => 'DELETE',
				'route'  => '/menus/{menu_id}/items/{item_id}',
			),
			'wp_reorder_menu_items'  => array(
				'method' => 'POST',
				'route'  => '/menus/{menu_id}/items/reorder',
			),
			'wp_delete_menu'         => array(
				'method' => 'DELETE',
				'route'  => '/menus/{menu_id}',
			),
			'wp_assign_menu_location' => array(
				'method' => 'POST',
				'route'  => '/menus/assign-location',
			),
			'wp_update_page_template' => array(
				'method' => 'POST',
				'route'  => '/pages/{id}/template',
			),
			'wp_list_page_templates'  => array(
				'method' => 'GET',
				'route'  => '/templates/page',
			),
			'wp_bulk_find_replace'   => array(
				'method' => 'POST',
				'route'  => '/elementor/{id}/find-replace',
			),
			'wp_list_media'          => array(
				'method' => 'GET',
				'route'  => '/media',
			),
			'wp_list_content'   => array(
				'method' => 'GET',
				'route'  => '/content',
			),
			'wp_delete_content' => array(
				'method' => 'DELETE',
				'route'  => '/content/{post_type}/{id}',
			),
			'wp_search'         => array(
				'method' => 'GET',
				'route'  => '/search',
			),
			'wp_fetch'          => array(
				'method' => 'GET',
				'route'  => '/fetch',
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
			'wp_delete_page'    => array(
				'method' => 'DELETE',
				'route'  => '/pages/{id}',
			),
			'wp_clone_page'     => array(
				'method' => 'POST',
				'route'  => '/pages/{id}/clone',
			),
			'wp_get_page_by_slug' => array(
				'method' => 'GET',
				'route'  => '/pages/by-slug/{slug}',
			),
			'wp_set_featured_image' => array(
				'method' => 'POST',
				'route'  => '/posts/{id}/featured-image',
			),
			'wp_list_categories' => array(
				'method' => 'GET',
				'route'  => '/categories',
			),
			'wp_list_tags'       => array(
				'method' => 'GET',
				'route'  => '/tags',
			),
			'wp_batch_update'    => array(
				'method' => 'POST',
				'route'  => '/batch',
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
			'wp_upload_media_b64'      => array(
				'method' => 'POST',
				'route'  => '/media/from-base64',
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
			'wp_regenerate_elementor_css'  => array(
				'method' => 'POST',
				'route'  => '/elementor/regenerate-css',
			),
			'wp_screenshot_url'            => array(
				'method' => 'POST',
				'route'  => '/screenshot',
			),

			// API Keys
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

			// Rate Limiting
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

			// Webhooks
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
	}
}
