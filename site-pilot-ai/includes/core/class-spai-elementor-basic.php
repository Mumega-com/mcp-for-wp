<?php
/**
 * Basic Elementor handler (FREE tier)
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle basic Elementor operations.
 *
 * FREE tier includes:
 * - Get Elementor data for a page
 * - Set Elementor data for a page
 * - Check Elementor status
 *
 * PRO tier includes (in separate plugin):
 * - Templates
 * - Landing pages
 * - Widgets
 * - Globals
 * - Clone pages
 */
class Spai_Elementor_Basic {

	/**
	 * Check if Elementor is active.
	 *
	 * @return bool True if Elementor is active.
	 */
	public function is_active() {
		return defined( 'ELEMENTOR_VERSION' );
	}

	/**
	 * Check if Elementor Pro is active.
	 *
	 * @return bool True if Elementor Pro is active.
	 */
	public function is_pro_active() {
		return defined( 'ELEMENTOR_PRO_VERSION' );
	}

	/**
	 * Get Elementor status.
	 *
	 * @return array Elementor status.
	 */
	public function get_status() {
		return array(
			'active'  => $this->is_active(),
			'pro'     => $this->is_pro_active(),
			'version' => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : null,
			'pro_version' => defined( 'ELEMENTOR_PRO_VERSION' ) ? ELEMENTOR_PRO_VERSION : null,
		);
	}

	/**
	 * Validate and return a post if it's a supported Elementor type.
	 *
	 * @param int $post_id Post ID.
	 * @return WP_Post|WP_Error Post object or error.
	 */
	public function validate_post( $post_id ) {
		$post    = get_post( absint( $post_id ) );
		$allowed = array( 'page', 'post', 'elementor_library' );

		if ( ! $post || ! in_array( $post->post_type, $allowed, true ) ) {
			return new WP_Error(
				'not_found',
				__( 'Post not found or unsupported type.', 'site-pilot-ai' ),
				array( 'status' => 404 )
			);
		}

		return $post;
	}

	/**
	 * Get Elementor data for a page.
	 *
	 * @param int $page_id Page ID.
	 * @return array|WP_Error Elementor data or error.
	 */
	public function get_elementor_data( $page_id ) {
		if ( ! $this->is_active() ) {
			return new WP_Error(
				'elementor_not_active',
				__( 'Elementor is not installed or active.', 'site-pilot-ai' ),
				array( 'status' => 400 )
			);
		}

		$page = $this->validate_post( $page_id );
		if ( is_wp_error( $page ) ) {
			return $page;
		}

		$elementor_data = get_post_meta( $page_id, '_elementor_data', true );
		$edit_mode = get_post_meta( $page_id, '_elementor_edit_mode', true );
		$template_type = get_post_meta( $page_id, '_elementor_template_type', true );

		$page_settings = get_post_meta( $page_id, '_elementor_page_settings', true );

		return array(
			'page_id'        => $page_id,
			'title'          => $page->post_title,
			'has_elementor'  => ! empty( $elementor_data ),
			'edit_mode'      => $edit_mode ?: 'classic',
			'template_type'  => $template_type ?: null,
			'elementor_data' => $elementor_data ? json_decode( $elementor_data, true ) : null,
			'elementor_json' => $elementor_data ?: null,
			'page_settings'  => $page_settings ? ( is_array( $page_settings ) ? $page_settings : json_decode( $page_settings, true ) ) : null,
			'edit_url'       => admin_url( "post.php?post={$page_id}&action=elementor" ),
		);
	}

	/**
	 * Get a lightweight structural summary of Elementor data for a page.
	 *
	 * Returns section/container structure with widget types and key display
	 * settings, typically <1K tokens vs 64K+ for full data.
	 *
	 * @param int $page_id Page ID.
	 * @return array|WP_Error Summary or error.
	 */
	public function get_elementor_summary( $page_id ) {
		if ( ! $this->is_active() ) {
			return new WP_Error(
				'elementor_not_active',
				__( 'Elementor is not installed or active.', 'site-pilot-ai' ),
				array( 'status' => 400 )
			);
		}

		$page = $this->validate_post( $page_id );
		if ( is_wp_error( $page ) ) {
			return $page;
		}

		$elementor_data = get_post_meta( $page_id, '_elementor_data', true );
		if ( empty( $elementor_data ) ) {
			return array(
				'page_id'       => $page_id,
				'title'         => $page->post_title,
				'has_elementor' => false,
				'sections'      => array(),
			);
		}

		$elements = json_decode( $elementor_data, true );
		if ( ! is_array( $elements ) ) {
			return array(
				'page_id'       => $page_id,
				'title'         => $page->post_title,
				'has_elementor' => false,
				'sections'      => array(),
			);
		}

		$sections      = array();
		$widget_count  = 0;
		$section_count = 0;

		foreach ( $elements as $element ) {
			$section_summary = $this->summarize_element( $element, $widget_count );
			if ( $section_summary ) {
				$sections[] = $section_summary;
				$section_count++;
			}
		}

		return array(
			'page_id'       => $page_id,
			'title'         => $page->post_title,
			'has_elementor' => true,
			'section_count' => $section_count,
			'widget_count'  => $widget_count,
			'sections'      => $sections,
		);
	}

