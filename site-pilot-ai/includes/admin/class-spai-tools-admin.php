<?php
/**
 * MCP Tools Admin Page
 *
 * Handles the admin UI for managing MCP tool categories.
 * Allows site owners to enable/disable entire tool categories
 * to reduce noise in the AI model's context.
 *
 * @package SitePilotAI
 * @since   1.1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin page for MCP tool category management.
 */
class Spai_Tools_Admin {

	/**
	 * Page slug.
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'site-pilot-ai-tools';

	/**
	 * Category metadata.
	 *
	 * @return array Category slug => display metadata.
	 */
	public static function get_category_meta() {
		return array(
			'content'    => array(
				'name'        => __( 'Content', 'site-pilot-ai' ),
				'description' => __( 'Posts, pages, drafts, search, clone, bulk create', 'site-pilot-ai' ),
				'icon'        => 'dashicons-admin-post',
			),
			'media'      => array(
				'name'        => __( 'Media', 'site-pilot-ai' ),
				'description' => __( 'Upload files, import from URL, media library, screenshots', 'site-pilot-ai' ),
				'icon'        => 'dashicons-admin-media',
			),
			'elementor'  => array(
				'name'        => __( 'Elementor', 'site-pilot-ai' ),
				'description' => __( 'Get/set page data, templates, globals, widgets, CSS', 'site-pilot-ai' ),
				'icon'        => 'dashicons-editor-kitchensink',
			),
			'seo'        => array(
				'name'        => __( 'SEO', 'site-pilot-ai' ),
				'description' => __( 'Meta titles, descriptions, analysis, noindex', 'site-pilot-ai' ),
				'icon'        => 'dashicons-search',
			),
			'forms'      => array(
				'name'        => __( 'Forms', 'site-pilot-ai' ),
				'description' => __( 'List forms, view entries, plugin detection', 'site-pilot-ai' ),
				'icon'        => 'dashicons-feedback',
			),
			'gutenberg'  => array(
				'name'        => __( 'Gutenberg', 'site-pilot-ai' ),
				'description' => __( 'Block editor data, block types, patterns', 'site-pilot-ai' ),
				'icon'        => 'dashicons-block-default',
			),
			'taxonomy'   => array(
				'name'        => __( 'Taxonomy', 'site-pilot-ai' ),
				'description' => __( 'Categories, tags, custom taxonomy terms', 'site-pilot-ai' ),
				'icon'        => 'dashicons-tag',
			),
			'site'       => array(
				'name'        => __( 'Site', 'site-pilot-ai' ),
				'description' => __( 'Site info, plugins, theme, menus, options, health', 'site-pilot-ai' ),
				'icon'        => 'dashicons-admin-site-alt3',
			),
			'webhooks'   => array(
				'name'        => __( 'Webhooks', 'site-pilot-ai' ),
				'description' => __( 'Create, update, delete, test webhook subscriptions', 'site-pilot-ai' ),
				'icon'        => 'dashicons-rest-api',
			),
			'admin'      => array(
				'name'        => __( 'Admin', 'site-pilot-ai' ),
				'description' => __( 'API keys, rate limits, feedback', 'site-pilot-ai' ),
				'icon'        => 'dashicons-admin-tools',
			),
			'ai'         => array(
				'name'        => __( 'AI', 'site-pilot-ai' ),
				'description' => __( 'Stock photos, image generation, alt text, TTS', 'site-pilot-ai' ),
				'icon'        => 'dashicons-lightbulb',
			),
		);
	}

	/**
	 * Render the admin page.
	 */
	public function render() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'site-pilot-ai' ) );
		}

		$disabled_categories = get_option( 'spai_disabled_tool_categories', array() );
		if ( ! is_array( $disabled_categories ) ) {
			$disabled_categories = array();
		}

		$category_meta = self::get_category_meta();

		// Count tools per category.
		$tool_counts = $this->count_tools_per_category();

		include SPAI_PLUGIN_DIR . 'admin/partials/spai-tools-display.php';
	}

	/**
	 * Enqueue admin assets for tools page.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_assets( $hook ) {
		if ( 'site-pilot-ai_page_' . self::PAGE_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'spai-admin',
			SPAI_PLUGIN_URL . 'admin/css/spai-admin.css',
			array(),
			SPAI_VERSION
		);
	}

	/**
	 * Count tools per category from all registries.
	 *
	 * @return array Category slug => tool count.
	 */
	private function count_tools_per_category() {
		$counts = array();

		// Get all tools through the MCP controller.
		$mcp = new Spai_REST_MCP();
		$all_tools = method_exists( $mcp, 'get_introspection_data' )
			? $mcp->get_introspection_data()
			: array();

		$tools = isset( $all_tools['tools'] ) ? $all_tools['tools'] : array();

		foreach ( $tools as $tool ) {
			$cat = isset( $tool['annotations']['category'] ) ? $tool['annotations']['category'] : 'site';
			if ( ! isset( $counts[ $cat ] ) ) {
				$counts[ $cat ] = 0;
			}
			++$counts[ $cat ];
		}

		return $counts;
	}

	/**
	 * AJAX: Toggle tool category.
	 */
	public function ajax_toggle_category() {
		check_ajax_referer( 'spai_tools_nonce', 'nonce' );

		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		$category = isset( $_POST['category'] ) ? sanitize_key( wp_unslash( $_POST['category'] ) ) : '';
		$enabled  = isset( $_POST['enabled'] ) ? sanitize_text_field( wp_unslash( $_POST['enabled'] ) ) : '1';

		if ( empty( $category ) ) {
			wp_send_json_error( array( 'message' => __( 'Category is required.', 'site-pilot-ai' ) ) );
		}

		// Validate category slug.
		$valid_categories = array_keys( self::get_category_meta() );
		if ( ! in_array( $category, $valid_categories, true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid category.', 'site-pilot-ai' ) ) );
		}

		$disabled = get_option( 'spai_disabled_tool_categories', array() );
		if ( ! is_array( $disabled ) ) {
			$disabled = array();
		}

		if ( '1' === $enabled ) {
			// Enable: remove from disabled list.
			$disabled = array_values( array_diff( $disabled, array( $category ) ) );
		} else {
			// Disable: add to disabled list.
			if ( ! in_array( $category, $disabled, true ) ) {
				$disabled[] = $category;
			}
		}

		update_option( 'spai_disabled_tool_categories', $disabled );

		wp_send_json_success( array(
			'message'  => '1' === $enabled
				? sprintf(
					/* translators: %s: category name */
					__( '%s tools enabled.', 'site-pilot-ai' ),
					$category
				)
				: sprintf(
					/* translators: %s: category name */
					__( '%s tools disabled.', 'site-pilot-ai' ),
					$category
				),
			'disabled' => $disabled,
		) );
	}
}
