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
	 * Get tool category mappings for free tier.
	 *
	 * @return array Map of tool_name => category_slug.
	 */
	public function get_tool_categories() {
		return array(
			// Site & Analytics
			'wp_site_info'               => 'site',
			'wp_introspect'              => 'site',
			'wp_analytics'               => 'site',
			'wp_detect_plugins'          => 'site',
			'wp_get_options'             => 'site',
			'wp_update_options'          => 'site',
			'wp_get_site_context'        => 'site',
			'wp_set_site_context'        => 'site',
			'wp_get_custom_css'          => 'site',
			'wp_set_custom_css'          => 'site',
			'wp_list_menus'              => 'site',
			'wp_list_menu_locations'     => 'site',
			'wp_setup_menu'              => 'site',
			'wp_list_menu_items'         => 'site',
			'wp_add_menu_item'           => 'site',
			'wp_update_menu_item'        => 'site',
			'wp_delete_menu_item'        => 'site',
			'wp_reorder_menu_items'      => 'site',
			'wp_delete_menu'             => 'site',
			'wp_assign_menu_location'    => 'site',
			'wp_update_page_template'    => 'site',
			'wp_list_page_templates'     => 'site',
			'wp_get_option'              => 'site',
			'wp_update_option'           => 'site',
			'wp_get_theme_info'          => 'site',
			'wp_flush_permalinks'        => 'site',
			'wp_get_site_health'         => 'site',

			// Content
			'wp_list_content'            => 'content',
			'wp_delete_content'          => 'content',
			'wp_search'                  => 'content',
			'wp_fetch'                   => 'content',
			'wp_list_posts'              => 'content',
			'wp_create_post'             => 'content',
			'wp_update_post'             => 'content',
			'wp_delete_post'             => 'content',
			'wp_list_pages'              => 'content',
			'wp_create_page'             => 'content',
			'wp_update_page'             => 'content',
			'wp_delete_page'             => 'content',
			'wp_clone_page'              => 'content',
			'wp_get_page_by_slug'        => 'content',
			'wp_set_featured_image'      => 'content',
			'wp_list_drafts'             => 'content',
			'wp_delete_all_drafts'       => 'content',
			'wp_batch_update'            => 'content',
			'wp_bulk_create_pages'       => 'content',
			'wp_bulk_create_posts'       => 'content',
			'wp_get_post_meta'           => 'content',
			'wp_set_post_meta'           => 'content',

			// Media
			'wp_list_media'              => 'media',
			'wp_upload_media'            => 'media',
			'wp_upload_media_from_url'   => 'media',
			'wp_upload_media_b64'        => 'media',
			'wp_screenshot_url'          => 'media',

			// Taxonomy
			'wp_list_categories'         => 'taxonomy',
			'wp_list_tags'               => 'taxonomy',
			'wp_create_term'             => 'taxonomy',
			'wp_update_term'             => 'taxonomy',
			'wp_delete_term'             => 'taxonomy',

			// Elementor
			'wp_get_elementor'           => 'elementor',
			'wp_set_elementor'           => 'elementor',
			'wp_elementor_status'        => 'elementor',
			'wp_regenerate_elementor_css' => 'elementor',
			'wp_bulk_find_replace'       => 'elementor',

			// Gutenberg
			'wp_get_blocks'              => 'gutenberg',
			'wp_set_blocks'              => 'gutenberg',
			'wp_list_block_types'        => 'gutenberg',
			'wp_list_block_patterns'     => 'gutenberg',

			// API Keys & Rate Limiting
			'wp_list_api_keys'           => 'admin',
			'wp_create_api_key'          => 'admin',
			'wp_revoke_api_key'          => 'admin',
			'wp_rate_limit_status'       => 'admin',
			'wp_update_rate_limit'       => 'admin',
			'wp_reset_rate_limit'        => 'admin',

			// Webhooks
			'wp_list_webhook_events'     => 'webhooks',
			'wp_list_webhooks'           => 'webhooks',
			'wp_create_webhook'          => 'webhooks',
			'wp_update_webhook'          => 'webhooks',
			'wp_delete_webhook'          => 'webhooks',
			'wp_test_webhook'            => 'webhooks',
			'wp_list_webhook_logs'       => 'webhooks',

			// Feedback
			'wp_submit_feedback'         => 'admin',
			'wp_list_feedback'           => 'admin',
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
			'wp_get_blocks'               => 'gutenberg',
			'wp_set_blocks'               => 'gutenberg',
			'wp_list_block_types'         => 'gutenberg',
			'wp_list_block_patterns'      => 'gutenberg',
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

		// Site Context
		$tools[] = $this->define_tool(
			'wp_get_site_context',
			'Get the site context — a master prompt / style guide that defines design rules, header/footer structure, color palette, typography, predefined sections, and page layout guidelines. Always read this first when building or editing pages.',
			array()
		);

		$tools[] = $this->define_tool(
			'wp_set_site_context',
			'Set the site context (AI brief). This is a markdown document that tells AI assistants how to build pages for this site: design tokens, header/footer rules, reusable sections, and page structure templates. Included automatically in wp_introspect.',
			array(
				'context' => array(
					'type'        => 'string',
					'description' => 'Markdown text defining site style rules, header/footer structure, predefined sections, color palette, typography, and page layout guidelines',
					'required'    => true,
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
				'classes'   => array(
					'type'        => 'array',
					'description' => 'CSS classes for styling this menu item',
					'items'       => array( 'type' => 'string' ),
				),
				'target'    => array(
					'type'        => 'string',
					'description' => 'Link target: _blank (new tab) or _self (same tab)',
				),
				'description' => array(
					'type'        => 'string',
					'description' => 'Item description (used as tooltip or subtitle by some themes)',
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
				'classes'   => array(
					'type'        => 'array',
					'description' => 'CSS classes for styling this menu item',
					'items'       => array( 'type' => 'string' ),
				),
				'target'    => array(
					'type'        => 'string',
					'description' => 'Link target: _blank (new tab) or _self (same tab)',
				),
				'description' => array(
					'type'        => 'string',
					'description' => 'Item description (used as tooltip or subtitle by some themes)',
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
			'List posts with optional filters. Supports custom post types including wp_block (reusable blocks/synced patterns).',
			array(
				'post_type' => array(
					'type'        => 'string',
					'description' => 'Post type (default: post). Use wp_block for reusable blocks/synced patterns, or any public custom post type.',
					'default'     => 'post',
				),
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
			'Create a new post. Supports custom post types: use post_type=wp_block to create a reusable block (synced pattern).',
			array(
				'title'     => array(
					'type'        => 'string',
					'description' => 'Post title',
					'required'    => true,
				),
				'content'   => array(
					'type'        => 'string',
					'description' => 'Post content (HTML or Gutenberg block markup)',
					'default'     => '',
				),
				'status'    => array(
					'type'        => 'string',
					'description' => 'Post status (publish, draft, pending, private)',
					'default'     => 'draft',
				),
				'post_type' => array(
					'type'        => 'string',
					'description' => 'Post type (default: post). Use wp_block for reusable blocks/synced patterns.',
					'default'     => 'post',
				),
				'excerpt'   => array(
					'type'        => 'string',
					'description' => 'Post excerpt',
					'default'     => '',
				),
				'slug'      => array(
					'type'        => 'string',
					'description' => 'Post URL slug (e.g. "my-post")',
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
				'slug'    => array(
					'type'        => 'string',
					'description' => 'Post URL slug (e.g. "my-post")',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_delete_post',
			'Delete a blog post. Moves to trash by default; set force=true to permanently delete.',
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
				'slug'    => array(
					'type'        => 'string',
					'description' => 'Page URL slug (e.g. "about-us")',
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
				'slug'    => array(
					'type'        => 'string',
					'description' => 'Page URL slug (e.g. "about-us")',
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
			'Permanently delete a webhook subscription and stop all future deliveries.',
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

		// Feedback
		$tools[] = $this->define_tool(
			'wp_submit_feedback',
			'Submit feedback, a bug report, or a feature request to the site owner. Optionally creates a GitHub issue if configured.',
			array(
				'type'        => array(
					'type'        => 'string',
					'description' => 'Feedback type: bug_report, feature_request, or feedback',
					'required'    => true,
					'enum'        => array( 'bug_report', 'feature_request', 'feedback' ),
				),
				'title'       => array(
					'type'        => 'string',
					'description' => 'Short summary',
					'required'    => true,
				),
				'description' => array(
					'type'        => 'string',
					'description' => 'Detailed description',
					'required'    => true,
				),
				'priority'    => array(
					'type'        => 'string',
					'description' => 'Priority: low, medium, high, critical',
					'enum'        => array( 'low', 'medium', 'high', 'critical' ),
					'default'     => 'medium',
				),
				'meta'        => array(
					'type'        => 'object',
					'description' => 'Extra context (page_id, tool_name, error_message, steps_to_reproduce)',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_list_feedback',
			'List submitted feedback entries with optional filters for type and status',
			array(
				'type'   => array(
					'type'        => 'string',
					'description' => 'Filter by type: bug_report, feature_request, feedback',
					'enum'        => array( 'bug_report', 'feature_request', 'feedback' ),
				),
				'status' => array(
					'type'        => 'string',
					'description' => 'Filter by status: open, acknowledged, resolved, closed, all',
					'default'     => 'open',
				),
				'limit'  => array(
					'type'        => 'number',
					'description' => 'Max results (1-100)',
					'default'     => 20,
				),
			)
		);

		// Post Meta
		$tools[] = $this->define_tool(
			'wp_get_post_meta',
			'Get post meta for a post or page. Returns a single key or all non-sensitive meta.',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Post or page ID',
					'required'    => true,
				),
				'key' => array(
					'type'        => 'string',
					'description' => 'Specific meta key to retrieve (omit to get all)',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_set_post_meta',
			'Set a single post meta value. Blocked keys (passwords, secrets, internal WP keys) are rejected.',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Post or page ID',
					'required'    => true,
				),
				'key' => array(
					'type'        => 'string',
					'description' => 'Meta key to set',
					'required'    => true,
				),
				'value' => array(
					'type'        => 'string',
					'description' => 'Meta value to set',
					'required'    => true,
				),
			)
		);

		// Gutenberg Blocks
		$tools[] = $this->define_tool(
			'wp_get_blocks',
			'Get parsed Gutenberg blocks for a post or page. Returns structured block data (blockName, attrs, innerBlocks, innerHTML) and the raw content.',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Post or page ID',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_set_blocks',
			'Set Gutenberg blocks for a post or page. Provide either a blocks array (serialized automatically) or raw block content string. Blocks use WordPress block grammar (<!-- wp:blockname {...} --> content <!-- /wp:blockname -->).',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Post or page ID',
					'required'    => true,
				),
				'blocks' => array(
					'type'        => 'array',
					'description' => 'Array of block objects with blockName, attrs, innerBlocks, and innerContent',
				),
				'content' => array(
					'type'        => 'string',
					'description' => 'Raw block content string (alternative to blocks array)',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_list_block_types',
			'List all registered Gutenberg block types with name, title, category, description, and supported features. Use this to discover available blocks before building pages.',
			array()
		);

		$tools[] = $this->define_tool(
			'wp_list_block_patterns',
			'List all registered block patterns with name, title, categories, and content. Patterns are pre-built block layouts that can be inserted into pages.',
			array()
		);

		// Option Management
		$tools[] = $this->define_tool(
			'wp_get_option',
			'Get a single WordPress option by key. Only whitelisted safe keys are accessible (blogname, blogdescription, show_on_front, page_on_front, etc.).',
			array(
				'key' => array(
					'type'        => 'string',
					'description' => 'Option key (e.g., blogname, show_on_front, page_on_front)',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_update_option',
			'Update a single WordPress option by key. Only whitelisted safe keys are allowed (blogname, blogdescription, show_on_front, page_on_front, timezone_string, etc.).',
			array(
				'key' => array(
					'type'        => 'string',
					'description' => 'Option key to update',
					'required'    => true,
				),
				'value' => array(
					'type'        => 'string',
					'description' => 'New value for the option',
					'required'    => true,
				),
			)
		);

		// Bulk Create Pages
		$tools[] = $this->define_tool(
			'wp_bulk_create_pages',
			'Create multiple pages in one call. Returns array of created pages with IDs and slugs.',
			array(
				'pages' => array(
					'type'        => 'array',
					'description' => 'Array of page objects with: title (required), content, status (default: draft), slug, parent, template',
					'required'    => true,
				),
			)
		);

		// Bulk Create Posts
		$tools[] = $this->define_tool(
			'wp_bulk_create_posts',
			'Create multiple blog posts in one call. Returns array of created posts with IDs and slugs.',
			array(
				'posts' => array(
					'type'        => 'array',
					'description' => 'Array of post objects with: title (required), content, status (default: draft), categories (array of IDs), tags (array of strings), excerpt, slug, post_type',
					'required'    => true,
				),
			)
		);

		// Taxonomy Management
		$tools[] = $this->define_tool(
			'wp_create_term',
			'Create a new taxonomy term (category, tag, or custom taxonomy)',
			array(
				'taxonomy' => array(
					'type'        => 'string',
					'description' => 'Taxonomy name (category, post_tag, or custom)',
					'required'    => true,
				),
				'name'     => array(
					'type'        => 'string',
					'description' => 'Term name',
					'required'    => true,
				),
				'slug'     => array(
					'type'        => 'string',
					'description' => 'Term URL slug',
				),
				'description' => array(
					'type'        => 'string',
					'description' => 'Term description',
				),
				'parent'   => array(
					'type'        => 'number',
					'description' => 'Parent term ID (for hierarchical taxonomies)',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_update_term',
			'Update an existing taxonomy term (rename, change slug, update description)',
			array(
				'id'       => array(
					'type'        => 'number',
					'description' => 'Term ID',
					'required'    => true,
				),
				'taxonomy' => array(
					'type'        => 'string',
					'description' => 'Taxonomy name (category, post_tag, or custom)',
					'required'    => true,
				),
				'name'     => array(
					'type'        => 'string',
					'description' => 'New term name',
				),
				'slug'     => array(
					'type'        => 'string',
					'description' => 'New term slug',
				),
				'description' => array(
					'type'        => 'string',
					'description' => 'New term description',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_delete_term',
			'Delete a taxonomy term',
			array(
				'id'       => array(
					'type'        => 'number',
					'description' => 'Term ID',
					'required'    => true,
				),
				'taxonomy' => array(
					'type'        => 'string',
					'description' => 'Taxonomy name (category, post_tag, or custom)',
					'required'    => true,
				),
			)
		);

		// Theme Info
		$tools[] = $this->define_tool(
			'wp_get_theme_info',
			'Get detailed theme information: name, version, parent theme, block vs classic, Elementor compatibility, and template locations',
			array()
		);

		// Flush Permalinks
		$tools[] = $this->define_tool(
			'wp_flush_permalinks',
			'Flush WordPress rewrite rules (equivalent to visiting Settings > Permalinks). Use after creating pages or changing slugs.',
			array()
		);

		// Site Health
		$tools[] = $this->define_tool(
			'wp_get_site_health',
			'Get a site health snapshot: content counts by status, pages missing SEO metadata, orphan pages not in menus, missing featured images, and active plugins',
			array()
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
			'wp_get_site_context' => array(
				'method' => 'GET',
				'route'  => '/site-context',
			),
			'wp_set_site_context' => array(
				'method' => 'POST',
				'route'  => '/site-context',
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

			// Gutenberg Blocks
			'wp_get_blocks'          => array(
				'method' => 'GET',
				'route'  => '/blocks/{id}',
			),
			'wp_set_blocks'          => array(
				'method' => 'POST',
				'route'  => '/blocks/{id}',
			),
			'wp_list_block_types'    => array(
				'method' => 'GET',
				'route'  => '/block-types',
			),
			'wp_list_block_patterns' => array(
				'method' => 'GET',
				'route'  => '/block-patterns',
			),

			// Post Meta
			'wp_get_post_meta'   => array(
				'method' => 'GET',
				'route'  => '/post-meta/{id}',
			),
			'wp_set_post_meta'   => array(
				'method' => 'POST',
				'route'  => '/post-meta/{id}',
			),

			// Option Management
			'wp_get_option'      => array(
				'method' => 'GET',
				'route'  => '/option',
			),
			'wp_update_option'   => array(
				'method' => 'POST',
				'route'  => '/option',
			),

			// Feedback
			'wp_submit_feedback'     => array(
				'method' => 'POST',
				'route'  => '/feedback',
			),
			'wp_list_feedback'       => array(
				'method' => 'GET',
				'route'  => '/feedback',
			),

			// Bulk Pages
			'wp_bulk_create_pages'   => array(
				'method' => 'POST',
				'route'  => '/pages/bulk',
			),

			// Bulk Posts
			'wp_bulk_create_posts'   => array(
				'method' => 'POST',
				'route'  => '/posts/bulk',
			),

			// Taxonomy Management
			'wp_create_term'         => array(
				'method' => 'POST',
				'route'  => '/terms',
			),
			'wp_update_term'         => array(
				'method' => 'POST',
				'route'  => '/terms/{id}',
			),
			'wp_delete_term'         => array(
				'method' => 'DELETE',
				'route'  => '/terms/{id}',
			),

			// Theme & Site Utilities
			'wp_get_theme_info'      => array(
				'method' => 'GET',
				'route'  => '/theme-info',
			),
			'wp_flush_permalinks'    => array(
				'method' => 'POST',
				'route'  => '/flush-permalinks',
			),
			'wp_get_site_health'     => array(
				'method' => 'GET',
				'route'  => '/site-health',
			),
		);
	}
}
