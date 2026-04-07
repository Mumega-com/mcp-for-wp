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
			'wp_delete_media',
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
			'wp_upload_design_reference',
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
			'wp_onboard'                 => 'site',
			'wp_analytics'               => 'site',
			'wp_detect_plugins'          => 'site',
			'wp_get_options'             => 'site',
			'wp_update_options'          => 'site',
			'wp_get_site_context'        => 'site',
			'wp_set_site_context'        => 'site',
			'wp_get_custom_css'          => 'site',
			'wp_set_custom_css'          => 'site',
			'wp_delete_custom_css'       => 'site',
			'wp_get_css_length'          => 'site',
			'wp_get_rendered_html'       => 'site',
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
			'wp_list_design_references'  => 'site',
			'wp_get_design_reference'    => 'site',
			'wp_upload_design_reference' => 'site',
			'wp_update_design_reference' => 'site',

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
			'wp_bulk_update_posts'       => 'content',
			'wp_bulk_update_pages'       => 'content',
			'wp_get_post_meta'           => 'content',
			'wp_set_post_meta'           => 'content',

			// Media
			'wp_list_media'              => 'media',
			'wp_upload_media'            => 'media',
			'wp_upload_media_from_url'   => 'media',
			'wp_upload_media_b64'        => 'media',
			'wp_upload_media_b64'        => 'media',
			'wp_delete_media'            => 'media',
			'wp_screenshot_url'          => 'media',

			// Taxonomy
			'wp_list_categories'         => 'taxonomy',
			'wp_list_tags'               => 'taxonomy',
			'wp_create_term'             => 'taxonomy',
			'wp_update_term'             => 'taxonomy',
			'wp_delete_term'             => 'taxonomy',

			// Elementor — core read/write/edit
			'wp_get_elementor'           => 'elementor',
			'wp_get_elementor_bulk'      => 'elementor',
			'wp_get_elementor_summary'   => 'elementor',
			'wp_edit_section'            => 'elementor',
			'wp_add_section'             => 'elementor',
			'wp_remove_section'          => 'elementor',
			'wp_replace_section'         => 'elementor',
			'wp_patch_elementor'         => 'elementor',
			'wp_edit_widget'             => 'elementor',
			'wp_set_elementor'           => 'elementor',
			'wp_preview_elementor'       => 'elementor',
			'wp_bulk_find_replace'       => 'elementor',

			// Elementor Info — reference/status
			'wp_elementor_status'        => 'elementor-info',
			'wp_regenerate_elementor_css' => 'elementor-info',
			'wp_get_elementor_widgets'   => 'elementor-info',
			'wp_get_widget_schema'       => 'elementor-info',
			'wp_elementor_widget_help'   => 'elementor-info',

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

			// Plugin Settings
			'wp_get_plugin_settings'     => 'admin',
			'wp_update_plugin_settings'  => 'admin',

			// Plugin Updates
			'wp_check_update'            => 'admin',
			'wp_trigger_update'          => 'admin',

			// Integrations
			'wp_integrations_status'     => 'admin',
			'wp_configure_integration'   => 'admin',
			'wp_test_integration'        => 'admin',
			'wp_remove_integration'      => 'admin',

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

			// Guides & Workflows
			'wp_get_guide'               => 'site',
			'wp_get_workflow'            => 'site',
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
			'wp_get_elementor_bulk'       => 'elementor',
			'wp_get_elementor_summary'    => 'elementor',
			'wp_edit_section'             => 'elementor',
			'wp_add_section'              => 'elementor',
			'wp_remove_section'           => 'elementor',
			'wp_replace_section'          => 'elementor',
			'wp_patch_elementor'          => 'elementor',
			'wp_edit_widget'              => 'elementor',
			'wp_set_elementor'            => 'elementor',
			'wp_elementor_status'         => 'elementor',
			'wp_regenerate_elementor_css' => 'elementor',
			'wp_bulk_find_replace'        => 'elementor',
			'wp_get_elementor_widgets'    => 'elementor',
			'wp_get_widget_schema'        => 'elementor',
			'wp_preview_elementor'        => 'elementor',
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
			'Get WordPress site information including name, URL, version, theme, active plugins, content counts, and RTL/text direction detection',
			array()
		);

		$tools[] = $this->define_tool(
			'wp_introspect',
			'Get a machine-readable description of this plugin (auth, endpoints, tools, capabilities) so AI clients can self-configure instead of guessing',
			array()
		);

		$tools[] = $this->define_tool(
			'wp_onboard',
			'Get a complete first-connection briefing for this WordPress site. Returns site identity, content inventory, active integrations, available tools grouped by category, site context, recommended first actions, and a quick reference card. Call this first when connecting to a new site.',
			array()
		);

		$tools[] = $this->define_tool(
			'wp_list_design_references',
			'List stored design references that models can reuse when building pages, archetypes, and Elementor parts from uploaded screenshots or approved design images.',
			array(
				'query' => array(
					'type'        => 'string',
					'description' => 'Optional text query.',
				),
				'page_intent' => array(
					'type'        => 'string',
					'description' => 'Optional page intent such as landing_page, blog_post, product_page, or service_page.',
				),
				'archetype_class' => array(
					'type'        => 'string',
					'description' => 'Optional archetype class filter.',
				),
				'style' => array(
					'type'        => 'string',
					'description' => 'Optional style filter.',
				),
				'source_type' => array(
					'type'        => 'string',
					'description' => 'Optional source type filter such as upload, url, figma, or stitch.',
				),
				'per_page' => array(
					'type'        => 'number',
					'description' => 'Results per page.',
					'default'     => 20,
				),
				'page' => array(
					'type'        => 'number',
					'description' => 'Page number.',
					'default'     => 1,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_get_design_reference',
			'Get one stored design reference, including its image, intent, style, reuse notes, and linked archetypes or parts.',
			array(
				'id' => array(
					'type'        => 'string',
					'description' => 'Design reference ID.',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_upload_design_reference',
			'Create a reusable design reference from an uploaded image, external image URL, or existing media library image. Use this when a human shares a screenshot or design image that should guide future page creation.',
			array(
				'title' => array(
					'type'        => 'string',
					'description' => 'Reference title.',
				),
				'media_id' => array(
					'type'        => 'number',
					'description' => 'Existing image attachment ID to use instead of uploading a new file.',
				),
				'image_url' => array(
					'type'        => 'string',
					'description' => 'External image URL to import into the media library.',
				),
				'image_base64' => array(
					'type'        => 'string',
					'description' => 'Base64-encoded image data.',
				),
				'filename' => array(
					'type'        => 'string',
					'description' => 'Filename when using image_base64 or importing from URL.',
				),
				'page_intent' => array(
					'type'        => 'string',
					'description' => 'Target page type such as landing_page, blog_post, service_page, or product_page.',
				),
				'archetype_class' => array(
					'type'        => 'string',
					'description' => 'Archetype class the design best maps to.',
				),
				'style' => array(
					'type'        => 'string',
					'description' => 'Style label, for example editorial, premium, showcase, or minimal.',
				),
				'notes' => array(
					'type'        => 'string',
					'description' => 'Freeform design notes.',
				),
				'analysis_summary' => array(
					'type'        => 'string',
					'description' => 'Short summary of what matters in the design.',
				),
				'tags' => array(
					'type'        => 'array',
					'description' => 'Array of short tags.',
				),
				'must_keep' => array(
					'type'        => 'array',
					'description' => 'Array of elements that should be preserved.',
				),
				'avoid' => array(
					'type'        => 'array',
					'description' => 'Array of design choices to avoid when recreating this reference.',
				),
				'section_outline' => array(
					'type'        => 'array',
					'description' => 'Ordered list of sections inferred from the design.',
				),
				'source_type' => array(
					'type'        => 'string',
					'description' => 'Optional source type override: upload, url, base64, figma, stitch, manual, or media.',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_update_design_reference',
			'Update metadata for a stored design reference. Use this to refine intent, style, notes, section outline, or linked archetypes after review.',
			array(
				'id' => array(
					'type'        => 'string',
					'description' => 'Design reference ID.',
					'required'    => true,
				),
				'title' => array(
					'type'        => 'string',
					'description' => 'Reference title.',
				),
				'page_intent' => array(
					'type'        => 'string',
					'description' => 'Target page type.',
				),
				'archetype_class' => array(
					'type'        => 'string',
					'description' => 'Archetype class.',
				),
				'style' => array(
					'type'        => 'string',
					'description' => 'Style label.',
				),
				'notes' => array(
					'type'        => 'string',
					'description' => 'Freeform notes.',
				),
				'analysis_summary' => array(
					'type'        => 'string',
					'description' => 'Short analysis summary.',
				),
				'tags' => array(
					'type'        => 'array',
					'description' => 'Array of short tags.',
				),
				'must_keep' => array(
					'type'        => 'array',
					'description' => 'Array of design requirements to preserve.',
				),
				'avoid' => array(
					'type'        => 'array',
					'description' => 'Array of design constraints to avoid.',
				),
				'section_outline' => array(
					'type'        => 'array',
					'description' => 'Ordered section list.',
				),
				'linked_archetype_ids' => array(
					'type'        => 'array',
					'description' => 'Array of related Elementor archetype template IDs.',
				),
				'linked_part_ids' => array(
					'type'        => 'array',
					'description' => 'Array of related Elementor part template IDs.',
				),
			)
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
			'wp_delete_custom_css',
			'Delete all Additional CSS from the WordPress Customizer. Removes all custom CSS rules. Returns the previous CSS length.',
			array()
		);

		$tools[] = $this->define_tool(
			'wp_get_css_length',
			'Get the length and line count of the Additional CSS without returning the full CSS body. Lightweight check to see if custom CSS exists.',
			array()
		);

		$tools[] = $this->define_tool(
			'wp_get_rendered_html',
			'Fetch the rendered HTML of a page as the browser sees it. Useful for verifying CSS, fonts, meta tags, and actual content rendering. Supports CSS selector extraction (tag, .class, #id). Only same-host URLs allowed (SSRF-safe).',
			array(
				'id'        => array(
					'type'        => 'number',
					'description' => 'Post or page ID to fetch rendered HTML for',
				),
				'url'       => array(
					'type'        => 'string',
					'description' => 'URL to fetch (same-host only). Either id or url is required.',
				),
				'selector'  => array(
					'type'        => 'string',
					'description' => 'CSS selector to extract (e.g. "head", ".my-class", "#main")',
				),
				'max_bytes' => array(
					'type'        => 'number',
					'description' => 'Maximum response size in bytes (default 51200, max 204800)',
					'default'     => 51200,
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
			'List posts with optional filters. Supports custom post types including wp_block (reusable blocks/synced patterns). Use ids to fetch specific posts and fields to control which data is returned (e.g. fields=id,title,word_count to get word counts without full content).',
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
				'ids'      => array(
					'type'        => 'string',
					'description' => 'Comma-separated post IDs to fetch (e.g. "41,42,43")',
				),
				'fields'   => array(
					'type'        => 'string',
					'description' => 'Comma-separated field names to return (e.g. "id,title,word_count,content"). id is always included. Use "content" or "word_count" to include full content and word counts.',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_create_post',
			'Create a new post. Supports custom post types: use post_type=wp_block for reusable blocks, post_type=elementor_snippet for Elementor Custom Code (requires Elementor Pro).',
			array(
				'title'              => array(
					'type'        => 'string',
					'description' => 'Post title',
					'required'    => true,
				),
				'content'            => array(
					'type'        => 'string',
					'description' => 'Post content (HTML or Gutenberg block markup)',
					'default'     => '',
				),
				'status'             => array(
					'type'        => 'string',
					'description' => 'Post status (publish, draft, pending, private)',
					'default'     => 'draft',
				),
				'post_type'          => array(
					'type'        => 'string',
					'description' => 'Post type (default: post). Use wp_block for reusable blocks, elementor_snippet for Elementor Custom Code.',
					'default'     => 'post',
				),
				'excerpt'            => array(
					'type'        => 'string',
					'description' => 'Post excerpt',
					'default'     => '',
				),
				'slug'               => array(
					'type'        => 'string',
					'description' => 'Post URL slug (e.g. "my-post")',
				),
				'elementor_location' => array(
					'type'        => 'string',
					'description' => 'For elementor_snippet only: injection location (head, body_start, body_end). Default: head.',
					'default'     => 'head',
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
			'List pages with optional filters for status, search, and pagination. Use ids to fetch specific pages and fields to control which data is returned (e.g. fields=id,title,word_count).',
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
				'ids'      => array(
					'type'        => 'string',
					'description' => 'Comma-separated page IDs to fetch (e.g. "95,33,34")',
				),
				'fields'   => array(
					'type'        => 'string',
					'description' => 'Comma-separated field names to return (e.g. "id,title,word_count,content"). id is always included. Use "content" or "word_count" to include full content and word counts.',
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
			'Execute multiple REST API operations in a single request (max 25). Operations run sequentially and each returns {index, status, data}. Example: [{"method":"PUT","path":"/posts/42","body":{"title":"New Title"}}, {"method":"GET","path":"/pages"}]. Use this to reduce round-trips when making many changes.',
			array(
				'operations' => array(
					'type'        => 'array',
					'description' => 'Array of operation objects. Each must have: method (GET/POST/PUT/DELETE), path (relative to /site-pilot-ai/v1/, e.g. "/pages/42"), body (optional object — used as request body for POST/PUT, query params for GET/DELETE)',
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

		$tools[] = $this->define_tool(
			'wp_delete_media',
			'Delete a media attachment from the WordPress media library',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Attachment ID',
					'required'    => true,
				),
				'force' => array(
					'type'        => 'boolean',
					'description' => 'Permanently delete instead of trashing (default: false)',
					'default'     => false,
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
				'strip_defaults' => array(
					'type'        => 'boolean',
					'description' => 'Strip default widget settings to reduce payload size by 70-80%',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_get_elementor_bulk',
			'Get Elementor data for multiple pages in a single request (max 25). Returns results keyed by page ID with any errors listed separately. Useful for site audits and bulk operations.',
			array(
				'ids' => array(
					'type'        => 'string',
					'description' => 'Comma-separated page IDs (max 25), e.g. "10,20,30"',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_get_elementor_summary',
			'ALWAYS call this first before editing Elementor pages. Returns a lightweight structural summary with element IDs (needed for add/remove/replace/edit), section types, widget types, and key settings. Every element includes its ID and top-level sections include their index. Use this instead of wp_get_elementor when you only need to understand the page structure.',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Page or post ID',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_edit_section',
			'Surgically edit a single Elementor element without downloading/uploading the full page JSON. Find the target by element_id, section_index (0-based), or search criteria (find). Merges settings into the element and returns only the modified element. Much more token-efficient than get+set for small edits.',
			array(
				'id'              => array(
					'type'        => 'number',
					'description' => 'Page or post ID',
					'required'    => true,
				),
				'element_id'      => array(
					'type'        => 'string',
					'description' => 'Elementor element ID to edit (from summary or previous get)',
				),
				'section_index'   => array(
					'type'        => 'number',
					'description' => 'Top-level section index (0-based)',
				),
				'find'            => array(
					'type'        => 'object',
					'description' => 'Search criteria to find element: {widgetType: "heading", "settings.title": "Old Text"}',
				),
				'settings'        => array(
					'type'        => 'object',
					'description' => 'Settings to merge into the found element',
				),
				'delete_settings' => array(
					'type'        => 'array',
					'description' => 'Setting keys to remove from the element',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_edit_widget',
			'Edit a single Elementor widget\'s settings by its element ID, without requiring the full page JSON round-trip. Much more efficient than get+set for updating text, colors, or other widget properties. The widget must be a widget element (not section/container).',
			array(
				'id'              => array(
					'type'        => 'number',
					'description' => 'Page or post ID',
					'required'    => true,
				),
				'widget_id'       => array(
					'type'        => 'string',
					'description' => 'Elementor widget element ID (8-char alphanumeric, from summary or previous get)',
					'required'    => true,
				),
				'settings'        => array(
					'type'        => 'object',
					'description' => 'Settings to merge into the widget (e.g. {"title": "New Heading", "align": "center"})',
				),
				'delete_settings' => array(
					'type'        => 'array',
					'description' => 'Setting keys to remove from the widget',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_add_section',
			'Add a new section/container to an Elementor page at a specific position. No need to send the full page — just the new section. WORKFLOW: 1) wp_get_elementor_summary → get IDs for positioning 2) Build JSON manually or via wp_get_blueprint 3) wp_add_section(page_id, element, position) 4) Verify response has meta_verified=true.',
			array(
				'page_id'  => array(
					'type'        => 'integer',
					'description' => 'Page ID',
					'required'    => true,
				),
				'element'  => array(
					'type'        => 'object',
					'description' => 'The section/container element object with elType, elements[], and settings',
					'required'    => true,
				),
				'position' => array(
					'type'        => 'string',
					'description' => 'Where to insert: "start", "end" (default), "before:{element_id}", "after:{element_id}"',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_remove_section',
			'Remove a section/container from an Elementor page by its element ID. Searches both top-level and nested elements.',
			array(
				'page_id'    => array(
					'type'        => 'integer',
					'description' => 'Page ID',
					'required'    => true,
				),
				'element_id' => array(
					'type'        => 'string',
					'description' => 'The 8-char Elementor element ID to remove',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_replace_section',
			'Replace an entire section/container in an Elementor page by its element ID. The new element takes the position of the old one. Get the target element ID from wp_get_elementor_summary first.',
			array(
				'page_id'    => array(
					'type'        => 'integer',
					'description' => 'Page ID',
					'required'    => true,
				),
				'element_id' => array(
					'type'        => 'string',
					'description' => 'The 8-char Elementor element ID to replace',
					'required'    => true,
				),
				'element'    => array(
					'type'        => 'object',
					'description' => 'The replacement section/container element',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_patch_elementor',
			'Apply multiple operations to an Elementor page in a single read-write cycle (max 20 ops). More efficient than calling add/remove/replace individually. Operations: add, remove, replace, settings. Get element IDs from wp_get_elementor_summary first.',
			array(
				'page_id'    => array(
					'type'        => 'integer',
					'description' => 'Page ID',
					'required'    => true,
				),
				'operations' => array(
					'type'        => 'array',
					'description' => 'Array of operations. Each: {op: "add"|"remove"|"replace"|"settings", element_id: "...", element: {...}, position: "...", settings: {...}, delete_settings: [...]}',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_set_elementor',
			'FULL PAGE REPLACEMENT — overwrites all Elementor data on the page. For surgical edits, prefer wp_add_section, wp_replace_section, wp_edit_section, or wp_edit_widget instead. Use dry_run=true to validate data without saving. For large payloads with HTML content, use elementor_data_base64 (base64-encoded JSON) instead of elementor_data to avoid quoting/escaping issues.',
			array(
				'id'             => array(
					'type'        => 'number',
					'description' => 'Page or post ID',
					'required'    => true,
				),
				'elementor_data' => array(
					'type'        => 'string',
					'description' => 'Elementor JSON data. For large payloads with HTML, prefer elementor_data_base64 instead.',
				),
				'elementor_data_base64' => array(
					'type'        => 'string',
					'description' => 'Base64-encoded Elementor JSON data. Use this instead of elementor_data for large payloads containing HTML or special characters to avoid quoting/escaping corruption. Encode your JSON string with btoa() or base64_encode() before sending.',
				),
				'dry_run'        => array(
					'type'        => 'boolean',
					'description' => 'If true, validate only — no changes are saved. Returns warnings and fixes without writing to database.',
					'default'     => false,
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
			'Regenerate Elementor CSS for a specific page or the entire site. Use after updating Elementor data via API to ensure styles are applied. Returns detailed per-page results: regenerated, skipped (with reason), and failed (with error). Use force=true to delete existing CSS files before regenerating.',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Page ID to regenerate CSS for. Omit to regenerate all site CSS.',
				),
				'force' => array(
					'type'        => 'boolean',
					'description' => 'Delete existing CSS files before regenerating (default: false)',
					'default'     => false,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_get_elementor_widgets',
			'Get list of available Elementor widgets. Pass a widget name to get its full controls schema with valid control keys, types, defaults, and options.',
			array(
				'widget' => array(
					'type'        => 'string',
					'description' => 'Widget type name (e.g. "heading", "image", "nav-menu") to get full controls schema. Omit to list all widgets.',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_get_widget_schema',
			'Get the detailed controls schema for a specific Elementor widget type. Returns all valid control keys grouped by tab (content, style, advanced) with types, labels, defaults, and options. Use this to discover valid settings keys before building pages.',
			array(
				'widget_type' => array(
					'type'        => 'string',
					'description' => 'Widget type name (e.g. "heading", "image", "button")',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_elementor_widget_help',
			'Get complete help for an Elementor widget type: all valid settings keys with types and defaults, example JSON element, and common mistakes to avoid. Works offline (no Elementor installation required). Use this BEFORE building Elementor pages to ensure correct settings keys. If widget_type is not found, returns closest matches.',
			array(
				'widget_type' => array(
					'type'        => 'string',
					'description' => 'Widget type name (e.g. "heading", "image", "button", "icon-box", "price-table"). Use exact Elementor widget type names.',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_preview_elementor',
			'Get rendered HTML preview of a page\'s Elementor content. Returns HTML output, text summary, and element statistics (sections, columns, widgets, widget types, word count). Use "summary" format (default) to save tokens — returns truncated text + stats without HTML. Use "text" for full text extraction, or "html" for full rendered HTML.',
			array(
				'id'     => array(
					'type'        => 'number',
					'description' => 'Page/post ID to preview',
					'required'    => true,
				),
				'format' => array(
					'type'        => 'string',
					'description' => 'Output format: "summary" (text truncated to 500 chars + stats, no HTML — default, saves tokens), "text" (full plain text extraction), "html" (full rendered HTML + text)',
					'enum'        => array( 'summary', 'text', 'html' ),
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_screenshot_url',
			'Take a screenshot of a URL. Uses Cloudflare Browser Rendering (headless Chromium) if configured, otherwise falls back to WordPress mshots. Returns base64 PNG from Cloudflare or a URL from mshots. Optionally saves to media library. Set webhook_url for async mode — the webhook fires when the screenshot is ready (useful for mshots which renders asynchronously).',
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
				'webhook_url' => array(
					'type'        => 'string',
					'description' => 'URL to POST when screenshot is ready. Enables async mode for mshots. Payload: {url, screenshot_url, status, timestamp}. Header: X-SPAI-Event: screenshot.ready',
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
			'Create a role-based API key. Roles limit which MCP tool categories the key can access. Returns plaintext key once.',
			array(
				'label' => array(
					'type'        => 'string',
					'description' => 'Human-readable key label',
				),
				'role' => array(
					'type'        => 'string',
					'description' => 'Access role: admin (all tools), author (content/media/taxonomy), designer (elementor/gutenberg/media/site), editor (content/media/taxonomy/seo), custom (pick categories)',
					'enum'        => array( 'admin', 'author', 'designer', 'editor', 'custom' ),
				),
				'tool_categories' => array(
					'type'        => 'array',
					'description' => 'Tool categories to allow (only for custom role). Options: content, media, taxonomy, elementor, gutenberg, seo, forms, site, admin, webhooks',
				),
				'scopes' => array(
					'type'        => 'array',
					'description' => 'Key scopes (read, write, admin). Auto-set for non-admin roles.',
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

		// Plugin Settings
		$tools[] = $this->define_tool(
			'wp_get_plugin_settings',
			'Get Site Pilot AI plugin settings. Returns: activity logging config, CORS allowed origins, OAuth settings, alert thresholds, GitHub integration status. Secrets are redacted. Use wp_update_plugin_settings to change values.',
			array()
		);

		$tools[] = $this->define_tool(
			'wp_update_plugin_settings',
			'Update Site Pilot AI plugin settings. Pass only the keys you want to change. Allowed keys: enable_logging (bool), log_retention_days (1-365), log_store_response_data (bool), allowed_origins (comma-separated URLs), oauth_enabled (bool), oauth_client_id, oauth_client_secret, oauth_token_ttl (60-86400 seconds), alerts_enabled (bool), alerts_window_minutes (1-60), alerts_cooldown_minutes (1-1440), alerts_5xx_threshold (1-10000), alerts_auth_threshold (1-10000), github_token, github_repo (owner/repo).',
			array(
				'enable_logging' => array(
					'type'        => 'boolean',
					'description' => 'Enable API activity logging',
				),
				'log_retention_days' => array(
					'type'        => 'number',
					'description' => 'Days to retain activity logs (1-365)',
				),
				'log_store_response_data' => array(
					'type'        => 'boolean',
					'description' => 'Store response data in activity logs',
				),
				'allowed_origins' => array(
					'type'        => 'string',
					'description' => 'CORS allowed origins (comma-separated URLs, or empty for default)',
				),
				'oauth_enabled' => array(
					'type'        => 'boolean',
					'description' => 'Enable OAuth client credentials flow',
				),
				'oauth_client_id' => array(
					'type'        => 'string',
					'description' => 'OAuth client ID',
				),
				'oauth_client_secret' => array(
					'type'        => 'string',
					'description' => 'OAuth client secret (write-only, stored hashed)',
				),
				'oauth_token_ttl' => array(
					'type'        => 'number',
					'description' => 'OAuth token TTL in seconds (60-86400)',
				),
				'alerts_enabled' => array(
					'type'        => 'boolean',
					'description' => 'Enable error/auth failure alerts',
				),
				'alerts_window_minutes' => array(
					'type'        => 'number',
					'description' => 'Alert detection window in minutes (1-60)',
				),
				'alerts_cooldown_minutes' => array(
					'type'        => 'number',
					'description' => 'Cooldown between repeated alerts (1-1440)',
				),
				'alerts_5xx_threshold' => array(
					'type'        => 'number',
					'description' => 'Server errors in window before alert fires (1-10000)',
				),
				'alerts_auth_threshold' => array(
					'type'        => 'number',
					'description' => 'Auth failures in window before alert fires (1-10000)',
				),
				'github_token' => array(
					'type'        => 'string',
					'description' => 'GitHub personal access token (write-only)',
				),
				'github_repo' => array(
					'type'        => 'string',
					'description' => 'GitHub repository in owner/repo format',
				),
			)
		);

		// Plugin Updates
		$tools[] = $this->define_tool(
			'wp_check_update',
			'Check if a newer version of Site Pilot AI is available. Returns current version, latest version, and download URL.',
			array()
		);

		$tools[] = $this->define_tool(
			'wp_trigger_update',
			'Download and install the latest version of Site Pilot AI. The plugin will be upgraded in place. Requires administrator privileges.',
			array()
		);

		// Integration Management
		$tools[] = $this->define_tool(
			'wp_integrations_status',
			'List all available integrations and their configuration status. Shows which providers are configured, when they were set up, and their last test result. Providers include: pexels (stock photos), openai/gemini (AI image gen, vision), elevenlabs (TTS), screenshot (Cloudflare Browser Rendering for screenshots), google_indexing, and figma (design context intake).',
			array()
		);

		$tools[] = $this->define_tool(
			'wp_configure_integration',
			'Configure a third-party integration. For single-key providers (pexels, openai, gemini, elevenlabs), pass "key". For multi-field providers like "screenshot", "google_indexing", and "figma", pass "config". Example: provider="figma", config={"access_token":"your-token","default_file_key":"abc123"}',
			array(
				'provider' => array(
					'type'        => 'string',
					'description' => 'Provider slug: pexels, openai, gemini, elevenlabs, screenshot, google_indexing, figma',
					'required'    => true,
					'enum'        => array( 'pexels', 'openai', 'gemini', 'elevenlabs', 'screenshot', 'google_indexing', 'figma' ),
				),
				'key' => array(
					'type'        => 'string',
					'description' => 'API key (for single-key providers: pexels, openai, gemini, elevenlabs)',
				),
				'config' => array(
					'type'        => 'object',
					'description' => 'Configuration object for multi-field providers. For screenshot: {"url": "worker_url", "token": "auth_token"}. For figma: {"access_token": "personal_access_token", "default_file_key": "optional_file_key"}.',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_test_integration',
			'Test a configured integration connection. For screenshot, sends a test request to the worker. For figma, validates the API token and optional default file access. For API providers, validates the API key.',
			array(
				'provider' => array(
					'type'        => 'string',
					'description' => 'Provider slug to test',
					'required'    => true,
					'enum'        => array( 'pexels', 'openai', 'gemini', 'elevenlabs', 'screenshot', 'google_indexing', 'figma' ),
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_remove_integration',
			'Remove a configured integration. Deletes the stored API key or configuration for the provider.',
			array(
				'provider' => array(
					'type'        => 'string',
					'description' => 'Provider slug to remove',
					'required'    => true,
					'enum'        => array( 'pexels', 'openai', 'gemini', 'elevenlabs', 'screenshot', 'google_indexing', 'figma' ),
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
			'Get a single WordPress option by key. Supports core WP options (blogname, show_on_front, etc.) and plugin prefixes: elementor_*, wpseo_*, rank_math_*, astra_*, theme_mods_*, widget_*, woocommerce_*, spai_*. Sensitive keys (passwords, tokens, secrets) are always blocked.',
			array(
				'key' => array(
					'type'        => 'string',
					'description' => 'Option key (e.g., blogname, show_on_front, elementor_active_kit, wpseo_titles)',
					'required'    => true,
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_update_option',
			'Update a single WordPress option by key. Supports core WP options and plugin prefixes: elementor_*, wpseo_*, rank_math_*, astra_*, theme_mods_*, widget_*, woocommerce_*, spai_*. Sensitive keys (passwords, tokens, secrets) are always blocked.',
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

		// Bulk Update Posts
		$tools[] = $this->define_tool(
			'wp_bulk_update_posts',
			'Update multiple posts in one call. Each item must include id plus fields to update. Returns array of updated posts and any errors.',
			array(
				'posts' => array(
					'type'        => 'array',
					'description' => 'Array of post objects with: id (required), title, content, status, excerpt, slug, categories, tags',
					'required'    => true,
				),
			)
		);

		// Bulk Update Pages
		$tools[] = $this->define_tool(
			'wp_bulk_update_pages',
			'Update multiple pages in one call. Each item must include id plus fields to update. Returns array of updated pages and any errors.',
			array(
				'pages' => array(
					'type'        => 'array',
					'description' => 'Array of page objects with: id (required), title, content, status, slug, parent, template',
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

		// Guides & Workflows
		$tools[] = $this->define_tool(
			'wp_get_guide',
			'Get a detailed guide on a specific topic. Call with no topic to list all available topics. Topics include: onboarding, elementor, seo, menus, media, content, forms, woocommerce, workflows, troubleshooting. Only shows topics relevant to active plugins.',
			array(
				'topic' => array(
					'type'        => 'string',
					'description' => 'Guide topic slug (e.g., "onboarding", "elementor", "woocommerce", "workflows"). Omit to list all available topics.',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_get_workflow',
			'Get a step-by-step workflow template for a common task. Each workflow includes tool names, parameters, and tips. Available workflows include: build_landing_page, build_from_parts_library, build_from_page_archetype, build_product_from_archetype, seo_audit, content_migration, site_redesign, menu_setup, media_management, form_setup.',
			array(
				'name' => array(
					'type'        => 'string',
					'description' => 'Workflow name (e.g., "build_from_page_archetype", "build_product_from_archetype")',
					'required'    => true,
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
			'wp_onboard'        => array(
				'method' => 'GET',
				'route'  => '/onboard',
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
			'wp_delete_custom_css' => array(
				'method' => 'DELETE',
				'route'  => '/custom-css',
			),
			'wp_get_css_length' => array(
				'method' => 'GET',
				'route'  => '/custom-css/length',
			),
			'wp_get_rendered_html' => array(
				'method' => 'GET',
				'route'  => '/rendered-html',
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
			'wp_update_menu_item_auto' => array(
				'method' => 'POST',
				'route'  => '/menus/items/{item_id}',
			),
			'wp_delete_menu_item_auto' => array(
				'method' => 'DELETE',
				'route'  => '/menus/items/{item_id}',
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
			'wp_list_design_references' => array(
				'method' => 'GET',
				'route'  => '/design-references',
			),
			'wp_get_design_reference' => array(
				'method' => 'GET',
				'route'  => '/design-references/{id}',
			),
			'wp_upload_design_reference' => array(
				'method' => 'POST',
				'route'  => '/design-references',
			),
			'wp_update_design_reference' => array(
				'method' => 'POST',
				'route'  => '/design-references/{id}',
			),
			'wp_delete_media'          => array(
				'method' => 'DELETE',
				'route'  => '/media/{id}',
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
			'wp_get_elementor_bulk'    => array(
				'method' => 'GET',
				'route'  => '/elementor/bulk',
			),
			'wp_get_elementor_summary' => array(
				'method' => 'GET',
				'route'  => '/elementor/{id}/summary',
			),
			'wp_edit_section'          => array(
				'method' => 'POST',
				'route'  => '/elementor/{id}/edit-section',
			),
			'wp_add_section'           => array(
				'method' => 'POST',
				'route'  => '/elementor/{page_id}/add-section',
			),
			'wp_remove_section'        => array(
				'method' => 'POST',
				'route'  => '/elementor/{page_id}/remove-section',
			),
			'wp_replace_section'       => array(
				'method' => 'POST',
				'route'  => '/elementor/{page_id}/replace-section',
			),
			'wp_patch_elementor'       => array(
				'method' => 'POST',
				'route'  => '/elementor/{page_id}/patch',
			),
			'wp_edit_widget'           => array(
				'method' => 'POST',
				'route'  => '/elementor/{id}/edit-widget',
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
			'wp_get_elementor_widgets'     => array(
				'method' => 'GET',
				'route'  => '/elementor/widgets',
			),
			'wp_get_widget_schema'         => array(
				'method' => 'GET',
				'route'  => '/elementor/widgets/{widget_type}',
			),
			'wp_elementor_widget_help'     => array(
				'method' => 'GET',
				'route'  => '/elementor/widget-help/{widget_type}',
			),
			'wp_preview_elementor'         => array(
				'method' => 'GET',
				'route'  => '/elementor/{id}/preview',
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

			// Plugin Settings
			'wp_get_plugin_settings'     => array(
				'method' => 'GET',
				'route'  => '/plugin-settings',
			),
			'wp_update_plugin_settings'  => array(
				'method' => 'PUT',
				'route'  => '/plugin-settings',
			),

			// Plugin Updates
			'wp_check_update'            => array(
				'method' => 'GET',
				'route'  => '/update',
			),
			'wp_trigger_update'          => array(
				'method' => 'POST',
				'route'  => '/update',
			),

			// Integrations
			'wp_integrations_status'     => array(
				'method' => 'GET',
				'route'  => '/integrations/status',
			),
			'wp_configure_integration'   => array(
				'method' => 'POST',
				'route'  => '/integrations/configure',
			),
			'wp_test_integration'        => array(
				'method' => 'POST',
				'route'  => '/integrations/test',
			),
			'wp_remove_integration'      => array(
				'method' => 'POST',
				'route'  => '/integrations/remove',
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
			'wp_bulk_update_posts'   => array(
				'method' => 'PUT',
				'route'  => '/posts/bulk',
			),
			'wp_bulk_update_pages'   => array(
				'method' => 'PUT',
				'route'  => '/pages/bulk',
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

			// Guides & Workflows
			'wp_get_guide'           => array(
				'method' => 'GET',
				'route'  => '/guides',
			),
			'wp_get_workflow'        => array(
				'method' => 'GET',
				'route'  => '/workflows/{name}',
			),
		);
	}
}
