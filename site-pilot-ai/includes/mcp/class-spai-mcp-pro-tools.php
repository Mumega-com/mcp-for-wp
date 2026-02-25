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
			'wc_delete_product',
			'wp_delete_course_category',
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
	 * Get tool category mappings for pro tier.
	 *
	 * @return array Map of tool_name => category_slug.
	 */
	public function get_tool_categories() {
		return array(
			// Google Indexing
			'wp_submit_to_google_index'          => 'seo',
			'wp_google_index_status'             => 'seo',

			// Multilanguage
			'wp_languages'                      => 'content',
			'wp_set_language'                    => 'content',
			'wp_get_translations'                => 'content',
			'wp_create_translation'              => 'content',

			// SEO
			'wp_get_seo'                         => 'seo',
			'wp_set_seo'                         => 'seo',
			'wp_analyze_seo'                     => 'seo',
			'wp_bulk_seo'                        => 'seo',
			'wp_seo_status'                      => 'seo',
			'wp_set_noindex'                     => 'seo',
			'wp_seo_scan'                        => 'seo',
			'wp_seo_report'                      => 'seo',

			// Forms
			'wp_list_forms'                      => 'forms',
			'wp_get_form'                        => 'forms',
			'wp_get_form_entries'                => 'forms',
			'wp_forms_status'                    => 'forms',

			// Elementor Pro
			'wp_list_elementor_templates'        => 'elementor',
			'wp_get_elementor_template'          => 'elementor',
			'wp_create_elementor_template'       => 'elementor',
			'wp_update_elementor_template'       => 'elementor',
			'wp_delete_elementor_template'       => 'elementor',
			'wp_apply_elementor_template'        => 'elementor',
			'wp_create_landing_page'             => 'elementor',
			'wp_clone_elementor_page'            => 'elementor',
			'wp_get_elementor_globals'           => 'elementor',
			'wp_set_elementor_globals'           => 'elementor',
			'wp_list_elementor_custom_code'      => 'elementor',
			'wp_disable_elementor_custom_code'   => 'elementor',
			'wp_enable_elementor_custom_code'    => 'elementor',
			'wp_sanitize_elementor_custom_code'  => 'elementor',

			// Theme Builder
			'wp_theme_builder_status'            => 'elementor',
			'wp_list_theme_templates'            => 'elementor',
			'wp_get_theme_template'              => 'elementor',
			'wp_set_template_conditions'         => 'elementor',
			'wp_assign_template'                 => 'elementor',
			'wp_create_theme_template'           => 'elementor',
			'wp_build_page'                      => 'elementor',

			// Menu Management (Pro)
			'wp_get_menu'                        => 'site',
			'wp_create_menu'                     => 'site',
			'wp_update_menu'                     => 'site',

			// WooCommerce
			'wc_status'                          => 'woocommerce',
			'wc_list_products'                   => 'woocommerce',
			'wc_get_product'                     => 'woocommerce',
			'wc_create_product'                  => 'woocommerce',
			'wc_update_product'                  => 'woocommerce',
			'wc_delete_product'                  => 'woocommerce',
			'wc_list_product_categories'         => 'woocommerce',
			'wc_create_product_category'         => 'woocommerce',
			'wc_update_product_category'         => 'woocommerce',
			'wc_list_product_tags'               => 'woocommerce',
			'wc_list_orders'                     => 'woocommerce',
			'wc_get_order'                       => 'woocommerce',
			'wc_update_order'                    => 'woocommerce',
			'wc_list_order_statuses'             => 'woocommerce',
			'wc_list_customers'                  => 'woocommerce',
			'wc_get_customer'                    => 'woocommerce',
			'wc_analytics'                       => 'woocommerce',

			// Widgets & Sidebars
			'wp_list_sidebars'                   => 'site',
			'wp_get_sidebar'                     => 'site',
			'wp_get_sidebar_widgets'             => 'site',
			'wp_get_widget_types'                => 'site',
			'wp_get_widget'                      => 'site',
			'wp_add_widget'                      => 'site',
			'wp_update_widget'                   => 'site',
			'wp_delete_widget'                   => 'site',
			'wp_move_widget'                     => 'site',
			'wp_reorder_widgets'                 => 'site',

			// LearnPress LMS
			'wp_list_courses'                    => 'learnpress',
			'wp_get_course'                      => 'learnpress',
			'wp_create_course'                   => 'learnpress',
			'wp_update_course'                   => 'learnpress',
			'wp_get_curriculum'                  => 'learnpress',
			'wp_set_curriculum'                  => 'learnpress',
			'wp_list_lessons'                    => 'learnpress',
			'wp_create_lesson'                   => 'learnpress',
			'wp_update_lesson'                   => 'learnpress',
			'wp_list_quizzes'                    => 'learnpress',
			'wp_create_quiz'                     => 'learnpress',
			'wp_update_quiz'                     => 'learnpress',
			'wp_get_quiz_questions'              => 'learnpress',
			'wp_list_course_categories'          => 'learnpress',
			'wp_create_course_category'          => 'learnpress',
			'wp_update_course_category'          => 'learnpress',
			'wp_delete_course_category'          => 'learnpress',
			'wp_lms_stats'                       => 'learnpress',

			// Events
			'wp_list_events'                     => 'events',
			'wp_get_event'                       => 'events',
			'wp_create_event'                    => 'events',
			'wp_update_event'                    => 'events',

			// Multisite
			'wp_network_sites'                   => 'multisite',
			'wp_network_switch'                  => 'multisite',
			'wp_network_stats'                   => 'multisite',
		);
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
			'wp_set_noindex'        => 'seo',
			'wp_seo_scan'           => 'seo',
			'wp_seo_report'         => 'seo',
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
			'wp_list_elementor_custom_code'       => 'elementor',
			'wp_disable_elementor_custom_code'    => 'elementor',
			'wp_enable_elementor_custom_code'     => 'elementor',
			'wp_sanitize_elementor_custom_code'   => 'elementor',
			// WooCommerce tools.
			'wc_status'                          => 'woocommerce',
			'wc_list_products'                   => 'woocommerce',
			'wc_get_product'                     => 'woocommerce',
			'wc_create_product'                  => 'woocommerce',
			'wc_update_product'                  => 'woocommerce',
			'wc_delete_product'                  => 'woocommerce',
			'wc_list_product_categories'         => 'woocommerce',
			'wc_create_product_category'         => 'woocommerce',
			'wc_update_product_category'         => 'woocommerce',
			'wc_list_product_tags'               => 'woocommerce',
			'wc_list_orders'                     => 'woocommerce',
			'wc_get_order'                       => 'woocommerce',
			'wc_update_order'                    => 'woocommerce',
			'wc_list_order_statuses'             => 'woocommerce',
			'wc_list_customers'                  => 'woocommerce',
			'wc_get_customer'                    => 'woocommerce',
			'wc_analytics'                       => 'woocommerce',
			// Theme Builder tools.
			'wp_theme_builder_status'             => 'elementor',
			'wp_list_theme_templates'             => 'elementor',
			'wp_get_theme_template'               => 'elementor',
			'wp_set_template_conditions'          => 'elementor',
			'wp_assign_template'                  => 'elementor',
			'wp_create_theme_template'            => 'elementor',
			'wp_build_page'                       => 'elementor',
			// LearnPress tools.
			'wp_list_courses'                    => 'learnpress',
			'wp_get_course'                      => 'learnpress',
			'wp_create_course'                   => 'learnpress',
			'wp_update_course'                   => 'learnpress',
			'wp_get_curriculum'                  => 'learnpress',
			'wp_set_curriculum'                  => 'learnpress',
			'wp_list_lessons'                    => 'learnpress',
			'wp_create_lesson'                   => 'learnpress',
			'wp_update_lesson'                   => 'learnpress',
			'wp_list_quizzes'                    => 'learnpress',
			'wp_create_quiz'                     => 'learnpress',
			'wp_update_quiz'                     => 'learnpress',
			'wp_get_quiz_questions'              => 'learnpress',
			'wp_list_course_categories'          => 'learnpress',
			'wp_create_course_category'          => 'learnpress',
			'wp_update_course_category'          => 'learnpress',
			'wp_delete_course_category'          => 'learnpress',
			'wp_lms_stats'                       => 'learnpress',
			// Events tools.
			'wp_list_events'                     => 'tp_events',
			'wp_get_event'                       => 'tp_events',
			'wp_create_event'                    => 'tp_events',
			'wp_update_event'                    => 'tp_events',
			// Multisite tools.
			'wp_network_sites'                   => 'multisite',
			'wp_network_switch'                  => 'multisite',
			'wp_network_stats'                   => 'multisite',
		);
	}

	/**
	 * Get tool definitions for pro tier.
	 *
	 * @return array Tool definitions.
	 */
	public function get_tools() {
		$pro_tools = array();

		// Google Indexing API Tools.
		$tools[] = $this->define_tool(
			'wp_submit_to_google_index',
			'Submit one or more URLs to Google for indexing via the Indexing API. Requires Google Indexing API integration to be configured. Use action URL_UPDATED for new/updated pages and URL_DELETED for removed pages. Limited to 200 URLs per day by Google.',
			array(
				'urls'   => array(
					'type'        => 'array',
					'description' => 'Array of URLs to submit for indexing',
					'required'    => true,
				),
				'action' => array(
					'type'        => 'string',
					'description' => 'Notification type: URL_UPDATED (new/updated page) or URL_DELETED (removed page)',
					'default'     => 'URL_UPDATED',
				),
			)
		);

		$tools[] = $this->define_tool(
			'wp_google_index_status',
			'Check Google indexing status for a URL. Returns the latest update and removal notification times from the Indexing API. Requires Google Indexing API integration to be configured.',
			array(
				'url' => array(
					'type'        => 'string',
					'description' => 'URL to check indexing status for',
					'required'    => true,
				),
			)
		);

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
				'canonical'       => array(
					'type'        => 'string',
					'description' => 'Canonical URL',
				),
				'noindex'         => array(
					'type'        => 'boolean',
					'description' => 'Set to true to add noindex meta robots tag',
				),
				'nofollow'        => array(
					'type'        => 'boolean',
					'description' => 'Set to true to add nofollow meta robots tag',
				),
				'og_title'        => array(
					'type'        => 'string',
					'description' => 'Open Graph title for social sharing',
				),
				'og_description'  => array(
					'type'        => 'string',
					'description' => 'Open Graph description for social sharing',
				),
				'og_image'        => array(
					'type'        => 'string',
					'description' => 'Open Graph image URL for social sharing',
				),
				'twitter_title'   => array(
					'type'        => 'string',
					'description' => 'Twitter card title',
				),
				'twitter_description' => array(
					'type'        => 'string',
					'description' => 'Twitter card description',
				),
				'twitter_image'   => array(
					'type'        => 'string',
					'description' => 'Twitter card image URL',
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
				'updates' => array(
					'type'        => 'array',
					'description' => 'Array of objects. Each must have id (post/page ID) plus any SEO fields: title, description, focus_keyword, canonical_url, noindex (bool), nofollow (bool), og_title, og_description, og_image',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_seo_status',
			'Get SEO plugin status and configuration',
			array()
		);

		$pro_tools[] = $this->define_tool(
			'wp_seo_scan',
			'Scan all published content for SEO issues. Returns missing titles, descriptions, thin content, and more.',
			array(
				'threshold' => array(
					'type'        => 'number',
					'description' => 'Minimum word count for thin content detection (default: 300)',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_seo_report',
			'Export complete SEO metadata for all published content. Returns title, description, keyword, noindex, canonical, word count for every post and page.',
			array(
				'post_type' => array(
					'type'        => 'string',
					'description' => 'Filter by post type (e.g. post, page)',
				),
				'limit' => array(
					'type'        => 'number',
					'description' => 'Maximum number of posts to return (default: 100, max: 500)',
				),
			)
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
			'wp_build_page',
			'Build a page from semantic section blueprints. Generates valid Elementor data automatically. Supported section types: hero, features, cta, pricing, faq, testimonials, text, gallery, contact_form, map, countdown, stats, logo_grid, video.',
			array(
				'title' => array(
					'type'        => 'string',
					'description' => 'Page title',
					'required'    => true,
				),
				'sections' => array(
					'type'        => 'array',
					'description' => 'Array of section objects. Each has "type" plus type-specific params. Hero: heading, subheading, cta_text, cta_url, background (color/#hex/gradient), image_url. Features: heading, columns, items[{icon, title, desc}]. CTA: heading, subheading, button_text, button_url, background. Pricing: heading, plans[{title, price, period, features[], button_text, button_url}]. FAQ: heading, items[{question, answer}]. Testimonials: heading, items[{text, name, title, image}]. Text: heading, content. Gallery: heading, images[], columns. Contact_form: heading, subheading, form_id, plugin (wpforms/cf7/gravity). Map: heading, address, zoom (1-20), height. Countdown: heading, due_date (YYYY-MM-DD HH:MM), subheading. Stats: heading, columns, items[{number, title, suffix}]. Logo_grid: heading, columns, items[{image, url}]. Video: heading, url (YouTube/Vimeo/hosted mp4), subheading.',
					'required'    => true,
				),
				'status' => array(
					'type'        => 'string',
					'description' => 'Page status: draft (default), publish, private',
					'default'     => 'draft',
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
			'Set Elementor global settings (colors, typography, button styles, kit-level CSS, etc.). Merges with existing kit settings. Use custom_css for site-wide Elementor CSS that persists independently of theme Custom CSS.',
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
				'custom_css' => array(
					'type'        => 'string',
					'description' => 'Kit-level custom CSS. Applied site-wide via Elementor (separate from WordPress Customizer CSS). Replaces any existing kit CSS.',
				),
			)
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
					'description' => 'Post type for specific_post_type scope (e.g., post, page, tp_event, lp_course)',
				),
				'post_ids' => array(
					'type'        => 'array',
					'description' => 'Array of post IDs for specific_posts scope',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_create_theme_template',
			'Create a Theme Builder template (header, footer, single, archive) and assign it to a display scope in one step',
			array(
				'title' => array(
					'type'        => 'string',
					'description' => 'Template title',
					'required'    => true,
				),
				'type' => array(
					'type'        => 'string',
					'description' => 'Template type: header, footer, single, archive',
					'required'    => true,
				),
				'elementor_data' => array(
					'type'        => 'array',
					'description' => 'Optional Elementor JSON data for the template content',
				),
				'scope' => array(
					'type'        => 'string',
					'description' => 'Display scope: entire_site, specific_post_type, specific_posts, archive, front_page, 404',
					'default'     => 'entire_site',
				),
				'post_type' => array(
					'type'        => 'string',
					'description' => 'Post type for specific_post_type scope (e.g., post, page, tp_event, lp_course)',
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

		$pro_tools[] = $this->define_tool(
			'wp_set_noindex',
			'Set or remove noindex on a page or post (controls search engine indexing). Convenience wrapper around wp_set_seo.',
			array(
				'id'      => array(
					'type'        => 'number',
					'description' => 'Page or post ID',
					'required'    => true,
				),
				'noindex' => array(
					'type'        => 'boolean',
					'description' => 'true to add noindex (hide from search), false to remove it (allow indexing)',
					'required'    => true,
				),
			)
		);

		// =====================================================================
		// WooCommerce Tools
		// =====================================================================

		$pro_tools[] = $this->define_tool(
			'wc_status',
			'Get WooCommerce status: version, currency, tax settings, product/order counts',
			array()
		);

		$pro_tools[] = $this->define_tool(
			'wc_list_products',
			'List WooCommerce products with price, stock, SKU, categories, tags. Supports filtering by type, category, tag, SKU, stock status, and search.',
			array(
				'per_page'     => array(
					'type'        => 'number',
					'description' => 'Items per page (default 50, max 100)',
				),
				'page'         => array(
					'type'        => 'number',
					'description' => 'Page number',
				),
				'status'       => array(
					'type'        => 'string',
					'description' => 'Product status: publish, draft, pending, private, any',
				),
				'type'         => array(
					'type'        => 'string',
					'description' => 'Product type: simple, variable, grouped, external',
				),
				'category'     => array(
					'type'        => 'string',
					'description' => 'Filter by category slug',
				),
				'tag'          => array(
					'type'        => 'string',
					'description' => 'Filter by tag slug',
				),
				'search'       => array(
					'type'        => 'string',
					'description' => 'Search term',
				),
				'sku'          => array(
					'type'        => 'string',
					'description' => 'Exact SKU match',
				),
				'stock_status' => array(
					'type'        => 'string',
					'description' => 'Stock status: instock, outofstock, onbackorder',
				),
				'orderby'      => array(
					'type'        => 'string',
					'description' => 'Order by: date, title, price, popularity, rating',
				),
				'order'        => array(
					'type'        => 'string',
					'description' => 'Sort order: ASC or DESC',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wc_get_product',
			'Get a single WooCommerce product with full details: description, images, attributes, dimensions, variations (for variable products)',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Product ID',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wc_create_product',
			'Create a WooCommerce product. Supports simple, variable, grouped, and external product types.',
			array(
				'name'              => array(
					'type'        => 'string',
					'description' => 'Product name',
					'required'    => true,
				),
				'type'              => array(
					'type'        => 'string',
					'description' => 'Product type: simple (default), variable, grouped, external',
				),
				'status'            => array(
					'type'        => 'string',
					'description' => 'Product status: publish (default), draft, pending, private',
				),
				'description'       => array(
					'type'        => 'string',
					'description' => 'Full product description (HTML)',
				),
				'short_description' => array(
					'type'        => 'string',
					'description' => 'Short description (HTML)',
				),
				'sku'               => array(
					'type'        => 'string',
					'description' => 'Product SKU',
				),
				'regular_price'     => array(
					'type'        => 'string',
					'description' => 'Regular price',
				),
				'sale_price'        => array(
					'type'        => 'string',
					'description' => 'Sale price',
				),
				'manage_stock'      => array(
					'type'        => 'boolean',
					'description' => 'Enable stock management',
				),
				'stock_quantity'    => array(
					'type'        => 'number',
					'description' => 'Stock quantity (requires manage_stock: true)',
				),
				'stock_status'      => array(
					'type'        => 'string',
					'description' => 'Stock status: instock, outofstock, onbackorder',
				),
				'categories'        => array(
					'type'        => 'array',
					'description' => 'Category names or IDs (auto-creates if name not found)',
				),
				'tags'              => array(
					'type'        => 'array',
					'description' => 'Tag names or IDs (auto-creates if name not found)',
				),
				'weight'            => array(
					'type'        => 'string',
					'description' => 'Product weight',
				),
				'length'            => array(
					'type'        => 'string',
					'description' => 'Product length',
				),
				'width'             => array(
					'type'        => 'string',
					'description' => 'Product width',
				),
				'height'            => array(
					'type'        => 'string',
					'description' => 'Product height',
				),
				'image_id'          => array(
					'type'        => 'number',
					'description' => 'Main image attachment ID',
				),
				'gallery_image_ids' => array(
					'type'        => 'array',
					'description' => 'Gallery image attachment IDs',
				),
				'virtual'           => array(
					'type'        => 'boolean',
					'description' => 'Virtual product (no shipping)',
				),
				'downloadable'      => array(
					'type'        => 'boolean',
					'description' => 'Downloadable product',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wc_update_product',
			'Update a WooCommerce product. Any field not provided is left unchanged.',
			array(
				'id'                => array(
					'type'        => 'number',
					'description' => 'Product ID',
					'required'    => true,
				),
				'name'              => array(
					'type'        => 'string',
					'description' => 'Product name',
				),
				'status'            => array(
					'type'        => 'string',
					'description' => 'Product status: publish, draft, pending, private',
				),
				'description'       => array(
					'type'        => 'string',
					'description' => 'Full product description (HTML)',
				),
				'short_description' => array(
					'type'        => 'string',
					'description' => 'Short description (HTML)',
				),
				'sku'               => array(
					'type'        => 'string',
					'description' => 'Product SKU',
				),
				'regular_price'     => array(
					'type'        => 'string',
					'description' => 'Regular price',
				),
				'sale_price'        => array(
					'type'        => 'string',
					'description' => 'Sale price',
				),
				'manage_stock'      => array(
					'type'        => 'boolean',
					'description' => 'Enable stock management',
				),
				'stock_quantity'    => array(
					'type'        => 'number',
					'description' => 'Stock quantity',
				),
				'stock_status'      => array(
					'type'        => 'string',
					'description' => 'Stock status: instock, outofstock, onbackorder',
				),
				'categories'        => array(
					'type'        => 'array',
					'description' => 'Category names or IDs (replaces existing)',
				),
				'tags'              => array(
					'type'        => 'array',
					'description' => 'Tag names or IDs (replaces existing)',
				),
				'weight'            => array(
					'type'        => 'string',
					'description' => 'Product weight',
				),
				'image_id'          => array(
					'type'        => 'number',
					'description' => 'Main image attachment ID',
				),
				'gallery_image_ids' => array(
					'type'        => 'array',
					'description' => 'Gallery image attachment IDs',
				),
				'virtual'           => array(
					'type'        => 'boolean',
					'description' => 'Virtual product',
				),
				'downloadable'      => array(
					'type'        => 'boolean',
					'description' => 'Downloadable product',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wc_delete_product',
			'Delete a WooCommerce product. By default moves to trash; use force to permanently delete.',
			array(
				'id'    => array(
					'type'        => 'number',
					'description' => 'Product ID',
					'required'    => true,
				),
				'force' => array(
					'type'        => 'boolean',
					'description' => 'Permanently delete (bypass trash)',
					'default'     => false,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wc_list_product_categories',
			'List all WooCommerce product categories (product_cat taxonomy) with ID, name, slug, parent, and product count',
			array()
		);

		$pro_tools[] = $this->define_tool(
			'wc_create_product_category',
			'Create a WooCommerce product category',
			array(
				'name'        => array(
					'type'        => 'string',
					'description' => 'Category name',
					'required'    => true,
				),
				'slug'        => array(
					'type'        => 'string',
					'description' => 'Category slug (auto-generated from name if omitted)',
				),
				'description' => array(
					'type'        => 'string',
					'description' => 'Category description',
				),
				'parent'      => array(
					'type'        => 'number',
					'description' => 'Parent category ID for nested categories',
				),
				'image_id'    => array(
					'type'        => 'number',
					'description' => 'Category thumbnail image attachment ID',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wc_update_product_category',
			'Update a WooCommerce product category',
			array(
				'id'          => array(
					'type'        => 'number',
					'description' => 'Category (term) ID',
					'required'    => true,
				),
				'name'        => array(
					'type'        => 'string',
					'description' => 'Category name',
				),
				'slug'        => array(
					'type'        => 'string',
					'description' => 'Category slug',
				),
				'description' => array(
					'type'        => 'string',
					'description' => 'Category description',
				),
				'parent'      => array(
					'type'        => 'number',
					'description' => 'Parent category ID',
				),
				'image_id'    => array(
					'type'        => 'number',
					'description' => 'Category thumbnail image attachment ID',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wc_list_product_tags',
			'List all WooCommerce product tags with ID, name, slug, and product count',
			array()
		);

		$pro_tools[] = $this->define_tool(
			'wc_list_orders',
			'List WooCommerce orders with status, totals, customer info. Supports filtering by status, customer, and date range.',
			array(
				'per_page' => array(
					'type'        => 'number',
					'description' => 'Items per page (default 50, max 100)',
				),
				'page'     => array(
					'type'        => 'number',
					'description' => 'Page number',
				),
				'status'   => array(
					'type'        => 'string',
					'description' => 'Order status: any, pending, processing, on-hold, completed, cancelled, refunded, failed',
				),
				'customer' => array(
					'type'        => 'number',
					'description' => 'Filter by customer ID',
				),
				'after'    => array(
					'type'        => 'string',
					'description' => 'Orders after date (ISO 8601)',
				),
				'before'   => array(
					'type'        => 'string',
					'description' => 'Orders before date (ISO 8601)',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wc_get_order',
			'Get a single WooCommerce order with full details: items, billing/shipping addresses, notes, payment method',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Order ID',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wc_update_order',
			'Update a WooCommerce order status and/or add a note',
			array(
				'id'            => array(
					'type'        => 'number',
					'description' => 'Order ID',
					'required'    => true,
				),
				'status'        => array(
					'type'        => 'string',
					'description' => 'New order status: pending, processing, on-hold, completed, cancelled, refunded, failed',
				),
				'note'          => array(
					'type'        => 'string',
					'description' => 'Order note to add',
				),
				'note_customer' => array(
					'type'        => 'boolean',
					'description' => 'Send note to customer (default false)',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wc_list_order_statuses',
			'List all available WooCommerce order statuses (including custom statuses)',
			array()
		);

		$pro_tools[] = $this->define_tool(
			'wc_list_customers',
			'List WooCommerce customers with order count and total spent',
			array(
				'per_page' => array(
					'type'        => 'number',
					'description' => 'Items per page (default 50, max 100)',
				),
				'page'     => array(
					'type'        => 'number',
					'description' => 'Page number',
				),
				'search'   => array(
					'type'        => 'string',
					'description' => 'Search by name, email, or username',
				),
				'orderby'  => array(
					'type'        => 'string',
					'description' => 'Order by: registered, display_name, user_login, user_email',
				),
				'order'    => array(
					'type'        => 'string',
					'description' => 'Sort order: ASC or DESC',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wc_get_customer',
			'Get a single WooCommerce customer with billing/shipping addresses and order history summary',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Customer (user) ID',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wc_analytics',
			'Get WooCommerce analytics: sales totals, order counts, average order value, top-selling products, stock status, customers. Supports period (day/week/month/year) or custom date range.',
			array(
				'period'   => array(
					'type'        => 'string',
					'description' => 'Time period: day, week, month (default), year',
				),
				'date_min' => array(
					'type'        => 'string',
					'description' => 'Custom start date (ISO 8601, overrides period)',
				),
				'date_max' => array(
					'type'        => 'string',
					'description' => 'Custom end date (ISO 8601, overrides period)',
				),
			)
		);

		// =====================================================================
		// LearnPress LMS Tools
		// =====================================================================

		$pro_tools[] = $this->define_tool(
			'wp_list_courses',
			'List LearnPress courses with price, duration, level, instructor, enrollment count, lesson/quiz count, and categories. Supports search, status, and category filters.',
			array(
				'per_page' => array(
					'type'        => 'number',
					'description' => 'Items per page (default 50, max 100)',
				),
				'page'     => array(
					'type'        => 'number',
					'description' => 'Page number',
				),
				'status'   => array(
					'type'        => 'string',
					'description' => 'Course status: publish, draft, pending, private, any',
				),
				'search'   => array(
					'type'        => 'string',
					'description' => 'Search term',
				),
				'category' => array(
					'type'        => 'string',
					'description' => 'Category slug or ID to filter by',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_get_course',
			'Get full LearnPress course details: title, content, price, duration, level, requirements, target audiences, key features, FAQs, featured review, enrollment count, curriculum summary.',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Course ID',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_create_course',
			'Create a LearnPress course with title, content, price, duration (e.g. "10 week"), level (all/beginner/intermediate/advanced), requirements, target audiences, key features, FAQs, and categories.',
			array(
				'title'            => array(
					'type'        => 'string',
					'description' => 'Course title',
					'required'    => true,
				),
				'content'          => array(
					'type'        => 'string',
					'description' => 'Course description (HTML)',
				),
				'excerpt'          => array(
					'type'        => 'string',
					'description' => 'Course short description',
				),
				'status'           => array(
					'type'        => 'string',
					'description' => 'Post status: draft (default), publish, private',
					'default'     => 'draft',
				),
				'regular_price'    => array(
					'type'        => 'string',
					'description' => 'Regular price',
				),
				'sale_price'       => array(
					'type'        => 'string',
					'description' => 'Sale price',
				),
				'duration'         => array(
					'type'        => 'string',
					'description' => 'Course duration (e.g. "10 week", "3 month")',
				),
				'level'            => array(
					'type'        => 'string',
					'description' => 'Course level: all, beginner, intermediate, advanced',
				),
				'requirements'     => array(
					'type'        => 'array',
					'description' => 'Array of prerequisite strings',
				),
				'target_audiences' => array(
					'type'        => 'array',
					'description' => 'Array of target audience strings',
				),
				'key_features'     => array(
					'type'        => 'array',
					'description' => 'Array of key feature strings',
				),
				'faqs'             => array(
					'type'        => 'array',
					'description' => 'Array of [question, answer] pairs',
				),
				'featured_review'  => array(
					'type'        => 'string',
					'description' => 'Featured review text',
				),
				'categories'       => array(
					'type'        => 'array',
					'description' => 'Array of category names or IDs',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_update_course',
			'Update a LearnPress course. Any field can be updated: title, content, price, duration, level, requirements, target audiences, key features, FAQs, categories.',
			array(
				'id'               => array(
					'type'        => 'number',
					'description' => 'Course ID',
					'required'    => true,
				),
				'title'            => array(
					'type'        => 'string',
					'description' => 'Course title',
				),
				'content'          => array(
					'type'        => 'string',
					'description' => 'Course description (HTML)',
				),
				'excerpt'          => array(
					'type'        => 'string',
					'description' => 'Course short description',
				),
				'status'           => array(
					'type'        => 'string',
					'description' => 'Post status',
				),
				'regular_price'    => array(
					'type'        => 'string',
					'description' => 'Regular price',
				),
				'sale_price'       => array(
					'type'        => 'string',
					'description' => 'Sale price',
				),
				'duration'         => array(
					'type'        => 'string',
					'description' => 'Course duration (e.g. "10 week")',
				),
				'level'            => array(
					'type'        => 'string',
					'description' => 'Course level: all, beginner, intermediate, advanced',
				),
				'requirements'     => array(
					'type'        => 'array',
					'description' => 'Array of prerequisite strings',
				),
				'target_audiences' => array(
					'type'        => 'array',
					'description' => 'Array of target audience strings',
				),
				'key_features'     => array(
					'type'        => 'array',
					'description' => 'Array of key feature strings',
				),
				'faqs'             => array(
					'type'        => 'array',
					'description' => 'Array of [question, answer] pairs',
				),
				'featured_review'  => array(
					'type'        => 'string',
					'description' => 'Featured review text',
				),
				'categories'       => array(
					'type'        => 'array',
					'description' => 'Array of category names or IDs',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_get_curriculum',
			'Get the curriculum (sections and items) for a LearnPress course. Returns sections with their lessons and quizzes in order.',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Course ID',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_set_curriculum',
			'Set or replace the curriculum for a LearnPress course. Provide an array of sections, each with name, description, and items (lesson/quiz IDs with types). Lessons and quizzes must be created first.',
			array(
				'id'       => array(
					'type'        => 'number',
					'description' => 'Course ID',
					'required'    => true,
				),
				'sections' => array(
					'type'        => 'array',
					'description' => 'Array of section objects: {name, description, items: [{id, type: "lp_lesson"|"lp_quiz"}]}',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_list_lessons',
			'List LearnPress lessons with duration and preview status. Optionally filter by course_id.',
			array(
				'per_page'  => array(
					'type'        => 'number',
					'description' => 'Items per page (default 50, max 100)',
				),
				'page'      => array(
					'type'        => 'number',
					'description' => 'Page number',
				),
				'course_id' => array(
					'type'        => 'number',
					'description' => 'Filter lessons by course ID',
				),
				'search'    => array(
					'type'        => 'string',
					'description' => 'Search term',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_create_lesson',
			'Create a LearnPress lesson with title, content, duration, and preview flag. After creating, add to a course curriculum using wp_set_curriculum.',
			array(
				'title'    => array(
					'type'        => 'string',
					'description' => 'Lesson title',
					'required'    => true,
				),
				'content'  => array(
					'type'        => 'string',
					'description' => 'Lesson content (HTML)',
				),
				'status'   => array(
					'type'        => 'string',
					'description' => 'Post status (default: publish)',
					'default'     => 'publish',
				),
				'duration' => array(
					'type'        => 'string',
					'description' => 'Lesson duration (e.g. "30 minute", "1 hour")',
				),
				'preview'  => array(
					'type'        => 'boolean',
					'description' => 'Allow free preview of this lesson',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_update_lesson',
			'Update a LearnPress lesson: title, content, duration, preview flag.',
			array(
				'id'       => array(
					'type'        => 'number',
					'description' => 'Lesson ID',
					'required'    => true,
				),
				'title'    => array(
					'type'        => 'string',
					'description' => 'Lesson title',
				),
				'content'  => array(
					'type'        => 'string',
					'description' => 'Lesson content (HTML)',
				),
				'status'   => array(
					'type'        => 'string',
					'description' => 'Post status',
				),
				'duration' => array(
					'type'        => 'string',
					'description' => 'Lesson duration (e.g. "30 minute")',
				),
				'preview'  => array(
					'type'        => 'boolean',
					'description' => 'Allow free preview',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_list_quizzes',
			'List LearnPress quizzes with duration, passing grade, and review settings. Optionally filter by course_id.',
			array(
				'per_page'  => array(
					'type'        => 'number',
					'description' => 'Items per page (default 50, max 100)',
				),
				'page'      => array(
					'type'        => 'number',
					'description' => 'Page number',
				),
				'course_id' => array(
					'type'        => 'number',
					'description' => 'Filter quizzes by course ID',
				),
				'search'    => array(
					'type'        => 'string',
					'description' => 'Search term',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_create_quiz',
			'Create a LearnPress quiz with settings: duration (e.g. "40 minute"), passing grade, retake count, instant check, review. After creating, add to a course curriculum using wp_set_curriculum.',
			array(
				'title'          => array(
					'type'        => 'string',
					'description' => 'Quiz title',
					'required'    => true,
				),
				'content'        => array(
					'type'        => 'string',
					'description' => 'Quiz description (HTML)',
				),
				'status'         => array(
					'type'        => 'string',
					'description' => 'Post status (default: publish)',
					'default'     => 'publish',
				),
				'duration'       => array(
					'type'        => 'string',
					'description' => 'Quiz duration (e.g. "40 minute")',
				),
				'passing_grade'  => array(
					'type'        => 'string',
					'description' => 'Passing grade percentage (e.g. "80")',
				),
				'retake_count'   => array(
					'type'        => 'string',
					'description' => 'Number of allowed retakes (0 = unlimited)',
				),
				'instant_check'  => array(
					'type'        => 'string',
					'description' => 'Enable instant answer check: yes/no',
				),
				'review'         => array(
					'type'        => 'string',
					'description' => 'Allow review after completion: yes/no',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_update_quiz',
			'Update a LearnPress quiz: title, content, duration, passing grade, retake count, instant check, review.',
			array(
				'id'             => array(
					'type'        => 'number',
					'description' => 'Quiz ID',
					'required'    => true,
				),
				'title'          => array(
					'type'        => 'string',
					'description' => 'Quiz title',
				),
				'content'        => array(
					'type'        => 'string',
					'description' => 'Quiz description (HTML)',
				),
				'status'         => array(
					'type'        => 'string',
					'description' => 'Post status',
				),
				'duration'       => array(
					'type'        => 'string',
					'description' => 'Quiz duration (e.g. "40 minute")',
				),
				'passing_grade'  => array(
					'type'        => 'string',
					'description' => 'Passing grade percentage',
				),
				'retake_count'   => array(
					'type'        => 'string',
					'description' => 'Number of allowed retakes',
				),
				'instant_check'  => array(
					'type'        => 'string',
					'description' => 'Enable instant answer check: yes/no',
				),
				'review'         => array(
					'type'        => 'string',
					'description' => 'Allow review after completion: yes/no',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_get_quiz_questions',
			'Get all questions for a LearnPress quiz with their titles, content, and order.',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Quiz ID',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_list_course_categories',
			'List all LearnPress course categories with name, slug, description, parent, and course count.',
			array()
		);

		$pro_tools[] = $this->define_tool(
			'wp_create_course_category',
			'Create a LearnPress course category.',
			array(
				'name'        => array(
					'type'        => 'string',
					'description' => 'Category name',
					'required'    => true,
				),
				'slug'        => array(
					'type'        => 'string',
					'description' => 'Category slug (auto-generated from name if omitted)',
				),
				'description' => array(
					'type'        => 'string',
					'description' => 'Category description',
				),
				'parent'      => array(
					'type'        => 'number',
					'description' => 'Parent category ID for hierarchical categories',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_update_course_category',
			'Update a LearnPress course category.',
			array(
				'id'          => array(
					'type'        => 'number',
					'description' => 'Category term ID',
					'required'    => true,
				),
				'name'        => array(
					'type'        => 'string',
					'description' => 'Category name',
				),
				'slug'        => array(
					'type'        => 'string',
					'description' => 'Category slug',
				),
				'description' => array(
					'type'        => 'string',
					'description' => 'Category description',
				),
				'parent'      => array(
					'type'        => 'number',
					'description' => 'Parent category ID',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_delete_course_category',
			'Delete a LearnPress course category by term ID.',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Category term ID',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_lms_stats',
			'Get LearnPress LMS dashboard statistics: total courses, lessons, quizzes, enrollments, categories, and revenue summary.',
			array()
		);

		// =====================================================================
		// TP Events Tools
		// =====================================================================

		$pro_tools[] = $this->define_tool(
			'wp_list_events',
			'List ThimPress Events with date, time, location, price, and status. Supports search and status filters.',
			array(
				'per_page' => array(
					'type'        => 'number',
					'description' => 'Items per page (default 50, max 100)',
				),
				'page'     => array(
					'type'        => 'number',
					'description' => 'Page number',
				),
				'status'   => array(
					'type'        => 'string',
					'description' => 'Event status: publish, draft, pending, private, any',
				),
				'search'   => array(
					'type'        => 'string',
					'description' => 'Search term',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_get_event',
			'Get full ThimPress Event details: title, content, dates, times, location, price, quantity, registration deadline, and iframe embed.',
			array(
				'id' => array(
					'type'        => 'number',
					'description' => 'Event ID',
					'required'    => true,
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_create_event',
			'Create a ThimPress Event with title, content, dates, times, location, price, quantity, and registration deadline.',
			array(
				'title'                 => array(
					'type'        => 'string',
					'description' => 'Event title',
					'required'    => true,
				),
				'content'               => array(
					'type'        => 'string',
					'description' => 'Event description (HTML)',
				),
				'excerpt'               => array(
					'type'        => 'string',
					'description' => 'Event short description',
				),
				'status'                => array(
					'type'        => 'string',
					'description' => 'Post status: draft (default), publish, private',
					'default'     => 'draft',
				),
				'date_start'            => array(
					'type'        => 'string',
					'description' => 'Start date (YYYY-MM-DD)',
				),
				'time_start'            => array(
					'type'        => 'string',
					'description' => 'Start time (HH:MM)',
				),
				'date_end'              => array(
					'type'        => 'string',
					'description' => 'End date (YYYY-MM-DD)',
				),
				'time_end'              => array(
					'type'        => 'string',
					'description' => 'End time (HH:MM)',
				),
				'location'              => array(
					'type'        => 'string',
					'description' => 'Event location',
				),
				'price'                 => array(
					'type'        => 'string',
					'description' => 'Ticket price',
				),
				'qty'                   => array(
					'type'        => 'string',
					'description' => 'Available quantity/seats',
				),
				'registration_end_date' => array(
					'type'        => 'string',
					'description' => 'Registration deadline date (YYYY-MM-DD)',
				),
				'registration_end_time' => array(
					'type'        => 'string',
					'description' => 'Registration deadline time (HH:MM)',
				),
			)
		);

		$pro_tools[] = $this->define_tool(
			'wp_update_event',
			'Update a ThimPress Event: title, content, dates, times, location, price, quantity, registration deadline.',
			array(
				'id'                    => array(
					'type'        => 'number',
					'description' => 'Event ID',
					'required'    => true,
				),
				'title'                 => array(
					'type'        => 'string',
					'description' => 'Event title',
				),
				'content'               => array(
					'type'        => 'string',
					'description' => 'Event description (HTML)',
				),
				'excerpt'               => array(
					'type'        => 'string',
					'description' => 'Event short description',
				),
				'status'                => array(
					'type'        => 'string',
					'description' => 'Post status',
				),
				'date_start'            => array(
					'type'        => 'string',
					'description' => 'Start date (YYYY-MM-DD)',
				),
				'time_start'            => array(
					'type'        => 'string',
					'description' => 'Start time (HH:MM)',
				),
				'date_end'              => array(
					'type'        => 'string',
					'description' => 'End date (YYYY-MM-DD)',
				),
				'time_end'              => array(
					'type'        => 'string',
					'description' => 'End time (HH:MM)',
				),
				'location'              => array(
					'type'        => 'string',
					'description' => 'Event location',
				),
				'price'                 => array(
					'type'        => 'string',
					'description' => 'Ticket price',
				),
				'qty'                   => array(
					'type'        => 'string',
					'description' => 'Available quantity/seats',
				),
				'registration_end_date' => array(
					'type'        => 'string',
					'description' => 'Registration deadline date (YYYY-MM-DD)',
				),
				'registration_end_time' => array(
					'type'        => 'string',
					'description' => 'Registration deadline time (HH:MM)',
				),
			)
		);

		// Multisite tools (only exposed on multisite installations).
		if ( is_multisite() ) {
			$pro_tools[] = $this->define_tool(
				'wp_network_sites',
				'List all sites in the WordPress multisite network with their status, plugin activation state, and API key availability.',
				array(
					'per_page' => array(
						'type'        => 'number',
						'description' => 'Number of sites to return (default 50, max 200)',
						'default'     => 50,
					),
					'search'   => array(
						'type'        => 'string',
						'description' => 'Search term to filter sites by name or URL',
					),
				)
			);

			$pro_tools[] = $this->define_tool(
				'wp_network_switch',
				'Get MCP connection details for a specific site in the multisite network. Returns the MCP endpoint URL and API key status so you can connect to that site.',
				array(
					'blog_id' => array(
						'type'        => 'number',
						'description' => 'Blog ID of the site to switch to',
						'required'    => true,
					),
				)
			);

			$pro_tools[] = $this->define_tool(
				'wp_network_stats',
				'Get content statistics across all sites in the multisite network including post, page, and media counts per site.',
				array()
			);
		}

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
			'wp_set_noindex'                 => array(
				'method' => 'POST',
				'route'  => '/seo/{id}/noindex',
			),
			'wp_seo_scan'                    => array(
				'method' => 'GET',
				'route'  => '/seo/scan',
			),
			'wp_seo_report'                  => array(
				'method' => 'GET',
				'route'  => '/seo/report',
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
			'wp_build_page'                  => array(
				'method' => 'POST',
				'route'  => '/elementor/build-page',
			),
			'wp_get_elementor_globals'       => array(
				'method' => 'GET',
				'route'  => '/elementor/globals',
			),
			'wp_set_elementor_globals'       => array(
				'method' => 'POST',
				'route'  => '/elementor/globals',
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
			'wp_create_theme_template'     => array(
				'method' => 'POST',
				'route'  => '/theme-builder/templates',
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

			// WooCommerce
			'wc_status'                  => array(
				'method' => 'GET',
				'route'  => '/woocommerce/status',
			),
			'wc_list_products'           => array(
				'method' => 'GET',
				'route'  => '/woocommerce/products',
			),
			'wc_get_product'             => array(
				'method' => 'GET',
				'route'  => '/woocommerce/products/{id}',
			),
			'wc_create_product'          => array(
				'method' => 'POST',
				'route'  => '/woocommerce/products',
			),
			'wc_update_product'          => array(
				'method' => 'PUT',
				'route'  => '/woocommerce/products/{id}',
			),
			'wc_delete_product'          => array(
				'method' => 'DELETE',
				'route'  => '/woocommerce/products/{id}',
			),
			'wc_list_product_categories' => array(
				'method' => 'GET',
				'route'  => '/woocommerce/products/categories',
			),
			'wc_create_product_category' => array(
				'method' => 'POST',
				'route'  => '/woocommerce/products/categories',
			),
			'wc_update_product_category' => array(
				'method' => 'PUT',
				'route'  => '/woocommerce/products/categories/{id}',
			),
			'wc_list_product_tags'       => array(
				'method' => 'GET',
				'route'  => '/woocommerce/products/tags',
			),
			'wc_list_orders'             => array(
				'method' => 'GET',
				'route'  => '/woocommerce/orders',
			),
			'wc_get_order'               => array(
				'method' => 'GET',
				'route'  => '/woocommerce/orders/{id}',
			),
			'wc_update_order'            => array(
				'method' => 'PUT',
				'route'  => '/woocommerce/orders/{id}',
			),
			'wc_list_order_statuses'     => array(
				'method' => 'GET',
				'route'  => '/woocommerce/orders/statuses',
			),
			'wc_list_customers'          => array(
				'method' => 'GET',
				'route'  => '/woocommerce/customers',
			),
			'wc_get_customer'            => array(
				'method' => 'GET',
				'route'  => '/woocommerce/customers/{id}',
			),
			'wc_analytics'               => array(
				'method' => 'GET',
				'route'  => '/woocommerce/analytics',
			),

			// Google Indexing
			'wp_submit_to_google_index'  => array(
				'method' => 'POST',
				'route'  => '/google-indexing/submit',
			),
			'wp_google_index_status'     => array(
				'method' => 'GET',
				'route'  => '/google-indexing/status',
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

			// LearnPress LMS
			'wp_list_courses'            => array(
				'method' => 'GET',
				'route'  => '/learnpress/courses',
			),
			'wp_get_course'              => array(
				'method' => 'GET',
				'route'  => '/learnpress/courses/{id}',
			),
			'wp_create_course'           => array(
				'method' => 'POST',
				'route'  => '/learnpress/courses',
			),
			'wp_update_course'           => array(
				'method' => 'PUT',
				'route'  => '/learnpress/courses/{id}',
			),
			'wp_get_curriculum'          => array(
				'method' => 'GET',
				'route'  => '/learnpress/courses/{id}/curriculum',
			),
			'wp_set_curriculum'          => array(
				'method' => 'PUT',
				'route'  => '/learnpress/courses/{id}/curriculum',
			),
			'wp_list_lessons'            => array(
				'method' => 'GET',
				'route'  => '/learnpress/lessons',
			),
			'wp_create_lesson'           => array(
				'method' => 'POST',
				'route'  => '/learnpress/lessons',
			),
			'wp_update_lesson'           => array(
				'method' => 'PUT',
				'route'  => '/learnpress/lessons/{id}',
			),
			'wp_list_quizzes'            => array(
				'method' => 'GET',
				'route'  => '/learnpress/quizzes',
			),
			'wp_create_quiz'             => array(
				'method' => 'POST',
				'route'  => '/learnpress/quizzes',
			),
			'wp_update_quiz'             => array(
				'method' => 'PUT',
				'route'  => '/learnpress/quizzes/{id}',
			),
			'wp_get_quiz_questions'      => array(
				'method' => 'GET',
				'route'  => '/learnpress/quizzes/{id}/questions',
			),
			'wp_list_course_categories'  => array(
				'method' => 'GET',
				'route'  => '/learnpress/categories',
			),
			'wp_create_course_category'  => array(
				'method' => 'POST',
				'route'  => '/learnpress/categories',
			),
			'wp_update_course_category'  => array(
				'method' => 'PUT',
				'route'  => '/learnpress/categories/{id}',
			),
			'wp_delete_course_category'  => array(
				'method' => 'DELETE',
				'route'  => '/learnpress/categories/{id}',
			),
			'wp_lms_stats'               => array(
				'method' => 'GET',
				'route'  => '/learnpress/stats',
			),

			// Events
			'wp_list_events'             => array(
				'method' => 'GET',
				'route'  => '/events',
			),
			'wp_get_event'               => array(
				'method' => 'GET',
				'route'  => '/events/{id}',
			),
			'wp_create_event'            => array(
				'method' => 'POST',
				'route'  => '/events',
			),
			'wp_update_event'            => array(
				'method' => 'PUT',
				'route'  => '/events/{id}',
			),

			// Multisite
			'wp_network_sites'           => array(
				'method' => 'GET',
				'route'  => '/network/sites',
			),
			'wp_network_switch'          => array(
				'method' => 'POST',
				'route'  => '/network/switch',
			),
			'wp_network_stats'           => array(
				'method' => 'GET',
				'route'  => '/network/stats',
			),
		);
	}
}
