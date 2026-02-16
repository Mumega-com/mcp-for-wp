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

		return array(
			'page_id'        => $page_id,
			'title'          => $page->post_title,
			'has_elementor'  => ! empty( $elementor_data ),
			'edit_mode'      => $edit_mode ?: 'classic',
			'template_type'  => $template_type ?: null,
			'elementor_data' => $elementor_data ? json_decode( $elementor_data, true ) : null,
			'elementor_json' => $elementor_data ?: null,
			'edit_url'       => admin_url( "post.php?post={$page_id}&action=elementor" ),
		);
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

		// --- 3. Sanitize widget settings to prevent renderer crashes (#90) ---

		$sanitize_warnings = $this->sanitize_element_settings( $elementor_json );
		if ( ! empty( $sanitize_warnings ) ) {
			$save_debug['sanitize_warnings'] = $sanitize_warnings;
			// Re-write sanitized data.
			update_post_meta( $page_id, '_elementor_data', wp_slash( $elementor_json ) );
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

		// --- 5. Purge page caches (#89) ---

		$this->purge_page_cache( $page_id );
		$save_debug['page_cache_purged'] = true;

		return array(
			'success'     => true,
			'page_id'     => (string) $page_id,
			'message'     => __( 'Elementor data updated.', 'site-pilot-ai' ),
			'save_method' => $save_method,
			'debug'       => $save_debug,
			'edit_url'    => admin_url( "post.php?post={$page_id}&action=elementor" ),
		);
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
	 * Known valid control keys per widget type.
	 *
	 * Keys not in this map are left as-is (Elementor ignores truly unknown keys
	 * for most widgets). This map targets controls that use the wrong name and
	 * crash the frontend renderer.
	 *
	 * @var array<string, array<string, string>>
	 */
	private static $control_renames = array(
		'icon-box' => array(
			'title_size' => 'title_typography_font_size',
		),
	);

	/**
	 * Walk the element tree and fix known invalid control keys.
	 *
	 * Modifies $elementor_json in-place (reference).
	 *
	 * @param string &$elementor_json JSON string (modified in-place).
	 * @return array Warnings about renamed keys.
	 */
	private function sanitize_element_settings( &$elementor_json ) {
		$elements = json_decode( $elementor_json, true );
		if ( ! is_array( $elements ) ) {
			return array();
		}

		$warnings = array();
		$changed  = false;

		$walk = function ( &$el ) use ( &$walk, &$warnings, &$changed ) {
			$widget_type = isset( $el['widgetType'] ) ? $el['widgetType'] : '';
			if ( $widget_type && isset( self::$control_renames[ $widget_type ] ) ) {
				$renames = self::$control_renames[ $widget_type ];
				foreach ( $renames as $old_key => $new_key ) {
					if ( isset( $el['settings'][ $old_key ] ) && ! isset( $el['settings'][ $new_key ] ) ) {
						$el['settings'][ $new_key ] = $el['settings'][ $old_key ];
						unset( $el['settings'][ $old_key ] );
						$warnings[] = "{$widget_type}: renamed {$old_key} -> {$new_key}";
						$changed    = true;
					}
				}
			}
			if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
				foreach ( $el['elements'] as &$child ) {
					$walk( $child );
				}
			}
		};

		foreach ( $elements as &$el ) {
			$walk( $el );
		}
		unset( $el );

		if ( $changed ) {
			$elementor_json = wp_json_encode( $elements );
		}

		return $warnings;
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
}
