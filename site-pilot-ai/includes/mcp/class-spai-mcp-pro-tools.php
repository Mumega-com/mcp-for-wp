<?php
/**
 * MCP Pro Tools Registry
 *
 * Contains all pro tier MCP tool definitions and route mappings.
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pro tools registry for MCP.
 *
 * Provides tool definitions and route mappings for pro tier tools.
 */
class Spai_MCP_Pro_Tools extends Spai_MCP_Tool_Registry {

	/**
	 * Get destructive tool names for pro tier.
	 *
	 * @return array Destructive tool names.
	 */
	protected function get_destructive_tools() {
		return array(
			'wp_delete_widget',
		);
	}

	/**
	 * Get open world tool names for pro tier.
	 *
	 * @return array Open world tool names.
	 */
	protected function get_open_world_tools() {
		return array();
	}

	/**
	 * Get required capabilities for pro tools.
	 *
	 * @return array Map of tool_name => capability_key.
	 */
	public function get_required_capabilities() {
		return array(
			// SEO tools — any SEO plugin.
			'wp_get_seo'            => 'seo',
			'wp_set_seo'            => 'seo',
			'wp_analyze_seo'        => 'seo',
			'wp_bulk_seo'           => 'seo',
			'wp_seo_status'         => 'seo',
			// Forms tools — any forms plugin.
			'wp_list_forms'         => 'forms',
			'wp_get_form'           => 'forms',
			'wp_get_form_entries'   => 'forms',
			'wp_forms_status'       => 'forms',
			// Elementor Pro tools.
			'wp_list_elementor_templates'         => 'elementor',
			'wp_get_elementor_template'           => 'elementor',
			'wp_create_elementor_template'        => 'elementor',
			'wp_update_elementor_template'        => 'elementor',
			'wp_delete_elementor_template'        => 'elementor',
			'wp_apply_elementor_template'         => 'elementor',
			'wp_create_landing_page'              => 'elementor',
			'wp_clone_elementor_page'             => 'elementor',
			'wp_get_elementor_globals'            => 'elementor',
			'wp_set_elementor_globals'            => 'elementor',
			'wp_get_elementor_widgets'            => 'elementor',
			'wp_list_elementor_custom_code'       => 'elementor',
			'wp_disable_elementor_custom_code'    => 'elementor',
			'wp_enable_elementor_custom_code'     => 'elementor',
			'wp_sanitize_elementor_custom_code'   => 'elementor',
			// Theme Builder tools.
			'wp_theme_builder_status'             => 'elementor',
			'wp_list_theme_templates'             => 'elementor',
			'wp_get_theme_template'               => 'elementor',
			'wp_set_template_conditions'          => 'elementor',
			'wp_assign_template'                  => 'elementor',
		);
	}