	/**
	 * Summarize a single Elementor element recursively.
	 *
	 * @param array $element      The element.
	 * @param int   $widget_count Running widget count (by reference).
	 * @return array|null Summary or null.
	 */
	private function summarize_element( $element, &$widget_count ) {
		if ( ! is_array( $element ) || empty( $element['elType'] ) ) {
			return null;
		}

		$summary = array(
			'type' => $element['elType'],
		);

		if ( ! empty( $element['widgetType'] ) ) {
			$summary['widget'] = $element['widgetType'];
			$widget_count++;

			// Extract key display settings based on widget type.
			$settings = isset( $element['settings'] ) ? $element['settings'] : array();
			$key_settings = array();

			$display_keys = $this->get_widget_display_keys( $element['widgetType'] );
			foreach ( $display_keys as $key ) {
				if ( isset( $settings[ $key ] ) && '' !== $settings[ $key ] ) {
					$value = $settings[ $key ];
					// Truncate long strings to keep summary compact.
					if ( is_string( $value ) && strlen( $value ) > 100 ) {
						$value = substr( $value, 0, 100 ) . '...';
					}
					$key_settings[ $key ] = $value;
				}
			}

			if ( ! empty( $key_settings ) ) {
				$summary['settings'] = $key_settings;
			}
		}

		// Recurse into child elements.
		if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
			$children = array();
			foreach ( $element['elements'] as $child ) {
				$child_summary = $this->summarize_element( $child, $widget_count );
				if ( $child_summary ) {
					$children[] = $child_summary;
				}
			}
			if ( ! empty( $children ) ) {
				$summary['children'] = $children;
			}
		}