	/**
	 * Get tool definitions for pro tier.
	 *
	 * @return array Tool definitions.
	 */
	public function get_tools() {
		$pro_tools = array();

		// Multilanguage Tools (WPML, Polylang, TranslatePress).
		$pro_tools[] = $this->define_tool(
			'wp_languages',
			'Get multilingual plugin status and available languages',
			array()
		);

		$pro_tools[] = $this->define_tool(
			'wp_set_language',
			'Set current language for subsequent translation operations',
			array(
				'language' => array(
					'type'        => 'string',
					'description' => 'Language code (e.g., fa, en)',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_get_translations',
			'Get translations for a post or page',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Post/Page ID',
					'required'    => true,
				),
				'type' => array(
					'type'        => 'string',
					'description' => 'Content type (post or page)',
					'default'     => 'page',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_create_translation',
			'Create a translation for a post or page in a target language',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Source Post/Page ID',
					'required'    => true,
				),
				'type' => array(
					'type'        => 'string',
					'description' => 'Content type (post or page)',
					'default'     => 'page',
				),
				'language' => array(
					'type'        => 'string',
					'description' => 'Target language code',
					'required'    => true,
				),
				'title' => array(
					'type'        => 'string',
					'description' => 'Translated title',
				),
				'content' => array(
					'type'        => 'string',
					'description' => 'Translated content',
				),
				'excerpt' => array(
					'type'        => 'string',
					'description' => 'Translated excerpt',
				),
				'status' => array(
					'type'        => 'string',
					'description' => 'Translation post status',
					'default'     => 'draft',
				),
			)
		);

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
			'wp_get_elementor_template',
			'Get a single Elementor template (Theme Builder template lives in elementor_library)',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Template post ID',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_create_elementor_template',
			'Create a new Elementor template (Theme Builder template lives in elementor_library)',
			array(
				'title'          => array(
					'type'        => 'string',
					'description' => 'Template title',
					'required'    => true,
				),
				'type'           => array(
					'type'        => 'string',
					'description' => 'Template type (e.g. header, footer, single, archive, section, page)',
					'required'    => true,
				),
				'elementor_data' => array(
					'type'        => 'array',
					'description' => 'Optional Elementor data JSON (array). If omitted, Elementor creates a blank template.',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_update_elementor_template',
			'Update an Elementor template',
			array(
				'id'             => array(
					'type'        => 'number',
					'description' => 'Template post ID',
					'required'    => true,
				),
				'title'          => array(
					'type'        => 'string',
					'description' => 'Optional new title',
				),
				'elementor_data' => array(
					'type'        => 'array',
					'description' => 'Optional Elementor data JSON (array)',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_delete_elementor_template',
			'Delete an Elementor template',
			array(
				'id'    => array(
					'type'        => 'number',
					'description' => 'Template post ID',
					'required'    => true,
				),
				'force' => array(
					'type'        => 'boolean',
					'description' => 'Whether to force delete (bypass trash)',
					'default'     => false,
				),
			)
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
			'wp_set_elementor_globals',
			'Set Elementor global settings (colors, typography, button styles, etc.). Merges with existing kit settings.',
			array(
				'system_colors' => array(
					'type'        => 'array',
					'description' => 'Array of {_id, title, color} objects for global colors',
				),
				'custom_colors' => array(
					'type'        => 'array',
					'description' => 'Array of {_id, title, color} objects for custom colors',
				),
				'system_typography' => array(
					'type'        => 'array',
					'description' => 'Array of typography objects with font_family, font_size, font_weight, etc.',
				),
				'custom_typography' => array(
					'type'        => 'array',
					'description' => 'Array of custom typography definitions',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_get_elementor_widgets',
			'Get list of available Elementor widgets',
			array()
		);

		$pro_tools[] = $this->define_tool(
			'wp_list_elementor_custom_code',
			'List Elementor Pro Custom Code snippets (admin)',
			array(
				'per_page' => array(
					'type'        => 'number',
					'description' => 'Maximum number of items per page',
				),
				'page'     => array(
					'type'        => 'number',
					'description' => 'Page number',
				),
				'status'   => array(
					'type'        => 'string',
					'description' => 'Filter by post status: publish|draft|any',
				),
				'search'   => array(
					'type'        => 'string',
					'description' => 'Search by snippet title',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_disable_elementor_custom_code',
			'Disable an Elementor Pro Custom Code snippet (sets status to draft)',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Snippet post ID',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_enable_elementor_custom_code',
			'Enable an Elementor Pro Custom Code snippet (sets status to publish)',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Snippet post ID',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_sanitize_elementor_custom_code',
			'Sanitize an Elementor Pro Custom Code snippet by stripping <html>/<head>/<body> tags from meta values',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Snippet post ID',
					'required'    => true,
				),
			)
		);

		// Theme Builder Tools
		$pro_tools[] = $this->define_tool(
			'wp_theme_builder_status',
			'Get Theme Builder availability, registered locations, and which templates are assigned',
			array()
		);

		$pro_tools[] = $this->define_tool(
			'wp_list_theme_templates',
			'List Theme Builder templates (header, footer, single, archive, etc.) with their display conditions',
			array(
				'type' => array(
					'type'        => 'string',
					'description' => 'Filter by template type: header, footer, single, single-post, single-page, archive, search-results, error-404',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_get_theme_template',
			'Get a single Theme Builder template with its current display conditions',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Template post ID',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_set_template_conditions',
			'Set display conditions on a Theme Builder template. Conditions are arrays like ["include","general","singular","post"] or ["exclude","general","singular","page"]',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Template post ID',
					'required'    => true,
				),
				'conditions' => array(
					'type'        => 'array',
					'description' => 'Array of condition arrays, e.g. [["include","general","singular","post"],["exclude","general","singular","page"]]',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_assign_template',
			'Shortcut to assign a Theme Builder template to a scope (entire_site, all_singular, all_archive, specific_posts, specific_post_type)',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Template post ID',
					'required'    => true,
				),
				'scope' => array(
					'type'        => 'string',
					'description' => 'Assignment scope: entire_site, all_singular, all_archive, specific_posts, specific_post_type',
					'default'     => 'entire_site',
				),
				'post_type' => array(
					'type'        => 'string',
					'description' => 'Post type for specific_post_type scope (e.g., post, page)',
				),
				'post_ids' => array(
					'type'        => 'array',
					'description' => 'Array of post IDs for specific_posts scope',
				),
			)
		);

		// Menu Management Tools (Pro)
		$pro_tools[] = $this->define_tool(
			'wp_get_menu',
			'Get a single menu with all items, assigned locations, and metadata',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Menu ID',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_create_menu',
			'Create a navigation menu with initial items and optional location assignment',
			array(
				'name' => array(
					'type'        => 'string',
					'description' => 'Menu name',
					'required'    => true,
				),
				'location' => array(
					'type'        => 'string',
					'description' => 'Theme menu location key to assign (e.g., primary)',
				),
				'items' => array(
					'type'        => 'array',
					'description' => 'Initial menu items to add (array of {title, url, type, object, object_id} objects)',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_update_menu',
			'Rename a menu or change its theme location assignment',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Menu ID',
					'required'    => true,
				),
				'name' => array(
					'type'        => 'string',
					'description' => 'New menu name',
				),
				'location' => array(
					'type'        => 'string',
					'description' => 'Theme menu location key to assign',
				),
			)
		);

		// Widget & Sidebar Management Tools
		$pro_tools[] = $this->define_tool(
			'wp_list_sidebars',
			'List all registered widget areas (sidebars) with widget counts',
			array()
		);

		$pro_tools[] = $this->define_tool(
			'wp_get_sidebar',
			'Get a single sidebar with its widgets',
			array(
				'id' => array(
					'type'        => 'string',
					'description' => 'Sidebar ID (e.g., sidebar-1, footer-1)',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_get_sidebar_widgets',
			'Get all widgets in a specific sidebar',
			array(
				'id' => array(
					'type'        => 'string',
					'description' => 'Sidebar ID',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_get_widget_types',
			'List all available widget types that can be added to sidebars',
			array()
		);

		$pro_tools[] = $this->define_tool(
			'wp_get_widget',
			'Get a single widget by ID with its settings',
			array(
				'id' => array(
					'type'        => 'string',
					'description' => 'Widget ID (e.g., text-2, custom_html-3)',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_add_widget',
			'Add a widget to a sidebar',
			array(
				'id' => array(
					'type'        => 'string',
					'description' => 'Sidebar ID to add the widget to',
					'required'    => true,
				),
				'type' => array(
					'type'        => 'string',
					'description' => 'Widget type (id_base, e.g., text, custom_html, search)',
					'required'    => true,
				),
				'settings' => array(
					'type'        => 'object',
					'description' => 'Widget settings (varies by widget type)',
				),
				'position' => array(
					'type'        => 'number',
					'description' => 'Position in sidebar (0-based index)',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_update_widget',
			'Update widget settings',
			array(
				'id' => array(
					'type'        => 'string',
					'description' => 'Widget ID (e.g., text-2)',
					'required'    => true,
				),
				'settings' => array(
					'type'        => 'object',
					'description' => 'New settings to merge with existing',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_delete_widget',
			'Delete a widget from its sidebar and remove its settings',
			array(
				'id' => array(
					'type'        => 'string',
					'description' => 'Widget ID (e.g., text-2)',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_move_widget',
			'Move a widget to a different sidebar',
			array(
				'id' => array(
					'type'        => 'string',
					'description' => 'Widget ID to move',
					'required'    => true,
				),
				'sidebar' => array(
					'type'        => 'string',
					'description' => 'Target sidebar ID',
					'required'    => true,
				),
				'position' => array(
					'type'        => 'number',
					'description' => 'Position in target sidebar',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_reorder_widgets',
			'Reorder widgets within a sidebar',
			array(
				'id' => array(
					'type'        => 'string',
					'description' => 'Sidebar ID',
					'required'    => true,
				),
				'widgets' => array(
					'type'        => 'array',
					'description' => 'Ordered array of widget IDs',
					'required'    => true,
				),
			)
		);

		return $pro_tools;
	}

	/**
	 * Get tool map for pro tier.
	 *
	 * @return array Tool mappings.
	 */
	public function get_tool_map() {
		return array(
			// SEO
			'wp_get_seo'                     => array(
				'method' => 'GET',
				'route'  => '/seo/{id}',
			),
			'wp_set_seo'                     => array(
				'method' => 'POST',
				'route'  => '/seo/{id}',
				'param_remap' => array(
					'seo_title'       => 'title',
					'seo_description' => 'description',
				),
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
			'wp_get_elementor_template'      => array(
				'method' => 'GET',
				'route'  => '/elementor/templates/{id}',
			),
			'wp_create_elementor_template'   => array(
				'method' => 'POST',
				'route'  => '/elementor/templates',
			),
			'wp_update_elementor_template'   => array(
				'method' => 'POST',
				'route'  => '/elementor/templates/{id}',
			),
			'wp_delete_elementor_template'   => array(
				'method' => 'DELETE',
				'route'  => '/elementor/templates/{id}',
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
			'wp_set_elementor_globals'       => array(
				'method' => 'POST',
				'route'  => '/elementor/globals',
			),
			'wp_get_elementor_widgets'       => array(
				'method' => 'GET',
				'route'  => '/elementor/widgets',
			),
			'wp_list_elementor_custom_code'  => array(
				'method' => 'GET',
				'route'  => '/elementor/custom-code',
			),
			'wp_disable_elementor_custom_code' => array(
				'method' => 'POST',
				'route'  => '/elementor/custom-code/{id}/disable',
			),
			'wp_enable_elementor_custom_code' => array(
				'method' => 'POST',
				'route'  => '/elementor/custom-code/{id}/enable',
			),
			'wp_sanitize_elementor_custom_code' => array(
				'method' => 'POST',
				'route'  => '/elementor/custom-code/{id}/sanitize',
			),

			// Theme Builder
			'wp_theme_builder_status'      => array(
				'method' => 'GET',
				'route'  => '/theme-builder/status',
			),
			'wp_list_theme_templates'      => array(
				'method' => 'GET',
				'route'  => '/theme-builder/templates',
			),
			'wp_get_theme_template'        => array(
				'method' => 'GET',
				'route'  => '/theme-builder/templates/{id}',
			),
			'wp_set_template_conditions'   => array(
				'method' => 'POST',
				'route'  => '/theme-builder/templates/{id}/conditions',
			),
			'wp_assign_template'           => array(
				'method' => 'POST',
				'route'  => '/theme-builder/templates/{id}/assign',
			),

			// Menu Management (Pro)
			'wp_get_menu'           => array(
				'method' => 'GET',
				'route'  => '/menus/{id}',
			),
			'wp_create_menu'        => array(
				'method' => 'POST',
				'route'  => '/menus',
			),
			'wp_update_menu'        => array(
				'method' => 'POST',
				'route'  => '/menus/{id}',
			),

			// Widgets & Sidebars
			'wp_list_sidebars'      => array(
				'method' => 'GET',
				'route'  => '/sidebars',
			),
			'wp_get_sidebar'        => array(
				'method' => 'GET',
				'route'  => '/sidebars/{id}',
			),
			'wp_get_sidebar_widgets' => array(
				'method' => 'GET',
				'route'  => '/sidebars/{id}/widgets',
			),
			'wp_get_widget_types'   => array(
				'method' => 'GET',
				'route'  => '/widgets/types',
			),
			'wp_get_widget'         => array(
				'method' => 'GET',
				'route'  => '/widgets/{id}',
			),
			'wp_add_widget'         => array(
				'method' => 'POST',
				'route'  => '/sidebars/{id}/widgets',
			),
			'wp_update_widget'      => array(
				'method' => 'PUT',
				'route'  => '/widgets/{id}',
			),
			'wp_delete_widget'      => array(
				'method' => 'DELETE',
				'route'  => '/widgets/{id}',
			),
			'wp_move_widget'        => array(
				'method' => 'POST',
				'route'  => '/widgets/{id}/move',
			),
			'wp_reorder_widgets'    => array(
				'method' => 'POST',
				'route'  => '/sidebars/{id}/reorder',
			),

			// Multilanguage (map these at the end after Widgets)
			'wp_languages'       => array(
				'method' => 'GET',
				'route'  => '/languages',
			),
			'wp_set_language'     => array(
				'method' => 'POST',
				'route'  => '/languages/current',
			),
			'wp_get_translations' => array(
				'method' => 'GET',
				'route'  => '/{type}s/{id}/translations',
			),
			'wp_create_translation' => array(
				'method' => 'POST',
				'route'  => '/{type}s/{id}/translations',
			),
		);
	}
}