		return $summary;
	}

	/**
	 * Get key display setting names for a widget type.
	 *
	 * @param string $widget_type Widget type name.
	 * @return array Setting key names.
	 */
	private function get_widget_display_keys( $widget_type ) {
		$map = array(
			'heading'       => array( 'title', 'header_size', 'align' ),
			'text-editor'   => array( 'editor' ),
			'image'         => array( 'image' ),
			'button'        => array( 'text', 'link' ),
			'icon-box'      => array( 'title_text', 'description_text', 'selected_icon' ),
			'image-box'     => array( 'title_text', 'description_text' ),
			'icon-list'     => array(),
			'counter'       => array( 'starting_number', 'ending_number', 'title' ),
			'progress-bar'  => array( 'title', 'percent' ),
			'testimonial'   => array( 'testimonial_name', 'testimonial_job', 'testimonial_content' ),
			'tabs'          => array(),
			'accordion'     => array(),
			'toggle'        => array(),
			'social-icons'  => array(),
			'alert'         => array( 'alert_title', 'alert_description' ),
			'html'          => array(),
			'video'         => array( 'youtube_url', 'vimeo_url' ),
			'google-maps'   => array( 'address' ),
			'form'          => array( 'form_name' ),
			'nav-menu'      => array( 'menu' ),
			'sitemap'       => array(),
			'flip-box'      => array( 'title_text_a', 'title_text_b' ),
			'call-to-action' => array( 'title', 'description', 'button' ),
			'price-table'   => array( 'heading', 'sub_heading', 'price' ),
			'price-list'    => array(),
			'countdown'     => array( 'due_date' ),
			'share-buttons' => array(),
			'blockquote'    => array( 'blockquote_content' ),
			'template'      => array( 'template_id' ),
		);

		return isset( $map[ $widget_type ] ) ? $map[ $widget_type ] : array( 'title', 'text', 'heading' );
	}

	/**
	 * Set Elementor data for a page.
	 *
	 * @param int   $page_id Page ID.
	 * @param array $data    Elementor data.
	 * @return array|WP_Error Result or error.
	 */
	public function set_elementor_data( $page_id, $data ) {
		if ( ! $this->is_active() ) {
			return new WP_Error(
				'elementor_not_active',
				__( 'Elementor is not installed or active.', 'site-pilot-ai' ),
				array( 'status' => 400 )
			);
		}

		$page = $this->validate_post( $page_id );
		if ( is_wp_error( $page ) ) {
			return $page;
		}

		// Validate and encode data
		$elementor_json = null;

		if ( isset( $data['elementor_data'] ) ) {
			// If array, validate structure and encode to JSON
			if ( is_array( $data['elementor_data'] ) ) {
				if ( ! $this->is_valid_elementor_structure( $data['elementor_data'] ) ) {
					return new WP_Error(
						'invalid_structure',
						__( 'Elementor data must be an array of element objects.', 'site-pilot-ai' ),
						array( 'status' => 400 )
					);
				}
				$elementor_json = wp_json_encode( $data['elementor_data'] );
			} else {
				// Validate JSON string
				$decoded = json_decode( $data['elementor_data'], true );
				if ( null === $decoded && json_last_error() !== JSON_ERROR_NONE ) {
					return new WP_Error(
						'invalid_json',
						__( 'Invalid Elementor JSON data.', 'site-pilot-ai' ),
						array( 'status' => 400 )
					);
				}
				if ( ! is_array( $decoded ) ) {
					return new WP_Error(
						'invalid_structure',
						__( 'Elementor data must decode to an array.', 'site-pilot-ai' ),
						array( 'status' => 400 )
					);
				}
				$elementor_json = $data['elementor_data'];
			}
		} elseif ( isset( $data['elementor_json'] ) ) {
			// Direct JSON string
			$decoded = json_decode( $data['elementor_json'], true );
			if ( null === $decoded && json_last_error() !== JSON_ERROR_NONE ) {
				return new WP_Error(
					'invalid_json',
					__( 'Invalid Elementor JSON data.', 'site-pilot-ai' ),
					array( 'status' => 400 )
				);
			}
			if ( ! is_array( $decoded ) ) {
				return new WP_Error(
					'invalid_structure',
					__( 'Elementor data must decode to an array.', 'site-pilot-ai' ),
					array( 'status' => 400 )
				);
			}
			$elementor_json = $data['elementor_json'];
		}

		// Validate JSON size and nesting depth (prevent DoS).
		if ( ! empty( $elementor_json ) && class_exists( 'Spai_Security' ) ) {
			$size_check = Spai_Security::validate_json_payload( $elementor_json, 5 * 1024 * 1024, 30 );
			if ( is_wp_error( $size_check ) ) {
				return $size_check;
			}
		}

		if ( empty( $elementor_json ) ) {
			return new WP_Error(
				'no_data',
				__( 'No Elementor data provided.', 'site-pilot-ai' ),
				array( 'status' => 400 )
			);
		}

		$save_debug    = array();
		$elementor_ok  = class_exists( '\Elementor\Plugin' );

		// --- 1. Set ALL required Elementor meta keys ---

		// Page template.
		$current_template = get_post_meta( $page_id, '_wp_page_template', true );
		if ( ! $current_template || 'default' === $current_template ) {
			update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );
		}

		// Edit mode must be 'builder'.
		update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );

		// Template type — required for frontend rendering.
		$template_type = get_post_meta( $page_id, '_elementor_template_type', true );
		if ( empty( $template_type ) ) {
			$post_type = get_post_type( $page_id );
			$type_value = ( 'elementor_library' === $post_type ) ? 'section' : 'wp-page';
			update_post_meta( $page_id, '_elementor_template_type', $type_value );
			$save_debug['set_template_type'] = $type_value;
		}

		// Elementor version — prevents unnecessary migrations.
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			update_post_meta( $page_id, '_elementor_version', ELEMENTOR_VERSION );
			$save_debug['elementor_version'] = ELEMENTOR_VERSION;
		}

		// Pro version — required for Pro widget rendering.
		if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			update_post_meta( $page_id, '_elementor_pro_version', ELEMENTOR_PRO_VERSION );
			$save_debug['elementor_pro_version'] = ELEMENTOR_PRO_VERSION;
		}

		// --- 2. Write element data via update_post_meta (always reliable) ---

		update_post_meta( $page_id, '_elementor_data', wp_slash( $elementor_json ) );
		$save_debug['meta_written'] = true;

		// Verify data was stored correctly.
		$stored = get_post_meta( $page_id, '_elementor_data', true );
		$stored_decoded = json_decode( $stored, true );
		$input_decoded  = json_decode( $elementor_json, true );
		$save_debug['meta_verified'] = (
			is_array( $stored_decoded ) &&
			is_array( $input_decoded ) &&
			count( $stored_decoded ) === count( $input_decoded )
		);

		// --- 3. Validate and fix element tree ---

		$validation = $this->validate_and_fix_elements( $elementor_json );
		if ( ! empty( $validation['fixes'] ) || ! empty( $validation['warnings'] ) ) {
			$save_debug['validation_fixes']    = $validation['fixes'];
			$save_debug['validation_warnings'] = $validation['warnings'];
			// Re-write fixed data if any auto-fixes were applied.
			if ( ! empty( $validation['fixes'] ) ) {
				update_post_meta( $page_id, '_elementor_data', wp_slash( $elementor_json ) );
			}
		}

		// --- 4. Clear Elementor caches and regenerate CSS ---

		$save_method = 'meta_direct';

		if ( $elementor_ok ) {
			// Clear file cache.
			if ( ! empty( \Elementor\Plugin::$instance->files_manager ) ) {
				\Elementor\Plugin::$instance->files_manager->clear_cache();
				$save_debug['cache_cleared'] = true;
			}

			// Delete compiled CSS meta to force regeneration on next page load.
			delete_post_meta( $page_id, '_elementor_css' );

			// Regenerate CSS for this page.
			if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
				$css_file = \Elementor\Core\Files\CSS\Post::create( $page_id );
				$css_file->update();
				$save_debug['css_regenerated'] = true;
			}
		}

		// --- 5. Page-level settings (custom CSS, etc.) (#81) ---

		if ( ! empty( $data['page_settings'] ) && is_array( $data['page_settings'] ) ) {
			$allowed_keys = array( 'custom_css', 'background_background', 'background_color', 'padding', 'hide_title' );
			$page_settings = get_post_meta( $page_id, '_elementor_page_settings', true );
			if ( ! is_array( $page_settings ) ) {
				$page_settings = array();
			}
			foreach ( $data['page_settings'] as $key => $value ) {
				if ( in_array( $key, $allowed_keys, true ) ) {
					$page_settings[ $key ] = $value;
				}
			}
			update_post_meta( $page_id, '_elementor_page_settings', $page_settings );
			$save_debug['page_settings_updated'] = array_keys( $data['page_settings'] );
		}

		// --- 6. Purge page caches (#89) ---

		$this->purge_page_cache( $page_id );
		$save_debug['page_cache_purged'] = true;

		// Build top-level warnings from validation results.
		$all_warnings = array();
		if ( ! empty( $save_debug['validation_warnings'] ) ) {
			$all_warnings = array_merge( $all_warnings, $save_debug['validation_warnings'] );
		}
		if ( ! empty( $save_debug['validation_fixes'] ) ) {
			$all_warnings = array_merge( $all_warnings, $save_debug['validation_fixes'] );
		}

		$result = array(
			'success'     => true,
			'page_id'     => (string) $page_id,
			'message'     => __( 'Elementor data updated.', 'site-pilot-ai' ),
			'save_method' => $save_method,
			'debug'       => $save_debug,
			'edit_url'    => admin_url( "post.php?post={$page_id}&action=elementor" ),
		);

		if ( ! empty( $all_warnings ) ) {
			$result['warnings'] = $all_warnings;
		}

		return $result;
	}

	/**
	 * Create a simple page with Elementor enabled.
	 *
	 * @param array $data Page data.
	 * @return array|WP_Error Created page data or error.
	 */
	public function create_elementor_page( $data ) {
		if ( ! $this->is_active() ) {
			return new WP_Error(
				'elementor_not_active',
				__( 'Elementor is not installed or active.', 'site-pilot-ai' ),
				array( 'status' => 400 )
			);
		}

		// Create page
		$page_data = array(
			'post_type'    => 'page',
			'post_title'   => isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : __( 'New Page', 'site-pilot-ai' ),
			'post_status'  => isset( $data['status'] ) ? sanitize_key( $data['status'] ) : 'draft',
			'post_content' => '',
		);

		$page_id = wp_insert_post( $page_data, true );

		if ( is_wp_error( $page_id ) ) {
			return $page_id;
		}

		// Enable Elementor with all required meta keys (#88).
		update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );
		update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			update_post_meta( $page_id, '_elementor_version', ELEMENTOR_VERSION );
		}

		// Set initial Elementor data if provided
		if ( ! empty( $data['elementor_data'] ) || ! empty( $data['elementor_json'] ) ) {
			$result = $this->set_elementor_data( $page_id, $data );
			if ( is_wp_error( $result ) ) {
				// Clean up page on error
				wp_delete_post( $page_id, true );
				return $result;
			}
		} else {
			// Set empty Elementor data
			update_post_meta( $page_id, '_elementor_data', '[]' );
		}

		return array(
			'success'  => true,
			'page_id'  => $page_id,
			'title'    => $page_data['post_title'],
			'status'   => $page_data['post_status'],
			'url'      => get_permalink( $page_id ),
			'edit_url' => admin_url( "post.php?post={$page_id}&action=elementor" ),
		);
	}

	/**
	 * Check if data has valid Elementor structure (array of elements).
	 *
	 * @param mixed $data Data to validate.
	 * @return bool True if valid structure.
	 */
	private function is_valid_elementor_structure( $data ) {
		// Must be an array (can be empty for blank pages).
		if ( ! is_array( $data ) ) {
			return false;
		}

		// Empty array is valid (blank page).
		if ( empty( $data ) ) {
			return true;
		}

		// If indexed array, first element should be an array (element object).
		if ( isset( $data[0] ) && ! is_array( $data[0] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Known control key renames per widget type.
	 *
	 * Maps commonly-used wrong key names to their correct Elementor equivalents.
	 * These are auto-fixed during validation to prevent renderer crashes.
	 *
	 * @var array<string, array<string, string>>
	 */
	private static $control_renames = array(
		'icon-box' => array(
			'title_size' => 'title_typography_font_size',
		),
		'flip-box' => array(
			'front_title_text'       => 'title_text_a',
			'front_description_text' => 'description_text_a',
			'back_title_text'        => 'title_text_b',
			'back_description_text'  => 'description_text_b',
			'front_background_color' => 'background_color_a',
			'back_background_color'  => 'background_color_b',
		),
	);

	/**
	 * Valid top-level element types.
	 *
	 * @var array
	 */
	private static $valid_el_types = array( 'section', 'column', 'widget', 'container' );

	/**
	 * Valid nesting rules: parent elType => allowed child elTypes.
	 *
	 * @var array<string, array<string>>
	 */
	private static $nesting_rules = array(
		'section'   => array( 'column' ),
		'column'    => array( 'widget', 'section', 'container' ),
		'container' => array( 'widget', 'container' ),
	);

	/**
	 * Known Elementor free widget types.
	 *
	 * @var array
	 */
	private static $known_free_widgets = array(
		'heading', 'text-editor', 'image', 'video', 'button', 'divider',
		'spacer', 'google_maps', 'icon', 'image-box', 'icon-box', 'icon-list',
		'counter', 'progress', 'testimonial', 'tabs', 'accordion', 'toggle',
		'social-icons', 'alert', 'html', 'shortcode', 'menu-anchor',
		'sidebar', 'read-more', 'star-rating', 'basic-gallery', 'image-carousel',
		'wp-widget-pages', 'wp-widget-calendar', 'wp-widget-archives',
		'wp-widget-media_audio', 'wp-widget-media_image', 'wp-widget-media_gallery',
		'wp-widget-media_video', 'wp-widget-meta', 'wp-widget-search',
		'wp-widget-text', 'wp-widget-categories', 'wp-widget-recent-posts',
		'wp-widget-recent-comments', 'wp-widget-rss', 'wp-widget-tag_cloud',
		'wp-widget-nav_menu', 'wp-widget-custom_html', 'inner-section',
		'common', 'container', 'text-path', 'nested-tabs', 'nested-accordion',
		'nested-carousel', 'link-in-bio', 'off-canvas',
	);

	/**
	 * Known Elementor Pro widget types.
	 *
	 * @var array
	 */
	private static $known_pro_widgets = array(
		'posts', 'portfolio', 'gallery', 'form', 'login', 'slides',
		'nav-menu', 'animated-headline', 'price-list', 'price-table',
		'flip-box', 'call-to-action', 'media-carousel', 'testimonial-carousel',
		'reviews', 'table-of-contents', 'countdown', 'share-buttons',
		'blockquote', 'template', 'facebook-button', 'facebook-comments',
		'facebook-embed', 'facebook-page', 'search-form', 'post-navigation',
		'author-box', 'post-comments', 'post-info', 'post-title',
		'post-excerpt', 'post-content', 'post-featured-image', 'archive-title',
		'archive-posts', 'sitemap', 'lottie', 'hotspot', 'paypal-button',
		'stripe-button', 'progress-tracker', 'code-highlight',
		'video-playlist', 'mega-menu', 'loop-grid', 'loop-carousel',
		'taxonomy-filter',
		// Theme Builder widgets (theme- prefix).
		'theme-post-title', 'theme-post-content', 'theme-post-excerpt',
		'theme-post-featured-image', 'theme-post-info', 'theme-post-navigation',
		'theme-archive-title', 'theme-archive-posts', 'theme-site-logo',
		'theme-site-title', 'theme-page-title', 'theme-builder-comments',
		'theme-search-form', 'theme-author-box',
	);

	/**
	 * Get the list of registered widget type names from Elementor.
	 *
	 * Falls back to a hardcoded list if the widget manager is not available.
	 *
	 * @return array Widget type names.
	 */
	private function get_registered_widgets() {
		// Start with live registry if available.
		$widgets = array();
		if ( class_exists( '\Elementor\Plugin' ) && ! empty( \Elementor\Plugin::$instance->widgets_manager ) ) {
			$manager = \Elementor\Plugin::$instance->widgets_manager;
			if ( method_exists( $manager, 'get_widget_types' ) ) {
				$types = $manager->get_widget_types();
				if ( ! empty( $types ) && is_array( $types ) ) {
					$widgets = array_keys( $types );
				}
			}
		}

		// Always merge both hardcoded lists so known widgets never trigger false warnings.
		// Pro widgets are harmless to allow — they simply won't render without Pro.
		$widgets = array_unique( array_merge( $widgets, self::$known_free_widgets, self::$known_pro_widgets ) );
		return $widgets;
	}

	/**
	 * Find closest match for a widget type using Levenshtein distance.
	 *
	 * @param string $input      Unknown widget type.
	 * @param array  $candidates Known widget types.
	 * @return string|null Closest match or null if none close enough.
	 */
	private function find_closest_widget( $input, $candidates ) {
		$best_match    = null;
		$best_distance = PHP_INT_MAX;

		foreach ( $candidates as $candidate ) {
			$distance = levenshtein( $input, $candidate );
			if ( $distance < $best_distance && $distance <= 3 ) {
				$best_distance = $distance;
				$best_match    = $candidate;
			}
		}

		return $best_match;
	}

	/**
	 * Validate and fix element tree.
	 *
	 * Performs 5 validation passes:
	 * 1. Auto-generate missing element IDs
	 * 2. Validate widget types against registered widgets
	 * 3. Rename known wrong control keys
	 * 4. Validate element structure and nesting
	 * 5. Flag suspicious/unknown control keys
	 *
	 * Modifies $elementor_json in-place (reference).
	 *
	 * @param string &$elementor_json JSON string (modified in-place).
	 * @return array Associative array with 'warnings' and 'fixes' arrays.
	 */
	private function validate_and_fix_elements( &$elementor_json ) {
		$elements = json_decode( $elementor_json, true );
		if ( ! is_array( $elements ) ) {
			return array(
				'warnings' => array( 'Elementor data is not a valid array.' ),
				'fixes'    => array(),
			);
		}

		$warnings          = array();
		$fixes             = array();
		$changed           = false;
		$registered        = $this->get_registered_widgets();
		$registered_lookup = array_flip( $registered );

		/**
		 * Walk a single element recursively.
		 *
		 * @param array  &$el   Element (modified in-place).
		 * @param string $path  Human-readable path for warnings.
		 * @param string $parent_type Parent elType for nesting validation.
		 */
		$walk = function ( &$el, $path = '', $parent_type = '' ) use (
			&$walk, &$warnings, &$fixes, &$changed,
			$registered, $registered_lookup
		) {
			$el_type     = isset( $el['elType'] ) ? $el['elType'] : '';
			$widget_type = isset( $el['widgetType'] ) ? $el['widgetType'] : '';

			// --- 1. Auto-generate missing IDs ---
			if ( empty( $el['id'] ) ) {
				$el['id'] = $this->generate_element_id();
				$fixes[]  = "{$path}: auto-generated missing ID";
				$changed  = true;
			}

			// --- 2. Validate elType ---
			if ( '' === $el_type ) {
				$warnings[] = "{$path}: missing elType";
			} elseif ( ! in_array( $el_type, self::$valid_el_types, true ) ) {
				$warnings[] = "{$path}: unknown elType '{$el_type}'";
			}

			// --- 3. Validate nesting ---
			if ( '' !== $parent_type && '' !== $el_type && isset( self::$nesting_rules[ $parent_type ] ) ) {
				if ( ! in_array( $el_type, self::$nesting_rules[ $parent_type ], true ) ) {
					$warnings[] = "{$path}: '{$el_type}' should not be nested inside '{$parent_type}'";
				}
			}

			// --- 4. Validate widget type ---
			if ( 'widget' === $el_type && '' !== $widget_type ) {
				if ( ! isset( $registered_lookup[ $widget_type ] ) ) {
					$suggestion = $this->find_closest_widget( $widget_type, $registered );
					if ( $suggestion ) {
						$warnings[] = "{$path}: unknown widget '{$widget_type}' (did you mean '{$suggestion}'?)";
					} else {
						$warnings[] = "{$path}: unknown widget type '{$widget_type}'";
					}
				}
			} elseif ( 'widget' === $el_type && '' === $widget_type ) {
				$warnings[] = "{$path}: widget element missing widgetType";
			}

			// --- 5. Rename known wrong control keys ---
			if ( '' !== $widget_type && isset( self::$control_renames[ $widget_type ] ) ) {
				$renames = self::$control_renames[ $widget_type ];
				foreach ( $renames as $old_key => $new_key ) {
					if ( isset( $el['settings'][ $old_key ] ) && ! isset( $el['settings'][ $new_key ] ) ) {
						$el['settings'][ $new_key ] = $el['settings'][ $old_key ];
						unset( $el['settings'][ $old_key ] );
						$fixes[] = "{$path}: renamed '{$old_key}' -> '{$new_key}'";
						$changed = true;
					}
				}
			}

			// --- 6. Validate elements array ---
			if ( isset( $el['elements'] ) && ! is_array( $el['elements'] ) ) {
				$warnings[]    = "{$path}: 'elements' must be an array";
				$el['elements'] = array();
				$changed        = true;
			}

			// Recurse into children.
			if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
				foreach ( $el['elements'] as $idx => &$child ) {
					$child_path = $path . '.' . ( isset( $child['elType'] ) ? $child['elType'] : 'element' ) . "[{$idx}]";
					$walk( $child, $child_path, $el_type );
				}
				unset( $child );
			}
		};

		// Walk each top-level element.
		foreach ( $elements as $idx => &$el ) {
			$top_type = isset( $el['elType'] ) ? $el['elType'] : 'element';
			$path     = "{$top_type}[{$idx}]";
			$walk( $el, $path, '' );
		}
		unset( $el );

		// Re-encode if any fixes were applied.
		if ( $changed ) {
			$elementor_json = wp_json_encode( $elements );
		}

		return array(
			'warnings' => $warnings,
			'fixes'    => $fixes,
		);
	}

	/**
	 * Generate a random 8-character element ID matching Elementor's format.
	 *
	 * @return string Random ID.
	 */
	private function generate_element_id() {
		$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$id    = '';
		for ( $i = 0; $i < 8; $i++ ) {
			$id .= $chars[ wp_rand( 0, 35 ) ];
		}
		return $id;
	}

	/**
	 * Backward-compatible wrapper for the old sanitize method.
	 *
	 * @param string &$elementor_json JSON string (modified in-place).
	 * @return array Warnings about renamed keys.
	 * @deprecated Use validate_and_fix_elements() instead.
	 */
	private function sanitize_element_settings( &$elementor_json ) {
		$result = $this->validate_and_fix_elements( $elementor_json );
		return array_merge( $result['warnings'], $result['fixes'] );
	}

	/**
	 * Purge page cache across common WordPress caching plugins.
	 *
	 * @param int $page_id Post ID to purge.
	 */
	private function purge_page_cache( $page_id ) {
		// WordPress core.
		clean_post_cache( $page_id );

		$url = get_permalink( $page_id );

		// SiteGround SG Optimizer.
		if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
			sg_cachepress_purge_cache( $url );
		}

		// WP Super Cache.
		if ( function_exists( 'wp_cache_post_change' ) ) {
			wp_cache_post_change( $page_id );
		}

		// W3 Total Cache.
		if ( function_exists( 'w3tc_flush_post' ) ) {
			w3tc_flush_post( $page_id );
		}

		// WP Rocket.
		if ( function_exists( 'rocket_clean_post' ) ) {
			rocket_clean_post( $page_id );
		}

		// LiteSpeed Cache.
		if ( method_exists( 'LiteSpeed_Cache_API', 'purge_post' ) ) {
			LiteSpeed_Cache_API::purge_post( $page_id );
		} elseif ( class_exists( 'LiteSpeed\Purge' ) && method_exists( 'LiteSpeed\Purge', 'purge_post' ) ) {
			LiteSpeed\Purge::purge_post( $page_id );
		}

		// WP Fastest Cache.
		if ( function_exists( 'wpfc_clear_post_cache_by_id' ) ) {
			wpfc_clear_post_cache_by_id( $page_id );
		}

		// Autoptimize.
		if ( class_exists( 'autoptimizeCache' ) && method_exists( 'autoptimizeCache', 'clearall' ) ) {
			autoptimizeCache::clearall();
		}
	}

	/**
	 * Regenerate Elementor CSS for a specific page or the entire site.
	 *
	 * @param int|null $page_id Page ID, or null for full site regeneration.
	 * @return array|WP_Error Result or error.
	 */
	public function regenerate_css( $page_id = null ) {
		if ( ! $this->is_active() ) {
			return new WP_Error(
				'elementor_not_active',
				__( 'Elementor is not installed or active.', 'site-pilot-ai' ),
				array( 'status' => 400 )
			);
		}

		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return new WP_Error(
				'elementor_not_loaded',
				__( 'Elementor plugin class not available.', 'site-pilot-ai' ),
				array( 'status' => 500 )
			);
		}

		$plugin = \Elementor\Plugin::$instance;

		if ( $page_id ) {
			$page_id = absint( $page_id );
			$page = get_post( $page_id );

			if ( ! $page ) {
				return new WP_Error(
					'not_found',
					__( 'Page not found.', 'site-pilot-ai' ),
					array( 'status' => 404 )
				);
			}

			// Regenerate CSS for specific post.
			if ( method_exists( $plugin, 'documents' ) ) {
				$document = $plugin->documents->get( $page_id );
				if ( $document ) {
					// Delete existing CSS file to force regeneration.
					$css_file = \Elementor\Core\Files\CSS\Post::create( $page_id );
					$css_file->update();

					return array(
						'success' => true,
						'page_id' => $page_id,
						'message' => __( 'CSS regenerated for page.', 'site-pilot-ai' ),
					);
				}
			}

			// Fallback: clear all cache.
			$plugin->files_manager->clear_cache();

			return array(
				'success' => true,
				'page_id' => $page_id,
				'message' => __( 'Elementor cache cleared.', 'site-pilot-ai' ),
			);
		}

		// Full site CSS regeneration.
		$plugin->files_manager->clear_cache();

		return array(
			'success' => true,
			'message' => __( 'All Elementor CSS regenerated.', 'site-pilot-ai' ),
		);
	}

	/**
	 * Surgical edit of a single Elementor element without full JSON round-trip.
	 *
	 * Finds an element by ID, section index, or search criteria, merges
	 * settings, saves back, and returns only the modified element.
	 *
	 * @param int   $page_id Page/post ID.
	 * @param array $args    {
	 *     @type string $element_id    Find element by its Elementor ID.
	 *     @type int    $section_index Find top-level section by 0-based index.
	 *     @type array  $find          Search criteria: {widgetType, settings.key => value}.
	 *     @type array  $settings      Settings to merge into the found element.
	 *     @type array  $delete_settings Setting keys to remove.
	 * }
	 * @return array|WP_Error Result with the modified element, or error.
	 */
	public function edit_section( $page_id, $args ) {
		if ( ! $this->is_active() ) {
			return new WP_Error(
				'elementor_not_active',
				__( 'Elementor is not installed or active.', 'site-pilot-ai' ),
				array( 'status' => 400 )
			);
		}

		$page = $this->validate_post( $page_id );
		if ( is_wp_error( $page ) ) {
			return $page;
		}

		$elementor_data = get_post_meta( $page_id, '_elementor_data', true );
		if ( empty( $elementor_data ) ) {
			return new WP_Error(
				'no_elementor_data',
				__( 'This page has no Elementor data.', 'site-pilot-ai' ),
				array( 'status' => 404 )
			);
		}

		$elements = json_decode( $elementor_data, true );
		if ( ! is_array( $elements ) ) {
			return new WP_Error(
				'invalid_elementor_data',
				__( 'Elementor data is not valid JSON.', 'site-pilot-ai' ),
				array( 'status' => 500 )
			);
		}

		// --- Locate the target element ---

		$element_id    = isset( $args['element_id'] ) ? (string) $args['element_id'] : '';
		$section_index = isset( $args['section_index'] ) ? (int) $args['section_index'] : -1;
		$find          = isset( $args['find'] ) && is_array( $args['find'] ) ? $args['find'] : array();

		$found    = null;
		$found_path = '';

		if ( '' !== $element_id ) {
			// Find by element ID (recursive).
			$found =& $this->find_element_by_id( $elements, $element_id, $found_path );
		} elseif ( $section_index >= 0 ) {
			// Find by top-level index.
			if ( isset( $elements[ $section_index ] ) ) {
				$found      =& $elements[ $section_index ];
				$found_path = ( isset( $found['elType'] ) ? $found['elType'] : 'element' ) . "[{$section_index}]";
			}
		} elseif ( ! empty( $find ) ) {
			// Find by search criteria.
			$found =& $this->find_element_by_criteria( $elements, $find, $found_path );
		} else {
			return new WP_Error(
				'no_selector',
				__( 'Provide element_id, section_index, or find criteria to locate the target element.', 'site-pilot-ai' ),
				array( 'status' => 400 )
			);
		}

		if ( null === $found ) {
			return new WP_Error(
				'element_not_found',
				__( 'No matching element found in the Elementor tree.', 'site-pilot-ai' ),
				array( 'status' => 404 )
			);
		}

		// --- Apply patches ---

		$changes = array();

		// Merge settings.
		if ( ! empty( $args['settings'] ) && is_array( $args['settings'] ) ) {
			if ( ! isset( $found['settings'] ) || ! is_array( $found['settings'] ) ) {
				$found['settings'] = array();
			}
			foreach ( $args['settings'] as $key => $value ) {
				$found['settings'][ $key ] = $value;
				$changes[] = "set {$key}";
			}
		}

		// Delete settings.
		if ( ! empty( $args['delete_settings'] ) && is_array( $args['delete_settings'] ) ) {
			foreach ( $args['delete_settings'] as $key ) {
				if ( isset( $found['settings'][ $key ] ) ) {
					unset( $found['settings'][ $key ] );
					$changes[] = "removed {$key}";
				}
			}
		}

		if ( empty( $changes ) ) {
			return new WP_Error(
				'no_changes',
				__( 'No settings or delete_settings provided — nothing to change.', 'site-pilot-ai' ),
				array( 'status' => 400 )
			);
		}

		// --- Save back ---

		$elementor_json = wp_json_encode( $elements );

		// Run validation/fixes (auto-generate IDs, rename bad keys, etc.).
		$validation = $this->validate_and_fix_elements( $elementor_json );

		update_post_meta( $page_id, '_elementor_data', wp_slash( $elementor_json ) );

		// Regenerate CSS.
		delete_post_meta( $page_id, '_elementor_css' );
		if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			$css_file = \Elementor\Core\Files\CSS\Post::create( $page_id );
			$css_file->update();
		}

		// Purge caches.
		$this->purge_page_cache( $page_id );

		// Re-read the saved element to return its final state.
		$saved_data = get_post_meta( $page_id, '_elementor_data', true );
		$saved_elements = json_decode( $saved_data, true );
		$saved_path = '';
		$saved_element = null;

		if ( '' !== $element_id ) {
			$saved_element = $this->find_element_by_id_readonly( $saved_elements, $element_id );
		} elseif ( $section_index >= 0 && isset( $saved_elements[ $section_index ] ) ) {
			$saved_element = $saved_elements[ $section_index ];
		} elseif ( ! empty( $find ) ) {
			$saved_element = $this->find_element_by_criteria_readonly( $saved_elements, $find );
		}

		$result = array(
			'success'  => true,
			'page_id'  => $page_id,
			'path'     => $found_path,
			'changes'  => $changes,
			'element'  => $saved_element ? $saved_element : $found,
			'edit_url' => admin_url( "post.php?post={$page_id}&action=elementor" ),
		);

		$all_warnings = array_merge(
			isset( $validation['warnings'] ) ? $validation['warnings'] : array(),
			isset( $validation['fixes'] ) ? $validation['fixes'] : array()
		);
		if ( ! empty( $all_warnings ) ) {
			$result['warnings'] = $all_warnings;
		}

		return $result;
	}

	/**
	 * Find an element by ID recursively (returns reference).
	 *
	 * @param array  &$elements Element tree (by reference for modification).
	 * @param string $id        Target element ID.
	 * @param string &$path     Populated with the path to the found element.
	 * @return array|null Reference to the found element or null.
	 */
	private function &find_element_by_id( &$elements, $id, &$path ) {
		$null = null;
		foreach ( $elements as $idx => &$el ) {
			$el_type = isset( $el['elType'] ) ? $el['elType'] : 'element';
			$current_path = "{$el_type}[{$idx}]";

			if ( isset( $el['id'] ) && (string) $el['id'] === $id ) {
				$path = $current_path;
				return $el;
			}

			if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
				$child_path = '';
				$found =& $this->find_element_by_id( $el['elements'], $id, $child_path );
				if ( null !== $found ) {
					$path = $current_path . '.' . $child_path;
					return $found;
				}
			}
		}
		unset( $el );
		return $null;
	}

	/**
	 * Find an element by search criteria recursively (returns reference).
	 *
	 * @param array  &$elements Element tree (by reference).
	 * @param array  $criteria  Search criteria (widgetType, settings.key => value).
	 * @param string &$path     Populated with the path to the found element.
	 * @return array|null Reference to the found element or null.
	 */
	private function &find_element_by_criteria( &$elements, $criteria, &$path ) {
		$null = null;
		foreach ( $elements as $idx => &$el ) {
			$el_type = isset( $el['elType'] ) ? $el['elType'] : 'element';
			$current_path = "{$el_type}[{$idx}]";

			if ( $this->element_matches_criteria( $el, $criteria ) ) {
				$path = $current_path;
				return $el;
			}

			if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
				$child_path = '';
				$found =& $this->find_element_by_criteria( $el['elements'], $criteria, $child_path );
				if ( null !== $found ) {
					$path = $current_path . '.' . $child_path;
					return $found;
				}
			}
		}
		unset( $el );
		return $null;
	}

	/**
	 * Check if an element matches search criteria.
	 *
	 * @param array $element  The element to check.
	 * @param array $criteria Search criteria.
	 * @return bool True if all criteria match.
	 */
	private function element_matches_criteria( $element, $criteria ) {
		foreach ( $criteria as $key => $value ) {
			if ( 'widgetType' === $key ) {
				if ( ! isset( $element['widgetType'] ) || $element['widgetType'] !== $value ) {
					return false;
				}
			} elseif ( 'elType' === $key ) {
				if ( ! isset( $element['elType'] ) || $element['elType'] !== $value ) {
					return false;
				}
			} elseif ( 0 === strpos( $key, 'settings.' ) ) {
				$setting_key = substr( $key, 9 );
				$settings    = isset( $element['settings'] ) ? $element['settings'] : array();
				if ( ! isset( $settings[ $setting_key ] ) || $settings[ $setting_key ] !== $value ) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Find an element by ID (read-only, returns copy).
	 *
	 * @param array  $elements Element tree.
	 * @param string $id       Target element ID.
	 * @return array|null Element copy or null.
	 */
	private function find_element_by_id_readonly( $elements, $id ) {
		foreach ( $elements as $el ) {
			if ( isset( $el['id'] ) && (string) $el['id'] === $id ) {
				return $el;
			}
			if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
				$found = $this->find_element_by_id_readonly( $el['elements'], $id );
				if ( null !== $found ) {
					return $found;
				}
			}
		}
		return null;
	}

	/**
	 * Find an element by criteria (read-only, returns copy).
	 *
	 * @param array $elements Element tree.
	 * @param array $criteria Search criteria.
	 * @return array|null Element copy or null.
	 */
	private function find_element_by_criteria_readonly( $elements, $criteria ) {
		foreach ( $elements as $el ) {
			if ( $this->element_matches_criteria( $el, $criteria ) ) {
				return $el;
			}
			if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
				$found = $this->find_element_by_criteria_readonly( $el['elements'], $criteria );
				if ( null !== $found ) {
					return $found;
				}
			}
		}
		return null;
	}
}
