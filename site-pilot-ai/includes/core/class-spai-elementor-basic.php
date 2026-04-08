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
		$allowed = array( 'page', 'post', 'elementor_library', 'elementor_snippet' );

		if ( ! $post || ! in_array( $post->post_type, $allowed, true ) ) {
			$hint = sprintf(
				'Post ID %d not found or is not a supported type (page, post, elementor_library, elementor_snippet). Use wp_list_pages or wp_list_posts to find valid IDs.',
				absint( $post_id )
			);
			return new WP_Error(
				'not_found',
				__( 'Post not found or unsupported type.', 'site-pilot-ai' ),
				array(
					'status' => 404,
					'hint'   => $hint,
				)
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
	public function get_elementor_data( $page_id, $data = array() ) {
		if ( ! $this->is_active() ) {
			return new WP_Error(
				'elementor_not_active',
				__( 'Elementor is not installed or active.', 'site-pilot-ai' ),
				array(
					'status' => 400,
					'hint'   => 'Elementor is not installed or active on this site. Use wp_introspect to check available page builders. For content editing without Elementor, use wp_update_page with HTML content, or wp_set_blocks for Gutenberg.',
				)
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

		$decoded = $elementor_data ? json_decode( $elementor_data, true ) : null;

		// Strip default widget settings to reduce payload size.
		if ( ! empty( $data['strip_defaults'] ) && is_array( $decoded ) ) {
			$this->strip_element_defaults( $decoded );
		}

		return array(
			'page_id'        => $page_id,
			'title'          => $page->post_title,
			'has_elementor'  => ! empty( $elementor_data ),
			'edit_mode'      => $edit_mode ?: 'classic',
			'template_type'  => $template_type ?: null,
			'elementor_data' => $decoded,
			'elementor_json' => $elementor_data ?: null,
			'page_settings'  => $page_settings ? ( is_array( $page_settings ) ? $page_settings : json_decode( $page_settings, true ) ) : null,
			'edit_url'       => admin_url( "post.php?post={$page_id}&action=elementor" ),
		);
	}

	/**
	 * Get Elementor data for multiple pages in bulk.
	 *
	 * @param array $page_ids Array of page IDs.
	 * @return array Results with 'results', 'errors', and 'count' keys.
	 */
	public function get_elementor_data_bulk( $page_ids ) {
		$results = array();
		$errors  = array();

		foreach ( $page_ids as $page_id ) {
			$page_id = absint( $page_id );
			$data    = $this->get_elementor_data( $page_id );

			if ( is_wp_error( $data ) ) {
				$errors[ $page_id ] = $data->get_error_message();
			} else {
				$results[ $page_id ] = $data;
			}
		}

		return array(
			'results' => $results,
			'errors'  => $errors,
			'count'   => count( $results ),
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

		foreach ( $elements as $index => $element ) {
			$section_summary = $this->summarize_element( $element, $widget_count );
			if ( $section_summary ) {
				$section_summary['index'] = $index;
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
			'id'   => isset( $element['id'] ) ? $element['id'] : null,
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
			'posts'         => array( 'posts_post_type', 'classic_posts_per_page', 'classic_columns' ),
			'loop-grid'     => array( 'query_post_type', 'template_id', 'columns', 'posts_per_page' ),
			'portfolio'     => array( 'posts_per_page', 'columns' ),
		);

		return isset( $map[ $widget_type ] ) ? $map[ $widget_type ] : array( 'title', 'text', 'heading' );
	}

	/**
	 * Set Elementor data for a page.
	 *
	 * @param int   $page_id  Page ID.
	 * @param array $data     Elementor data.
	 * @param bool  $dry_run  If true, validate only — no DB writes, no cache clear.
	 * @return array|WP_Error Result or error.
	 */
	public function set_elementor_data( $page_id, $data, $dry_run = false ) {
		if ( ! $this->is_active() ) {
			return new WP_Error(
				'elementor_not_active',
				__( 'Elementor is not installed or active.', 'site-pilot-ai' ),
				array(
					'status' => 400,
					'hint'   => 'Elementor is not installed or active on this site. Use wp_introspect to check available page builders. For content editing without Elementor, use wp_update_page with HTML content, or wp_set_blocks for Gutenberg.',
				)
			);
		}

		$page = $this->validate_post( $page_id );
		if ( is_wp_error( $page ) ) {
			return $page;
		}

		// Determine layout mode for hints.
		$layout_hint = '';
		if ( class_exists( 'Spai_Error_Hints' ) ) {
			$experiment   = get_option( 'elementor_experiment-container', '' );
			$is_flexbox   = in_array( $experiment, array( 'active', 'default' ), true );
			$layout_hint  = $is_flexbox
				? 'This site uses flexbox layout mode. Use "container" as the top-level elType.'
				: 'This site uses classic layout mode. Use "section" > "column" > widget structure.';
		}

		$structure_hint = 'Elementor data must be a JSON array of element objects. Each element needs: id (8-char alphanumeric), elType ("section"/"column"/"widget" or "container"), settings (object), elements (array). ' . $layout_hint . ' Use wp_get_elementor on an existing page to see the expected format.';

		// Validate and encode data
		$elementor_json = null;

		// --- Base64-encoded payload (bypass all quoting/escaping issues) ---
		if ( ! empty( $data['elementor_data_base64'] ) ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			$raw_decoded = base64_decode( $data['elementor_data_base64'], true );
			if ( false === $raw_decoded ) {
				return new WP_Error(
					'invalid_base64',
					__( 'Invalid base64 encoding in elementor_data_base64.', 'site-pilot-ai' ),
					array(
						'status' => 400,
						'hint'   => 'The elementor_data_base64 value is not valid base64. Encode your JSON string with base64_encode() or btoa() before sending.',
					)
				);
			}
			$decoded = json_decode( $raw_decoded, true );
			if ( null === $decoded && json_last_error() !== JSON_ERROR_NONE ) {
				return new WP_Error(
					'invalid_json',
					__( 'Base64-decoded data is not valid JSON.', 'site-pilot-ai' ),
					array(
						'status' => 400,
						'hint'   => 'The base64 payload decoded successfully but the resulting string is not valid JSON. Error: ' . json_last_error_msg(),
						'guide'  => 'wp_get_guide(topic=\'elementor\')',
					)
				);
			}
			if ( ! is_array( $decoded ) ) {
				return new WP_Error(
					'invalid_structure',
					__( 'Elementor data must decode to an array.', 'site-pilot-ai' ),
					array(
						'status' => 400,
						'hint'   => $structure_hint,
						'guide'  => 'wp_get_guide(topic=\'elementor\')',
					)
				);
			}
			$elementor_json = $raw_decoded;
		} elseif ( isset( $data['elementor_data'] ) ) {
			// If array, validate structure and encode to JSON
			if ( is_array( $data['elementor_data'] ) ) {
				if ( ! $this->is_valid_elementor_structure( $data['elementor_data'] ) ) {
					return new WP_Error(
						'invalid_structure',
						__( 'Elementor data must be an array of element objects.', 'site-pilot-ai' ),
						array(
							'status' => 400,
							'hint'   => $structure_hint,
							'guide'  => 'wp_get_guide(topic=\'elementor\')',
						)
					);
				}
				$elementor_json = wp_json_encode( $data['elementor_data'] );
			} else {
				// Validate JSON string — with recovery for common MCP corruption.
				$decoded = $this->try_json_decode( $data['elementor_data'] );
				if ( null === $decoded ) {
					return new WP_Error(
						'invalid_json',
						__( 'Invalid Elementor JSON data.', 'site-pilot-ai' ),
						array(
							'status' => 400,
							'hint'   => 'The provided string is not valid JSON even after recovery attempts (stripslashes, double-encoding unwrap). Error: ' . json_last_error_msg() . '. Consider using elementor_data_base64 to avoid quoting issues — base64-encode your JSON before sending.',
							'guide'  => 'wp_get_guide(topic=\'elementor\')',
						)
					);
				}
				if ( ! is_array( $decoded ) ) {
					return new WP_Error(
						'invalid_structure',
						__( 'Elementor data must decode to an array.', 'site-pilot-ai' ),
						array(
							'status' => 400,
							'hint'   => $structure_hint,
							'guide'  => 'wp_get_guide(topic=\'elementor\')',
						)
					);
				}
				// Re-encode from decoded data to ensure clean JSON.
				$elementor_json = wp_json_encode( $decoded );
			}
		} elseif ( isset( $data['elementor_json'] ) ) {
			// Direct JSON string — with recovery.
			$decoded = $this->try_json_decode( $data['elementor_json'] );
			if ( null === $decoded ) {
				return new WP_Error(
					'invalid_json',
					__( 'Invalid Elementor JSON data.', 'site-pilot-ai' ),
					array(
						'status' => 400,
						'hint'   => 'The provided string is not valid JSON even after recovery attempts. Error: ' . json_last_error_msg() . '. Consider using elementor_data_base64 to avoid quoting issues.',
						'guide'  => 'wp_get_guide(topic=\'elementor\')',
					)
				);
			}
			if ( ! is_array( $decoded ) ) {
				return new WP_Error(
					'invalid_structure',
					__( 'Elementor data must decode to an array.', 'site-pilot-ai' ),
					array(
						'status' => 400,
						'hint'   => $structure_hint,
						'guide'  => 'wp_get_guide(topic=\'elementor\')',
					)
				);
			}
			// Re-encode from decoded data to ensure clean JSON.
			$elementor_json = wp_json_encode( $decoded );
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

		// --- Dry-run mode: validate only, no DB writes ---
		if ( $dry_run ) {
			$dry_json   = $elementor_json; // work on a copy
			$validation = $this->validate_and_fix_elements( $dry_json );

			if ( ! empty( $validation['errors'] ) ) {
				return new WP_Error(
					'invalid_elementor_structure',
					implode( ' | ', $validation['errors'] ),
					array(
						'status'            => 400,
						'validation_errors' => $validation['errors'],
					)
				);
			}

			$all_warnings = array_merge(
				$validation['warnings'],
				$validation['fixes']
			);

			// Check RTL issues if site is RTL.
			if ( function_exists( 'is_rtl' ) && is_rtl() ) {
				$rtl_warnings = $this->check_rtl_issues( $dry_json );
				$all_warnings = array_merge( $all_warnings, $rtl_warnings );
			}

			$result = array(
				'success'  => true,
				'dry_run'  => true,
				'page_id'  => (string) $page_id,
				'message'  => __( 'Validation complete — no changes saved.', 'site-pilot-ai' ),
			);

			if ( ! empty( $all_warnings ) ) {
				$result['warnings'] = $all_warnings;
			}

			$result['debug'] = array(
				'validation_fixes'    => $validation['fixes'],
				'validation_warnings' => $validation['warnings'],
			);

			return $result;
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

		// --- 2. Validate and fix element tree BEFORE writing ---

		$input_count = count( json_decode( $elementor_json, true ) ?: array() );
		$save_debug['sections_submitted'] = $input_count;

		$validation = $this->validate_and_fix_elements( $elementor_json );
		if ( ! empty( $validation['errors'] ) ) {
			return new WP_Error(
				'invalid_elementor_structure',
				implode( ' | ', $validation['errors'] ),
				array(
					'status'            => 400,
					'validation_errors' => $validation['errors'],
				)
			);
		}
		if ( ! empty( $validation['fixes'] ) || ! empty( $validation['warnings'] ) ) {
			$save_debug['validation_fixes']    = $validation['fixes'];
			$save_debug['validation_warnings'] = $validation['warnings'];
		}

		// Safety check: ensure validation did not corrupt or truncate the data.
		$post_validation_decoded = json_decode( $elementor_json, true );
		if ( ! is_array( $post_validation_decoded ) ) {
			return new WP_Error(
				'validation_corrupted',
				__( 'Elementor data was corrupted during validation. Please retry with elementor_data_base64.', 'site-pilot-ai' ),
				array( 'status' => 500, 'sections_submitted' => $input_count )
			);
		}
		$post_validation_count = count( $post_validation_decoded );
		if ( $post_validation_count !== $input_count ) {
			return new WP_Error(
				'validation_data_loss',
				sprintf(
					__( 'Validation changed section count from %d to %d. Aborting to prevent data loss.', 'site-pilot-ai' ),
					$input_count,
					$post_validation_count
				),
				array( 'status' => 500, 'sections_submitted' => $input_count, 'sections_after_validation' => $post_validation_count )
			);
		}

		// --- 3. Save via Elementor Document API (preferred) or meta fallback ---

		$save_method   = 'meta_direct';
		$document_saved = false;

		if ( $elementor_ok ) {
			// Try Document::save() — this updates _elementor_data, post_content, and CSS in one go.
			$documents_manager = \Elementor\Plugin::$instance->documents;
			if ( $documents_manager && method_exists( $documents_manager, 'get' ) ) {
				wp_cache_delete( $page_id, 'post_meta' );
				clean_post_cache( $page_id );

				$document = $documents_manager->get( $page_id, false );
				if ( $document && method_exists( $document, 'save' ) ) {
					$save_result = $document->save( array( 'elements' => $post_validation_decoded ) );

					if ( ! is_wp_error( $save_result ) ) {
						// (#198) Always overwrite raw meta after Document::save() to
						// prevent revision/cache divergence on subsequent reads.
						update_post_meta( $page_id, '_elementor_data', wp_slash( $elementor_json ) );

						wp_cache_delete( $page_id, 'post_meta' );
						clean_post_cache( $page_id );
						if ( function_exists( 'wp_cache_flush_group' ) ) {
							wp_cache_flush_group( 'post_meta' );
						}

						$stored_after_document = get_post_meta( $page_id, '_elementor_data', true );
						$decoded_after_document = json_decode( $stored_after_document, true );
						$stored_after_count     = is_array( $decoded_after_document ) ? count( $decoded_after_document ) : 0;

						if ( $stored_after_count === $input_count ) {
							$document_saved              = true;
							$save_method                 = 'document_save_with_meta_overwrite';
							$save_debug['document_save'] = true;
						} else {
							$save_debug['document_save_persist_mismatch'] = array(
								'sections_expected' => $input_count,
								'sections_saved'    => $stored_after_count,
							);
						}
					} else {
						$save_debug['document_save_error'] = $save_result->get_error_message();
					}
				}
			}
		}

		// Fallback: direct meta write if Document::save() is unavailable or failed.
		if ( ! $document_saved ) {
			update_post_meta( $page_id, '_elementor_data', wp_slash( $elementor_json ) );
			$save_debug['meta_written'] = true;

			// (#187) Update post_content so Elementor's front-end renderer doesn't
			// short-circuit on stale/empty post_content from a previous save attempt.
			wp_update_post( array(
				'ID'           => $page_id,
				'post_content' => '',
			) );

			// (#187) Flush Elementor's in-memory document cache. Document::save() may
			// have cached an empty elements array; force a fresh load from the DB so
			// subsequent CSS regeneration and any same-request renders see the new data.
			if ( $elementor_ok && ! empty( \Elementor\Plugin::$instance->documents ) ) {
				clean_post_cache( $page_id );
				wp_cache_delete( $page_id, 'post_meta' );
				\Elementor\Plugin::$instance->documents->get( $page_id, false );
				$save_debug['document_cache_flushed'] = true;
			}
		}

		// Verify data was stored correctly.
		wp_cache_delete( $page_id, 'post_meta' );
		$stored = get_post_meta( $page_id, '_elementor_data', true );
		$stored_decoded = json_decode( $stored, true );
		$stored_count   = is_array( $stored_decoded ) ? count( $stored_decoded ) : 0;
		$save_debug['meta_verified']    = ( $stored_count === $input_count );
		$save_debug['sections_saved']   = $stored_count;

		if ( $stored_count !== $input_count ) {
			return new WP_Error(
				'meta_write_truncated',
				sprintf(
					__( 'Data truncated during save: %d sections submitted but only %d stored. Try using elementor_data_base64 for large payloads.', 'site-pilot-ai' ),
					$input_count,
					$stored_count
				),
				array(
					'status'              => 500,
					'sections_submitted'  => $input_count,
					'sections_saved'      => $stored_count,
					'hint'                => 'Base64-encode your JSON and send via elementor_data_base64 parameter to bypass size limits.',
				)
			);
		}

		// --- 4. CSS regeneration (only needed for meta_direct fallback — Document::save handles it) ---

		if ( ! $document_saved && $elementor_ok ) {
			// Clear file cache.
			if ( ! empty( \Elementor\Plugin::$instance->files_manager ) ) {
				\Elementor\Plugin::$instance->files_manager->clear_cache();
				$save_debug['cache_cleared'] = true;
			}

			delete_post_meta( $page_id, '_elementor_css' );

			if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
				$css_file = \Elementor\Core\Files\CSS\Post::create( $page_id );
				$css_file->update();
				$save_debug['css_regenerated'] = true;
			}
		}

		// Report CSS file size for debugging.
		if ( $elementor_ok ) {
			$upload_dir = wp_upload_dir();
			$css_path   = $upload_dir['basedir'] . '/elementor/css/post-' . $page_id . '.css';
			$css_size   = file_exists( $css_path ) ? filesize( $css_path ) : 0;
			$save_debug['css_file_size'] = $css_size;

			// If CSS is still empty/tiny, prime it via loopback request.
			if ( $css_size < 10 ) {
				delete_post_meta( $page_id, '_elementor_css' );
				$save_debug['css_deferred'] = true;
				$permalink = get_permalink( $page_id );
				if ( $permalink ) {
					wp_remote_get(
						add_query_arg( 'spai_prime_css', wp_rand(), $permalink ),
						array(
							'timeout'   => 15,
							'sslverify' => false,
							'blocking'  => false,
						)
					);
					$save_debug['css_primed'] = true;
				}
			}

			// Also regenerate the global CSS (kit styles) if applicable.
			if ( ! $document_saved && class_exists( '\Elementor\Core\Files\CSS\Global_CSS' ) ) {
				$global_css = \Elementor\Core\Files\CSS\Global_CSS::create( 'global.css' );
				if ( $global_css ) {
					$global_css->update();
					$save_debug['global_css_regenerated'] = true;
				}
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

		// --- 7. Check RTL issues if site is RTL ---

		if ( function_exists( 'is_rtl' ) && is_rtl() ) {
			$rtl_warnings = $this->check_rtl_issues( $elementor_json );
			if ( ! empty( $rtl_warnings ) ) {
				$save_debug['rtl_warnings'] = $rtl_warnings;
			}
		}

		// Build top-level warnings from validation results.
		$all_warnings = array();
		if ( ! empty( $save_debug['validation_warnings'] ) ) {
			$all_warnings = array_merge( $all_warnings, $save_debug['validation_warnings'] );
		}
		if ( ! empty( $save_debug['validation_fixes'] ) ) {
			$all_warnings = array_merge( $all_warnings, $save_debug['validation_fixes'] );
		}
		if ( ! empty( $save_debug['rtl_warnings'] ) ) {
			$all_warnings = array_merge( $all_warnings, $save_debug['rtl_warnings'] );
		}

		$result = array(
			'success'            => true,
			'page_id'            => (string) $page_id,
			'message'            => __( 'Elementor data updated.', 'site-pilot-ai' ),
			'sections_saved'     => $save_debug['sections_saved'],
			'sections_submitted' => $save_debug['sections_submitted'],
			'save_method'        => $save_method,
			'preview_url'        => add_query_arg( 'elementor-preview', $page_id, get_permalink( $page_id ) ),
			'css_regenerated'    => ! empty( $save_debug['css_regenerated'] ),
			'debug'              => $save_debug,
			'edit_url'           => admin_url( "post.php?post={$page_id}&action=elementor" ),
			'next_step'          => sprintf( 'Call wp_get_elementor_summary(id=%d) to verify the page structure.', $page_id ),
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
	/**
	 * Attempt to decode a JSON string with recovery for common MCP corruption.
	 *
	 * Tries, in order:
	 * 1. Direct json_decode
	 * 2. Strip outer quotes (double-encoded JSON string containing JSON)
	 * 3. stripslashes then decode (WordPress magic quotes / MCP escaping)
	 * 4. utf8_encode then decode (encoding issues)
	 *
	 * @param string $raw_string The raw JSON string to decode.
	 * @return mixed|null The decoded value, or null if all attempts fail.
	 */
	private function try_json_decode( $raw_string ) {
		// 1. Direct decode.
		$decoded = json_decode( $raw_string, true );
		if ( null !== $decoded || json_last_error() === JSON_ERROR_NONE ) {
			return $decoded;
		}

		// 2. Strip outer quotes — double-encoded JSON string (e.g. '"[{...}]"').
		$trimmed = trim( $raw_string );
		if ( strlen( $trimmed ) >= 2 && '"' === $trimmed[0] && '"' === $trimmed[ strlen( $trimmed ) - 1 ] ) {
			$inner   = json_decode( $trimmed, true ); // decode outer string wrapper
			if ( is_string( $inner ) ) {
				$decoded = json_decode( $inner, true );
				if ( null !== $decoded || json_last_error() === JSON_ERROR_NONE ) {
					return $decoded;
				}
			}
		}

		// 3. stripslashes then decode (WordPress magic quotes or MCP escaping).
		$unslashed = stripslashes( $raw_string );
		if ( $unslashed !== $raw_string ) {
			$decoded = json_decode( $unslashed, true );
			if ( null !== $decoded || json_last_error() === JSON_ERROR_NONE ) {
				return $decoded;
			}
		}

		// 4. utf8_encode for encoding issues (only if mbstring detects non-UTF-8).
		if ( function_exists( 'mb_detect_encoding' ) && ! mb_detect_encoding( $raw_string, 'UTF-8', true ) ) {
			// phpcs:ignore PHPCompatibility.FunctionUse.RemovedFunctions.utf8_encodeDeprecated
			$utf8    = function_exists( 'mb_convert_encoding' )
				? mb_convert_encoding( $raw_string, 'UTF-8', 'ISO-8859-1' )
				: utf8_encode( $raw_string );
			$decoded = json_decode( $utf8, true );
			if ( null !== $decoded || json_last_error() === JSON_ERROR_NONE ) {
				return $decoded;
			}
		}

		return null;
	}

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
		// Note: icon-box 'title_size' is a valid h-tag selector (h1-h6), not a font size.
		// Do NOT rename it to title_typography_font_size — that corrupts the value. (#190)
		'flip-box' => array(
			'front_title_text'       => 'title_text_a',
			'front_description_text' => 'description_text_a',
			'back_title_text'        => 'title_text_b',
			'back_description_text'  => 'description_text_b',
			'front_background_color' => 'background_color_a',
			'back_background_color'  => 'background_color_b',
		),
		'nav-menu' => array(
			'text_color'       => 'color_menu_item',
			'hover_color'      => 'color_menu_item_hover',
			'active_color'     => 'color_menu_item_active',
			'dropdown_color'   => 'color_dropdown_item',
			'dropdown_hover'   => 'color_dropdown_item_hover',
		),
		'posts' => array(
			'post_type'      => 'posts_post_type',
			'columns'        => 'classic_columns',
			'posts_per_page' => 'classic_posts_per_page',
			'show_title'     => 'classic_show_title',
			'show_excerpt'   => 'classic_show_excerpt',
			'excerpt_length' => 'classic_excerpt_length',
			'show_read_more' => 'classic_show_read_more',
			'read_more_text' => 'classic_read_more_text',
			'show_date'      => 'classic_show_date',
			'show_author'    => 'classic_show_author',
			'show_comments'  => 'classic_show_comments',
			'thumbnail_size' => 'classic_thumbnail_size_size',
			'orderby'        => 'posts_orderby',
			'order'          => 'posts_order',
		),
	);

	/**
	 * Valid top-level element types.
	 *
	 * @var array
	 */
	private static $valid_el_types = array(
		// Classic layout.
		'section', 'column', 'widget', 'container',
		// Elementor v4 Atomic elements (#211).
		'e-div-block', 'e-flexbox', 'e-heading', 'e-paragraph', 'e-button',
		'e-image', 'e-svg', 'e-divider', 'e-youtube', 'e-self-hosted-video',
	);

	/**
	 * Valid nesting rules: parent elType => allowed child elTypes.
	 *
	 * @var array<string, array<string>>
	 */
	private static $nesting_rules = array(
		// Classic layout.
		'section'     => array( 'column' ),
		'column'      => array( 'widget', 'section', 'container' ),
		'container'   => array( 'widget', 'container' ),
		// Elementor v4 Atomic layout (#211) — div-block and flexbox can nest anything.
		'e-div-block' => array( 'e-div-block', 'e-flexbox', 'e-heading', 'e-paragraph', 'e-button', 'e-image', 'e-svg', 'e-divider', 'e-youtube', 'e-self-hosted-video', 'widget', 'container' ),
		'e-flexbox'   => array( 'e-div-block', 'e-flexbox', 'e-heading', 'e-paragraph', 'e-button', 'e-image', 'e-svg', 'e-divider', 'e-youtube', 'e-self-hosted-video', 'widget', 'container' ),
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
	 * Get valid widget setting keys, preferring the live Elementor widget schema.
	 *
	 * Falls back to the static reference registry when live controls are unavailable.
	 *
	 * @param string $widget_type Widget type name.
	 * @return array Setting key names.
	 */
	private function get_valid_widget_keys( $widget_type ) {
		$keys = array();

		if ( class_exists( '\Elementor\Plugin' ) && ! empty( \Elementor\Plugin::$instance->widgets_manager ) ) {
			$manager = \Elementor\Plugin::$instance->widgets_manager;
			if ( method_exists( $manager, 'get_widget_types' ) ) {
				$types = $manager->get_widget_types();
				if ( isset( $types[ $widget_type ] ) && method_exists( $types[ $widget_type ], 'get_controls' ) ) {
					$controls = $types[ $widget_type ]->get_controls();
					if ( is_array( $controls ) ) {
						$keys = array_keys( $controls );
					}
				}
			}
		}

		// Always merge in our static reference schema — it may know about keys
		// that the live widget registry doesn't expose via get_controls() (e.g.
		// responsive base keys like 'align' on icon-box).
		$static_keys = Spai_Elementor_Widgets::get_valid_keys( $widget_type );
		$keys = array_merge( $keys, $static_keys );

		return array_values( array_unique( $keys ) );
	}

	/**
	 * Check whether a setting key is a valid responsive variant of a base control.
	 *
	 * Elementor commonly expands responsive controls into suffixed keys such as
	 * `_tablet` and `_mobile` even when the base control name is the canonical one.
	 *
	 * @param string $key        Candidate setting key.
	 * @param array  $valid_keys Known valid base keys.
	 * @return bool True if the key is a recognized responsive variant.
	 */
	private function is_valid_responsive_setting_key( $key, $valid_keys ) {
		if ( ! preg_match( '/^(.+?)_(mobile|mobile_extra|tablet|tablet_extra|laptop|widescreen)$/', $key, $matches ) ) {
			return false;
		}

		return in_array( $matches[1], $valid_keys, true );
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
	 * @return array Associative array with 'warnings', 'fixes', and 'errors' arrays.
	 */
	private function validate_and_fix_elements( &$elementor_json ) {
		$elements = json_decode( $elementor_json, true );
		if ( ! is_array( $elements ) ) {
			return array(
				'warnings' => array( 'Elementor data is not a valid array.' ),
				'fixes'    => array(),
				'errors'   => array(),
			);
		}

		$warnings          = array();
		$fixes             = array();
		$errors            = array();
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
			&$walk, &$warnings, &$fixes, &$errors, &$changed,
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

			// --- 3b. Auto-set isInner on child containers ---
			if ( 'container' === $el_type && 'container' === $parent_type ) {
				if ( ! isset( $el['settings'] ) || ! is_array( $el['settings'] ) ) {
					$el['settings'] = array();
				}
				if ( empty( $el['settings']['isInner'] ) ) {
					$warnings[] = "{$path}: nested container missing isInner flag (renders as e-parent instead of e-child, breaking flex layout)";
					$el['settings']['isInner'] = true;
					$fixes[]  = "{$path}: auto-set isInner for child container";
					$changed  = true;
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
				$errors[] = "{$path}: widget element missing widgetType";
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

			// --- 6. Flag unknown settings keys using widget reference ---
			if ( 'widget' === $el_type && '' !== $widget_type && ! empty( $el['settings'] ) && is_array( $el['settings'] ) ) {
				$valid_keys = $this->get_valid_widget_keys( $widget_type );
				if ( ! empty( $valid_keys ) ) {
					// Common Elementor internal prefixes that should not trigger warnings.
					$internal_prefixes = array( '_', 'motion_fx_', '__', 'hide_', 'responsive_', 'custom_css' );
					foreach ( array_keys( $el['settings'] ) as $key ) {
						// Skip known valid keys.
						if ( in_array( $key, $valid_keys, true ) || $this->is_valid_responsive_setting_key( $key, $valid_keys ) ) {
							continue;
						}
						// Skip internal/advanced keys.
						$skip = false;
						foreach ( $internal_prefixes as $prefix ) {
							if ( 0 === strpos( $key, $prefix ) ) {
								$skip = true;
								break;
							}
						}
						if ( $skip ) {
							continue;
						}
						// Flag unknown key with valid alternatives.
						$valid_list = implode( ', ', array_slice( $valid_keys, 0, 15 ) );
						$suffix     = count( $valid_keys ) > 15 ? ', ...' : '';
						$warnings[] = "{$path}: unknown key '{$key}' on {$widget_type} widget — valid keys: {$valid_list}{$suffix}";
					}
				}
			}

			// --- 7. Validate elements array ---
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
			'errors'   => $errors,
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
	 * Check Elementor data for RTL-unfriendly patterns.
	 *
	 * Walks the element tree looking for hardcoded LTR alignment, floats,
	 * and one-sided margins/padding that may break on RTL sites.
	 * Returns informational warnings only — no auto-fixes.
	 *
	 * @param string $elementor_json JSON string.
	 * @return array Warning strings.
	 */
	private function check_rtl_issues( $elementor_json ) {
		$elements = json_decode( $elementor_json, true );
		if ( ! is_array( $elements ) ) {
			return array();
		}

		$warnings = array();

		$walk = function ( $el, $path = '' ) use ( &$walk, &$warnings ) {
			$settings = isset( $el['settings'] ) && is_array( $el['settings'] ) ? $el['settings'] : array();

			// Check hardcoded align: left (should use 'start' or 'right' for RTL).
			$align_keys = array( 'align', 'title_align', 'description_align', 'content_align', 'text_align' );
			foreach ( $align_keys as $key ) {
				if ( isset( $settings[ $key ] ) && 'left' === $settings[ $key ] ) {
					$warnings[] = "{$path}: '{$key}' is 'left' — consider 'start' or 'right' for RTL sites";
				}
			}

			// Check custom_css for float: left or one-sided margin/padding.
			if ( ! empty( $settings['custom_css'] ) && is_string( $settings['custom_css'] ) ) {
				$css = $settings['custom_css'];
				if ( preg_match( '/float\s*:\s*left/i', $css ) ) {
					$warnings[] = "{$path}: custom_css contains 'float: left' — may need 'float: right' for RTL";
				}
				if ( preg_match( '/margin-left\s*:/i', $css ) && ! preg_match( '/margin-right\s*:/i', $css ) ) {
					$warnings[] = "{$path}: custom_css has margin-left without margin-right — may need mirroring for RTL";
				}
				if ( preg_match( '/padding-left\s*:/i', $css ) && ! preg_match( '/padding-right\s*:/i', $css ) ) {
					$warnings[] = "{$path}: custom_css has padding-left without padding-right — may need mirroring for RTL";
				}
			}

			// Recurse into children.
			if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
				foreach ( $el['elements'] as $idx => $child ) {
					$child_type = isset( $child['elType'] ) ? $child['elType'] : 'element';
					$walk( $child, $path . '.' . $child_type . "[{$idx}]" );
				}
			}
		};

		foreach ( $elements as $idx => $el ) {
			$top_type = isset( $el['elType'] ) ? $el['elType'] : 'element';
			$walk( $el, "{$top_type}[{$idx}]" );
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

		// SiteGround SG Optimizer — purge both URL and full cache for aggressive configs.
		if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
			sg_cachepress_purge_cache( $url );
		}
		if ( class_exists( '\SiteGround_Optimizer\Supercacher\Supercacher' ) ) {
			\SiteGround_Optimizer\Supercacher\Supercacher::purge_cache();
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
	 * Get the controls schema for a specific widget type.
	 *
	 * @param string $widget_type Widget type name (e.g. 'heading', 'image').
	 * @return array|WP_Error Schema data or error.
	 */
	public function get_widget_schema( $widget_type ) {
		if ( ! $this->is_active() ) {
			return new WP_Error(
				'elementor_not_active',
				__( 'Elementor is not installed or active.', 'site-pilot-ai' ),
				array( 'status' => 400 )
			);
		}

		if ( ! class_exists( '\Elementor\Plugin' ) || empty( \Elementor\Plugin::$instance->widgets_manager ) ) {
			return new WP_Error(
				'elementor_not_loaded',
				__( 'Elementor widget manager not available.', 'site-pilot-ai' ),
				array( 'status' => 500 )
			);
		}

		$manager = \Elementor\Plugin::$instance->widgets_manager;
		$types   = $manager->get_widget_types();

		if ( empty( $widget_type ) ) {
			// List all widget types, enriched with reference data.
			$list = array();
			foreach ( $types as $name => $widget ) {
				$entry = array(
					'name'  => $name,
					'title' => $widget->get_title(),
					'icon'  => $widget->get_icon(),
				);
				$ref = Spai_Elementor_Widgets::get( $name );
				if ( $ref ) {
					$entry['description']   = $ref['description'];
					$entry['category']      = $ref['category'];
					$entry['has_reference'] = true;
				} else {
					$entry['has_reference'] = false;
				}
				$list[] = $entry;
			}
			return array(
				'widgets' => $list,
				'count'   => count( $list ),
				'tip'     => 'Use wp_elementor_widget_help(widget_type) for full offline reference with example JSON and common mistakes.',
			);
		}

		if ( ! isset( $types[ $widget_type ] ) ) {
			$suggestion = $this->find_closest_widget( $widget_type, array_keys( $types ) );
			$msg        = sprintf( __( "Unknown widget type '%s'.", 'site-pilot-ai' ), $widget_type );
			if ( $suggestion ) {
				$msg .= sprintf( __( " Did you mean '%s'?", 'site-pilot-ai' ), $suggestion );
			}
			return new WP_Error(
				'unknown_widget',
				$msg,
				array(
					'status' => 404,
					'hint'   => sprintf(
						'Widget type "%s" does not exist.%s Use wp_get_elementor_widgets to list all available widget types on this site.',
						$widget_type,
						$suggestion ? sprintf( ' Did you mean "%s"?', $suggestion ) : ''
					),
					'guide'  => 'wp_get_guide(topic=\'elementor_widgets\')',
				)
			);
		}

		$widget   = $types[ $widget_type ];
		$controls = $widget->get_controls();
		$grouped  = array();

		foreach ( $controls as $key => $control ) {
			// Skip internal/hidden controls.
			if ( ! empty( $control['is_internal'] ) ) {
				continue;
			}

			$tab = isset( $control['tab'] ) ? $control['tab'] : 'content';
			if ( ! isset( $grouped[ $tab ] ) ) {
				$grouped[ $tab ] = array();
			}

			$entry = array(
				'name'    => $key,
				'type'    => isset( $control['type'] ) ? $control['type'] : 'unknown',
				'label'   => isset( $control['label'] ) ? $control['label'] : $key,
			);

			if ( isset( $control['default'] ) && '' !== $control['default'] ) {
				$entry['default'] = $control['default'];
			}
			if ( ! empty( $control['options'] ) ) {
				$entry['options'] = $control['options'];
			}
			if ( ! empty( $control['selectors'] ) ) {
				$entry['selectors'] = $control['selectors'];
			}

			$grouped[ $tab ][] = $entry;
		}

		$result = array(
			'widget' => $widget_type,
			'title'  => $widget->get_title(),
			'icon'   => $widget->get_icon(),
			'tabs'   => $grouped,
		);

		// Enrich with offline reference data if available.
		$ref = Spai_Elementor_Widgets::get( $widget_type );
		if ( $ref ) {
			$result['description']     = $ref['description'];
			$result['category']        = $ref['category'];
			$result['example']         = $ref['example'];
			$result['common_mistakes'] = $ref['common_mistakes'];
		}

		return $result;
	}

	/**
	 * Regenerate Elementor CSS for a specific page or the entire site.
	 *
	 * @param int|null $page_id Page ID, or null for full site regeneration.
	 * @param bool     $force   If true, delete existing CSS files before regenerating.
	 * @return array|WP_Error Result or error.
	 */
	public function regenerate_css( $page_id = null, $force = false ) {
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
		$upload_dir = wp_upload_dir();
		$css_dir    = $upload_dir['basedir'] . '/elementor/css/';

		if ( $page_id ) {
			$page_id = absint( $page_id );
			$page    = get_post( $page_id );

			if ( ! $page ) {
				return new WP_Error(
					'not_found',
					__( 'Page not found.', 'site-pilot-ai' ),
					array( 'status' => 404 )
				);
			}

			$method = 'cache_clear';

			// Force: delete existing CSS files before regenerating.
			if ( $force ) {
				delete_post_meta( $page_id, '_elementor_css' );
				$old_css_path = $css_dir . 'post-' . $page_id . '.css';
				if ( file_exists( $old_css_path ) ) {
					wp_delete_file( $old_css_path );
				}
			}

			// Regenerate CSS for specific post.
			if ( ! empty( $plugin->documents ) ) {
				$document = $plugin->documents->get( $page_id );
				if ( $document ) {
					$css_file = \Elementor\Core\Files\CSS\Post::create( $page_id );
					$css_file->update();
					$method = 'css_regenerated';
				}
			}

			if ( 'cache_clear' === $method ) {
				$plugin->files_manager->clear_cache();
			}

			// Check resulting CSS file.
			$css_path = $css_dir . 'post-' . $page_id . '.css';
			$css_size = file_exists( $css_path ) ? filesize( $css_path ) : 0;

			// If CSS is empty/tiny, delete meta to force frontend regeneration and prime it.
			$css_deferred = false;
			$css_primed   = false;
			if ( $css_size < 10 && 'css_regenerated' === $method ) {
				delete_post_meta( $page_id, '_elementor_css' );
				$css_deferred = true;
				$permalink = get_permalink( $page_id );
				if ( $permalink ) {
					wp_remote_get(
						add_query_arg( 'spai_prime_css', wp_rand(), $permalink ),
						array(
							'timeout'   => 15,
							'sslverify' => false,
							'blocking'  => false,
						)
					);
					$css_primed = true;
				}
			}

			$has_elementor_data = ! empty( get_post_meta( $page_id, '_elementor_data', true ) );

			$result = array(
				'success'      => true,
				'page_id'      => $page_id,
				'title'        => get_the_title( $page_id ),
				'method'       => $method,
				'force'        => $force,
				'css_file'     => 'post-' . $page_id . '.css',
				'css_size'     => $css_size,
				'css_deferred' => $css_deferred,
				'css_primed'   => $css_primed,
			);

			if ( 'css_regenerated' === $method ) {
				$result['regenerated'] = array( $page_id );
				$result['skipped']     = array();
				$result['message']     = __( 'CSS regenerated for page.', 'site-pilot-ai' );
			} else {
				$result['regenerated'] = array();
				$reason = ! $has_elementor_data ? 'no_elementor_data' : 'document_not_found';
				$result['skipped']     = array(
					array(
						'page_id' => $page_id,
						'reason'  => $reason,
					),
				);
				$result['message'] = __( 'Elementor cache cleared (document not found, CSS will regenerate on next page load).', 'site-pilot-ai' );
			}

			return $result;
		}

		// Full site CSS regeneration — find all Elementor posts first.
		$elementor_posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page', 'elementor_library', 'elementor_snippet' ),
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'meta_key'       => '_elementor_data',
				'posts_per_page' => 200,
				'fields'         => 'ids',
			)
		);

		$plugin->files_manager->clear_cache();

		// Force: delete all existing CSS files.
		if ( $force ) {
			foreach ( $elementor_posts as $pid ) {
				delete_post_meta( $pid, '_elementor_css' );
				$old_css_path = $css_dir . 'post-' . $pid . '.css';
				if ( file_exists( $old_css_path ) ) {
					wp_delete_file( $old_css_path );
				}
			}
		}

		$regenerated = array();
		$skipped     = array();
		$failed      = array();

		foreach ( $elementor_posts as $pid ) {
			if ( ! empty( $plugin->documents ) ) {
				$document = $plugin->documents->get( $pid );
				if ( ! $document ) {
					$skipped[] = array(
						'id'     => $pid,
						'title'  => get_the_title( $pid ),
						'reason' => 'Elementor document not found — page has _elementor_data meta but Elementor cannot load it as a document',
					);
					continue;
				}

				// When not forcing, check if CSS file already exists and is fresh.
				if ( ! $force ) {
					$existing_css_path = $css_dir . 'post-' . $pid . '.css';
					$css_meta          = get_post_meta( $pid, '_elementor_css', true );
					$post_modified     = get_post_modified_time( 'U', true, $pid );

					if ( file_exists( $existing_css_path ) && filesize( $existing_css_path ) > 10 && ! empty( $css_meta ) ) {
						// CSS meta stores the timestamp when CSS was last generated.
						$css_time = is_array( $css_meta ) && isset( $css_meta['time'] ) ? (int) $css_meta['time'] : 0;
						if ( $css_time > 0 && $css_time >= $post_modified ) {
							$skipped[] = array(
								'id'       => $pid,
								'title'    => get_the_title( $pid ),
								'reason'   => 'CSS already up-to-date (generated after last post modification). Use force=true to regenerate anyway.',
								'css_file' => 'post-' . $pid . '.css',
								'css_size' => filesize( $existing_css_path ),
							);
							continue;
						}
					}
				}

				try {
					$css_file = \Elementor\Core\Files\CSS\Post::create( $pid );
					$css_file->update();

					$css_path = $css_dir . 'post-' . $pid . '.css';
					$css_size = file_exists( $css_path ) ? filesize( $css_path ) : 0;

					$regen_entry = array(
						'id'       => $pid,
						'title'    => get_the_title( $pid ),
						'css_file' => 'post-' . $pid . '.css',
						'css_size' => $css_size,
					);

					// If CSS is empty/tiny, delete meta to force frontend regeneration and prime it.
					if ( $css_size < 10 ) {
						delete_post_meta( $pid, '_elementor_css' );
						$regen_entry['css_deferred'] = true;
						$permalink = get_permalink( $pid );
						if ( $permalink ) {
							wp_remote_get(
								add_query_arg( 'spai_prime_css', wp_rand(), $permalink ),
								array(
									'timeout'   => 15,
									'sslverify' => false,
									'blocking'  => false,
								)
							);
							$regen_entry['css_primed'] = true;
						}
					}

					$regenerated[] = $regen_entry;
				} catch ( \Exception $e ) {
					$failed[] = array(
						'id'    => $pid,
						'title' => get_the_title( $pid ),
						'error' => $e->getMessage(),
					);
				}
			}
		}

		// Regenerate the global Elementor kit CSS.
		$global_kit_regenerated = false;
		if ( method_exists( $plugin, 'kits_manager' ) ) {
			$kit_id = $plugin->kits_manager->get_active_id();
			if ( $kit_id ) {
				try {
					$kit_css = \Elementor\Core\Files\CSS\Post::create( $kit_id );
					$kit_css->update();
					$global_kit_regenerated = true;
				} catch ( \Exception $e ) {
					// Kit CSS regeneration failed — not critical.
					$global_kit_regenerated = false;
				}
			}
		}

		$result = array(
			'success'                => true,
			'force'                  => $force,
			'global_kit_regenerated' => $global_kit_regenerated,
			'total_pages'            => count( $elementor_posts ),
			'regenerated_count'      => count( $regenerated ),
			'skipped_count'          => count( $skipped ),
			'failed_count'           => count( $failed ),
			'regenerated'            => $regenerated,
			'skipped'                => $skipped,
			'failed'                 => $failed,
			'message'                => sprintf(
				/* translators: 1: regenerated count 2: total found 3: skipped count 4: failed count 5: global kit status */
				__( 'CSS regenerated for %1$d of %2$d Elementor pages (%3$d skipped, %4$d failed). Global kit CSS: %5$s. Cache cleared.', 'site-pilot-ai' ),
				count( $regenerated ),
				count( $elementor_posts ),
				count( $skipped ),
				count( $failed ),
				$global_kit_regenerated ? 'regenerated' : 'not regenerated'
			),
		);

		return $result;
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

		$save_debug = $this->save_elements_to_page( $page_id, $elements );
		if ( is_wp_error( $save_debug ) ) {
			return $save_debug;
		}

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
		if ( ! empty( $save_debug ) ) {
			$result['css_debug'] = array_intersect_key(
				$save_debug,
				array(
					'css_regenerated' => true,
					'css_file_size'   => true,
					'save_method'     => true,
					'meta_verified'   => true,
					'sections_saved'  => true,
				)
			);
		}

		$all_warnings = array_merge(
			isset( $save_debug['validation_warnings'] ) ? $save_debug['validation_warnings'] : array(),
			isset( $save_debug['validation_fixes'] ) ? $save_debug['validation_fixes'] : array()
		);
		if ( ! empty( $all_warnings ) ) {
			$result['warnings'] = $all_warnings;
		}

		return $result;
	}

	/**
	 * Edit a single widget's settings by widget ID.
	 *
	 * Lightweight alternative to edit_section that targets widgets specifically.
	 * Loads current elementor_data, finds the widget by its 8-char ID, merges
	 * new settings, and saves the updated data.
	 *
	 * @param int    $page_id   Page/post ID.
	 * @param string $widget_id The 8-char Elementor element ID of the widget.
	 * @param array  $settings  Settings to merge into the widget.
	 * @param array  $delete_settings Optional setting keys to remove.
	 * @return array|WP_Error Result with the modified widget, or error.
	 */
	public function edit_widget( $page_id, $widget_id, $settings, $delete_settings = array() ) {
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

		if ( empty( $widget_id ) ) {
			return new WP_Error(
				'missing_widget_id',
				__( 'widget_id is required.', 'site-pilot-ai' ),
				array( 'status' => 400 )
			);
		}

		if ( empty( $settings ) && empty( $delete_settings ) ) {
			return new WP_Error(
				'no_changes',
				__( 'No settings or delete_settings provided.', 'site-pilot-ai' ),
				array( 'status' => 400 )
			);
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

		// Find widget by ID.
		$found_path = '';
		$found      =& $this->find_element_by_id( $elements, (string) $widget_id, $found_path );

		if ( null === $found ) {
			return new WP_Error(
				'widget_not_found',
				sprintf(
					/* translators: %s: widget ID */
					__( 'No element with ID "%s" found in the Elementor tree.', 'site-pilot-ai' ),
					$widget_id
				),
				array( 'status' => 404 )
			);
		}

		// Verify it is actually a widget.
		$el_type = isset( $found['elType'] ) ? $found['elType'] : '';
		if ( 'widget' !== $el_type ) {
			return new WP_Error(
				'not_a_widget',
				sprintf(
					/* translators: 1: element ID, 2: actual elType */
					__( 'Element "%1$s" is a %2$s, not a widget. Use wp_edit_section for non-widget elements.', 'site-pilot-ai' ),
					$widget_id,
					$el_type
				),
				array( 'status' => 400 )
			);
		}

		// Apply settings merge.
		$changes = array();
		if ( ! isset( $found['settings'] ) || ! is_array( $found['settings'] ) ) {
			$found['settings'] = array();
		}

		if ( ! empty( $settings ) && is_array( $settings ) ) {
			foreach ( $settings as $key => $value ) {
				$found['settings'][ $key ] = $value;
				$changes[] = "set {$key}";
			}
		}

		// Delete settings.
		if ( ! empty( $delete_settings ) && is_array( $delete_settings ) ) {
			foreach ( $delete_settings as $key ) {
				if ( isset( $found['settings'][ $key ] ) ) {
					unset( $found['settings'][ $key ] );
					$changes[] = "removed {$key}";
				}
			}
		}

		$save_debug = $this->save_elements_to_page( $page_id, $elements );
		if ( is_wp_error( $save_debug ) ) {
			return $save_debug;
		}

		// Re-read the widget to return its final state.
		$saved_data     = get_post_meta( $page_id, '_elementor_data', true );
		$saved_elements = json_decode( $saved_data, true );
		$saved_widget   = $this->find_element_by_id_readonly( $saved_elements, (string) $widget_id );

		$result = array(
			'success'     => true,
			'page_id'     => $page_id,
			'widget_id'   => $widget_id,
			'widget_type' => isset( $found['widgetType'] ) ? $found['widgetType'] : null,
			'path'        => $found_path,
			'changes'     => $changes,
			'widget'      => $saved_widget ? $saved_widget : $found,
			'edit_url'    => admin_url( "post.php?post={$page_id}&action=elementor" ),
		);
		if ( ! empty( $save_debug ) ) {
			$result['css_debug'] = array_intersect_key(
				$save_debug,
				array(
					'css_regenerated' => true,
					'css_file_size'   => true,
					'save_method'     => true,
					'meta_verified'   => true,
					'sections_saved'  => true,
				)
			);
		}

		$all_warnings = array_merge(
			isset( $save_debug['validation_warnings'] ) ? $save_debug['validation_warnings'] : array(),
			isset( $save_debug['validation_fixes'] ) ? $save_debug['validation_fixes'] : array()
		);
		if ( ! empty( $all_warnings ) ) {
			$result['warnings'] = $all_warnings;
		}

		return $result;
	}

	// -------------------------------------------------------------------------
	// Section-level operations: save helper, add, remove, replace, patch, strip
	// -------------------------------------------------------------------------

	/**
	 * Shared helper: save an element array to a page with validation, CSS regen, and cache clear.
	 *
	 * @param int   $page_id  Page/post ID.
	 * @param array $elements Full top-level element array.
	 * @param array $options  Reserved for future options.
	 * @return array|WP_Error Debug info array or WP_Error on failure.
	 */
	protected function save_elements_to_page( $page_id, $elements, $options = array() ) {
		$debug = array();
		$elementor_json = wp_json_encode( $elements );

		// Count input.
		$input_count = count( $elements );
		$debug['sections_submitted'] = $input_count;

		// Validate and fix (before write).
		$validation = $this->validate_and_fix_elements( $elementor_json );
		if ( ! empty( $validation['errors'] ) ) {
			return new WP_Error( 'invalid_elementor_structure', implode( ' | ', $validation['errors'] ), array( 'status' => 400, 'validation_errors' => $validation['errors'] ) );
		}
		$debug['validation_fixes']    = $validation['fixes'];
		$debug['validation_warnings'] = $validation['warnings'];

		// Safety check: validation didn't corrupt data.
		$post_val = json_decode( $elementor_json, true );
		if ( ! is_array( $post_val ) || count( $post_val ) !== $input_count ) {
			return new WP_Error( 'validation_corrupted', 'Data corrupted during validation.', array( 'status' => 500 ) );
		}

		// Save via Elementor Document API (preferred) or meta fallback.
		$document_saved = false;
		$elementor_ok   = class_exists( '\Elementor\Plugin' );

		if ( $elementor_ok ) {
			$documents_manager = \Elementor\Plugin::$instance->documents;
			if ( $documents_manager && method_exists( $documents_manager, 'get' ) ) {
				wp_cache_delete( $page_id, 'post_meta' );
				clean_post_cache( $page_id );

				$document = $documents_manager->get( $page_id, false );
				if ( $document && method_exists( $document, 'save' ) ) {
					$save_result = $document->save( array( 'elements' => $post_val ) );
					if ( ! is_wp_error( $save_result ) ) {
						// (#198) Always do a direct meta overwrite after Document::save()
						// to guarantee the raw _elementor_data meta matches what we sent.
						// Document::save() may create revisions or write to internal caches
						// that diverge from the post meta on subsequent reads.
						update_post_meta( $page_id, '_elementor_data', wp_slash( $elementor_json ) );

						wp_cache_delete( $page_id, 'post_meta' );
						clean_post_cache( $page_id );

						// Flush persistent object cache if available.
						if ( function_exists( 'wp_cache_flush_group' ) ) {
							wp_cache_flush_group( 'post_meta' );
						}

						$stored_after_document = get_post_meta( $page_id, '_elementor_data', true );
						$decoded_after_document = json_decode( $stored_after_document, true );
						$stored_after_count     = is_array( $decoded_after_document ) ? count( $decoded_after_document ) : 0;

						if ( $stored_after_count === $input_count ) {
							$document_saved       = true;
							$debug['save_method'] = 'document_save_with_meta_overwrite';
						} else {
							$debug['document_save_persist_mismatch'] = array(
								'sections_expected' => $input_count,
								'sections_saved'    => $stored_after_count,
							);
						}
					} else {
						$debug['document_save_error'] = $save_result->get_error_message();
					}
				}
			}
		}

		// Fallback: direct meta write.
		if ( ! $document_saved ) {
			update_post_meta( $page_id, '_elementor_data', wp_slash( $elementor_json ) );
			$debug['save_method'] = 'meta_direct';

			// (#187) Update post_content so front-end renderer doesn't short-circuit.
			wp_update_post( array(
				'ID'           => $page_id,
				'post_content' => '',
			) );

			// (#187) Flush Elementor's in-memory document cache so CSS regen
			// and same-request renders read the new data from DB.
			if ( $elementor_ok && ! empty( \Elementor\Plugin::$instance->documents ) ) {
				clean_post_cache( $page_id );
				wp_cache_delete( $page_id, 'post_meta' );
				\Elementor\Plugin::$instance->documents->get( $page_id, false );
				$debug['document_cache_flushed'] = true;
			}
		}

		$debug['meta_written'] = true;

		// Verify.
		wp_cache_delete( $page_id, 'post_meta' );
		$stored         = get_post_meta( $page_id, '_elementor_data', true );
		$stored_decoded = json_decode( $stored, true );
		$stored_count   = is_array( $stored_decoded ) ? count( $stored_decoded ) : 0;
		$debug['sections_saved'] = $stored_count;
		$debug['meta_verified']  = ( $stored_count === $input_count );

		// CSS regen only needed for meta_direct fallback.
		if ( ! $document_saved && $elementor_ok ) {
			if ( ! empty( \Elementor\Plugin::$instance->files_manager ) ) {
				\Elementor\Plugin::$instance->files_manager->clear_cache();
			}
			delete_post_meta( $page_id, '_elementor_css' );

			if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
				$css_file = \Elementor\Core\Files\CSS\Post::create( $page_id );
				$css_file->update();
				$debug['css_regenerated'] = true;
			}
		}

		// Prime CSS if still empty.
		if ( $elementor_ok ) {
			$upload_dir = wp_upload_dir();
			$css_path   = $upload_dir['basedir'] . '/elementor/css/post-' . $page_id . '.css';
			$css_size   = file_exists( $css_path ) ? filesize( $css_path ) : 0;
			$debug['css_file_size'] = $css_size;
			if ( $css_size < 10 ) {
				delete_post_meta( $page_id, '_elementor_css' );
				$permalink = get_permalink( $page_id );
				if ( $permalink ) {
					wp_remote_get(
						add_query_arg( 'spai_prime_css', wp_rand(), $permalink ),
						array(
							'timeout'   => 15,
							'sslverify' => false,
							'blocking'  => false,
						)
					);
				}
			}
		}

		$this->purge_page_cache( $page_id );

		$debug['next_step'] = sprintf( 'Call wp_get_elementor_summary(id=%d) to verify the page structure.', $page_id );

		return $debug;
	}

	/**
	 * Add a section/container at a specific position on a page.
	 *
	 * @param int   $page_id Page/post ID.
	 * @param array $data    {
	 *     @type array  $element  The element to add (must have elType).
	 *     @type string $position Where to insert: 'start', 'end', 'before:{id}', 'after:{id}'.
	 * }
	 * @return array|WP_Error Result or error.
	 */
	public function add_section( $page_id, $data ) {
		// Validate page exists and has Elementor.
		$post = get_post( $page_id );
		if ( ! $post ) {
			return new WP_Error( 'invalid_page', 'Page not found.', array( 'status' => 404 ) );
		}

		// Get current elements.
		$raw      = get_post_meta( $page_id, '_elementor_data', true );
		$elements = json_decode( $raw, true );
		if ( ! is_array( $elements ) ) {
			$elements = array();
		}

		// Validate new element.
		$new_element = isset( $data['element'] ) ? $data['element'] : null;
		if ( ! is_array( $new_element ) || empty( $new_element['elType'] ) ) {
			return new WP_Error( 'invalid_element', 'element must be an object with elType.', array( 'status' => 400 ) );
		}

		// Auto-generate ID if missing.
		if ( empty( $new_element['id'] ) ) {
			$new_element['id'] = $this->generate_element_id();
		}

		// Parse position.
		$position = isset( $data['position'] ) ? $data['position'] : 'end';
		$inserted = false;

		if ( 'start' === $position ) {
			array_unshift( $elements, $new_element );
			$inserted = true;
		} elseif ( 'end' === $position ) {
			$elements[] = $new_element;
			$inserted = true;
		} elseif ( preg_match( '/^(before|after):(.+)$/', $position, $m ) ) {
			$rel    = $m[1];
			$ref_id = $m[2];
			foreach ( $elements as $idx => $el ) {
				if ( isset( $el['id'] ) && $el['id'] === $ref_id ) {
					$insert_at = ( 'after' === $rel ) ? $idx + 1 : $idx;
					array_splice( $elements, $insert_at, 0, array( $new_element ) );
					$inserted = true;
					break;
				}
			}
			if ( ! $inserted ) {
				return new WP_Error( 'ref_not_found', "Reference element '$ref_id' not found in top-level sections.", array( 'status' => 404 ) );
			}
		} else {
			return new WP_Error( 'invalid_position', "Invalid position '$position'. Use: start, end, before:{id}, after:{id}.", array( 'status' => 400 ) );
		}

		// Save.
		$debug = $this->save_elements_to_page( $page_id, $elements );
		if ( is_wp_error( $debug ) ) {
			return $debug;
		}

		return array(
			'success'       => true,
			'page_id'       => (string) $page_id,
			'element_id'    => $new_element['id'],
			'position'      => $position,
			'section_count' => count( $elements ),
			'debug'         => $debug,
		);
	}

	/**
	 * Remove a section/container/widget by element ID.
	 *
	 * Searches top-level first, then recursively in nested elements.
	 *
	 * @param int   $page_id Page/post ID.
	 * @param array $data    {
	 *     @type string $element_id The Elementor element ID to remove.
	 * }
	 * @return array|WP_Error Result or error.
	 */
	public function remove_section( $page_id, $data ) {
		$post = get_post( $page_id );
		if ( ! $post ) {
			return new WP_Error( 'invalid_page', 'Page not found.', array( 'status' => 404 ) );
		}

		$element_id = isset( $data['element_id'] ) ? $data['element_id'] : '';
		if ( empty( $element_id ) ) {
			return new WP_Error( 'missing_id', 'element_id is required.', array( 'status' => 400 ) );
		}

		$raw      = get_post_meta( $page_id, '_elementor_data', true );
		$elements = json_decode( $raw, true );
		if ( ! is_array( $elements ) ) {
			return new WP_Error( 'no_data', 'Page has no Elementor data.', array( 'status' => 404 ) );
		}

		$before_count = count( $elements );
		$removed      = null;

		// Search top-level first.
		foreach ( $elements as $idx => $el ) {
			if ( isset( $el['id'] ) && $el['id'] === $element_id ) {
				$removed = $el;
				array_splice( $elements, $idx, 1 );
				break;
			}
		}

		if ( ! $removed ) {
			// Search recursively in nested elements.
			$removed = $this->remove_nested_element( $elements, $element_id );
		}

		if ( ! $removed ) {
			return new WP_Error( 'not_found', "Element '$element_id' not found.", array( 'status' => 404 ) );
		}

		$debug = $this->save_elements_to_page( $page_id, $elements );
		if ( is_wp_error( $debug ) ) {
			return $debug;
		}

		return array(
			'success'       => true,
			'page_id'       => (string) $page_id,
			'removed_id'    => $element_id,
			'removed_type'  => isset( $removed['elType'] ) ? $removed['elType'] : 'unknown',
			'section_count' => count( $elements ),
			'debug'         => $debug,
		);
	}

	/**
	 * Recursively remove an element by ID from nested elements arrays.
	 *
	 * @param array  &$elements Element tree (by reference).
	 * @param string $target_id The element ID to remove.
	 * @return array|null The removed element, or null if not found.
	 */
	private function remove_nested_element( &$elements, $target_id ) {
		foreach ( $elements as $idx => &$el ) {
			if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
				foreach ( $el['elements'] as $child_idx => $child ) {
					if ( isset( $child['id'] ) && $child['id'] === $target_id ) {
						$removed = $child;
						array_splice( $el['elements'], $child_idx, 1 );
						return $removed;
					}
				}
				$found = $this->remove_nested_element( $el['elements'], $target_id );
				if ( $found ) {
					return $found;
				}
			}
		}
		unset( $el );
		return null;
	}

	/**
	 * Replace a section/container/widget by element ID.
	 *
	 * Searches top-level first, then recursively in nested elements.
	 *
	 * @param int   $page_id Page/post ID.
	 * @param array $data    {
	 *     @type string $element_id The Elementor element ID to replace.
	 *     @type array  $element    The new element (must have elType).
	 * }
	 * @return array|WP_Error Result or error.
	 */
	public function replace_section( $page_id, $data ) {
		$post = get_post( $page_id );
		if ( ! $post ) {
			return new WP_Error( 'invalid_page', 'Page not found.', array( 'status' => 404 ) );
		}

		$element_id  = isset( $data['element_id'] ) ? $data['element_id'] : '';
		$new_element = isset( $data['element'] ) ? $data['element'] : null;

		if ( empty( $element_id ) ) {
			return new WP_Error( 'missing_id', 'element_id is required.', array( 'status' => 400 ) );
		}
		if ( ! is_array( $new_element ) || empty( $new_element['elType'] ) ) {
			return new WP_Error( 'invalid_element', 'element must be an object with elType.', array( 'status' => 400 ) );
		}

		// Preserve or generate ID.
		if ( empty( $new_element['id'] ) ) {
			$new_element['id'] = $element_id; // keep same ID by default.
		}

		$raw      = get_post_meta( $page_id, '_elementor_data', true );
		$elements = json_decode( $raw, true );
		if ( ! is_array( $elements ) ) {
			return new WP_Error( 'no_data', 'Page has no Elementor data.', array( 'status' => 404 ) );
		}

		$replaced = false;

		// Search top-level.
		foreach ( $elements as $idx => $el ) {
			if ( isset( $el['id'] ) && $el['id'] === $element_id ) {
				$elements[ $idx ] = $new_element;
				$replaced = true;
				break;
			}
		}

		if ( ! $replaced ) {
			// Search recursively.
			$replaced = $this->replace_nested_element( $elements, $element_id, $new_element );
		}

		if ( ! $replaced ) {
			return new WP_Error( 'not_found', "Element '$element_id' not found.", array( 'status' => 404 ) );
		}

		$debug = $this->save_elements_to_page( $page_id, $elements );
		if ( is_wp_error( $debug ) ) {
			return $debug;
		}

		return array(
			'success'       => true,
			'page_id'       => (string) $page_id,
			'replaced_id'   => $element_id,
			'new_id'        => $new_element['id'],
			'section_count' => count( $elements ),
			'debug'         => $debug,
		);
	}

	/**
	 * Recursively replace an element by ID in nested elements arrays.
	 *
	 * @param array  &$elements   Element tree (by reference).
	 * @param string $target_id   The element ID to replace.
	 * @param array  $new_element The replacement element.
	 * @return bool True if replaced, false if not found.
	 */
	private function replace_nested_element( &$elements, $target_id, $new_element ) {
		foreach ( $elements as $idx => &$el ) {
			if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
				foreach ( $el['elements'] as $child_idx => $child ) {
					if ( isset( $child['id'] ) && $child['id'] === $target_id ) {
						$el['elements'][ $child_idx ] = $new_element;
						return true;
					}
				}
				if ( $this->replace_nested_element( $el['elements'], $target_id, $new_element ) ) {
					return true;
				}
			}
		}
		unset( $el );
		return false;
	}

	/**
	 * Batch patch Elementor data: add, remove, replace, or update settings in one request.
	 *
	 * Applies up to 20 operations sequentially, then saves once.
	 *
	 * @param int   $page_id Page/post ID.
	 * @param array $data    {
	 *     @type array $operations Array of operations, each with:
	 *         @type string $op             Operation type: 'add', 'remove', 'replace', 'settings'.
	 *         @type string $element_id     Target element ID (for remove, replace, settings).
	 *         @type array  $element        New element (for add, replace).
	 *         @type string $position       Insert position (for add): 'start', 'end', 'before:{id}', 'after:{id}'.
	 *         @type array  $settings       Settings to merge (for settings op).
	 *         @type array  $delete_settings Setting keys to remove (for settings op).
	 * }
	 * @return array|WP_Error Result with per-operation results, or error.
	 */
	public function patch_elementor( $page_id, $data ) {
		$post = get_post( $page_id );
		if ( ! $post ) {
			return new WP_Error( 'invalid_page', 'Page not found.', array( 'status' => 404 ) );
		}

		$operations = isset( $data['operations'] ) ? $data['operations'] : array();
		if ( empty( $operations ) || ! is_array( $operations ) ) {
			return new WP_Error( 'no_operations', 'operations array is required.', array( 'status' => 400 ) );
		}

		if ( count( $operations ) > 20 ) {
			return new WP_Error( 'too_many_ops', 'Maximum 20 operations per patch.', array( 'status' => 400 ) );
		}

		$raw      = get_post_meta( $page_id, '_elementor_data', true );
		$elements = json_decode( $raw, true );
		if ( ! is_array( $elements ) ) {
			$elements = array();
		}

		$results = array();

		foreach ( $operations as $i => $op ) {
			$type   = isset( $op['op'] ) ? $op['op'] : '';
			$el_id  = isset( $op['element_id'] ) ? $op['element_id'] : '';
			$result = array(
				'op'    => $type,
				'index' => $i,
			);

			switch ( $type ) {
				case 'add':
					$new_el = isset( $op['element'] ) ? $op['element'] : null;
					if ( ! is_array( $new_el ) ) {
						$result['error'] = 'element required';
						break;
					}
					if ( empty( $new_el['id'] ) ) {
						$new_el['id'] = $this->generate_element_id();
					}
					$position = isset( $op['position'] ) ? $op['position'] : 'end';
					if ( 'start' === $position ) {
						array_unshift( $elements, $new_el );
					} elseif ( 'end' === $position ) {
						$elements[] = $new_el;
					} elseif ( preg_match( '/^(before|after):(.+)$/', $position, $m ) ) {
						$found_ref = false;
						foreach ( $elements as $idx => $el ) {
							if ( isset( $el['id'] ) && $el['id'] === $m[2] ) {
								$insert_at = ( 'after' === $m[1] ) ? $idx + 1 : $idx;
								array_splice( $elements, $insert_at, 0, array( $new_el ) );
								$found_ref = true;
								break;
							}
						}
						if ( ! $found_ref ) {
							$result['error'] = "ref '{$m[2]}' not found";
							break;
						}
					}
					$result['success']    = true;
					$result['element_id'] = $new_el['id'];
					break;

				case 'remove':
					if ( empty( $el_id ) ) {
						$result['error'] = 'element_id required';
						break;
					}
					$removed = null;
					foreach ( $elements as $idx => $el ) {
						if ( isset( $el['id'] ) && $el['id'] === $el_id ) {
							$removed = $el;
							array_splice( $elements, $idx, 1 );
							break;
						}
					}
					if ( ! $removed ) {
						$removed = $this->remove_nested_element( $elements, $el_id );
					}
					if ( $removed ) {
						$result['success'] = true;
					} else {
						$result['error'] = "element '$el_id' not found";
					}
					break;

				case 'replace':
					if ( empty( $el_id ) ) {
						$result['error'] = 'element_id required';
						break;
					}
					$new_el = isset( $op['element'] ) ? $op['element'] : null;
					if ( ! is_array( $new_el ) ) {
						$result['error'] = 'element required';
						break;
					}
					if ( empty( $new_el['id'] ) ) {
						$new_el['id'] = $el_id;
					}
					$op_replaced = false;
					foreach ( $elements as $idx => $el ) {
						if ( isset( $el['id'] ) && $el['id'] === $el_id ) {
							$elements[ $idx ] = $new_el;
							$op_replaced = true;
							break;
						}
					}
					if ( ! $op_replaced ) {
						$op_replaced = $this->replace_nested_element( $elements, $el_id, $new_el );
					}
					$result['success'] = $op_replaced;
					if ( ! $op_replaced ) {
						$result['error'] = "element '$el_id' not found";
					}
					break;

				case 'settings':
					if ( empty( $el_id ) ) {
						$result['error'] = 'element_id required';
						break;
					}
					$dummy_path = '';
					$target     =& $this->find_element_by_id( $elements, $el_id, $dummy_path );
					if ( null === $target ) {
						$result['error'] = "element '$el_id' not found";
						break;
					}
					if ( ! isset( $target['settings'] ) || ! is_array( $target['settings'] ) ) {
						$target['settings'] = array();
					}
					if ( ! empty( $op['settings'] ) && is_array( $op['settings'] ) ) {
						foreach ( $op['settings'] as $k => $v ) {
							$target['settings'][ $k ] = $v;
						}
					}
					if ( ! empty( $op['delete_settings'] ) && is_array( $op['delete_settings'] ) ) {
						foreach ( $op['delete_settings'] as $k ) {
							unset( $target['settings'][ $k ] );
						}
					}
					unset( $target );
					$result['success'] = true;
					break;

				default:
					$result['error'] = "unknown op '$type' — use: add, remove, replace, settings";
					break;
			}

			$results[] = $result;
		}

		// Save once.
		$debug = $this->save_elements_to_page( $page_id, $elements );
		if ( is_wp_error( $debug ) ) {
			return $debug;
		}

		return array(
			'success'       => true,
			'page_id'       => (string) $page_id,
			'operations'    => $results,
			'section_count' => count( $elements ),
			'debug'         => $debug,
		);
	}

	/**
	 * Strip default widget settings from an element tree.
	 *
	 * Compares each widget's settings against the known schema defaults
	 * and removes any settings that match the default value, reducing payload size.
	 *
	 * @param array &$elements Element tree (by reference, modified in place).
	 */
	public function strip_element_defaults( &$elements ) {
		foreach ( $elements as &$el ) {
			if ( isset( $el['elType'] ) && 'widget' === $el['elType'] && ! empty( $el['widgetType'] ) ) {
				$schema = Spai_Elementor_Widgets::get( $el['widgetType'] );
				if ( $schema && ! empty( $schema['settings'] ) && ! empty( $el['settings'] ) ) {
					foreach ( $schema['settings'] as $key => $def ) {
						if ( isset( $el['settings'][ $key ] ) && isset( $def['default'] )
							&& $el['settings'][ $key ] === $def['default'] ) {
							unset( $el['settings'][ $key ] );
						}
					}
				}
			}
			if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
				$this->strip_element_defaults( $el['elements'] );
			}
		}
		unset( $el );
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

	/**
	 * Get rendered HTML content for an Elementor page.
	 *
	 * Uses Elementor's frontend builder to render the page content to HTML,
	 * without the full page template/header/footer.
	 *
	 * @since 1.1.22
	 *
	 * @param int $post_id Post ID.
	 * @return array|WP_Error Rendered content data or error.
	 */
	/**
	 * Get rendered HTML preview of Elementor content.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $format  Output format: "summary" (text + stats, no HTML), "text" (full text), "html" (full HTML).
	 * @return array|WP_Error Rendered content or error.
	 */
	public function get_rendered_content( $post_id, $format = 'summary' ) {
		if ( ! $this->is_active() ) {
			return new WP_Error(
				'elementor_not_active',
				__( 'Elementor is not installed or active.', 'site-pilot-ai' ),
				array( 'status' => 400 )
			);
		}

		$post = $this->validate_post( $post_id );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return new WP_Error(
				'elementor_not_loaded',
				__( 'Elementor plugin class not available.', 'site-pilot-ai' ),
				array( 'status' => 500 )
			);
		}

		// Check if the post has Elementor data.
		$elementor_data_raw = get_post_meta( $post_id, '_elementor_data', true );
		if ( empty( $elementor_data_raw ) ) {
			return new WP_Error(
				'no_elementor_data',
				__( 'This page has no Elementor data.', 'site-pilot-ai' ),
				array( 'status' => 404 )
			);
		}

		// Normalise format.
		$format = in_array( $format, array( 'html', 'text', 'summary' ), true ) ? $format : 'summary';

		// Build element statistics from the raw JSON structure.
		$stats = $this->compute_element_stats( $elementor_data_raw );

		// Preview URL.
		$preview_url = get_preview_post_link( $post_id );

		// Use Elementor's frontend to render the content.
		$frontend = \Elementor\Plugin::$instance->frontend;
		$html     = '';

		if ( $frontend ) {
			$html = $frontend->get_builder_content( $post_id, true );
		}

		// If rendering failed, build a fallback text from the raw JSON.
		if ( empty( $html ) ) {
			$fallback_text = $this->extract_text_from_elementor_json( $elementor_data_raw );

			$response = array(
				'id'          => $post_id,
				'title'       => get_the_title( $post_id ),
				'format'      => $format,
				'text'        => $fallback_text ? mb_substr( $fallback_text, 0, 500 ) : '',
				'stats'       => $stats,
				'preview_url' => $preview_url,
				'message'     => __( 'Elementor returned empty HTML. Text extracted from raw data instead. The page may need to be saved in the Elementor editor first.', 'site-pilot-ai' ),
			);

			return $response;
		}

		// Extract plain text from rendered HTML.
		$full_text = $this->html_to_plain_text( $html );

		// Build response based on requested format.
		$response = array(
			'id'          => $post_id,
			'title'       => get_the_title( $post_id ),
			'format'      => $format,
			'stats'       => $stats,
			'preview_url' => $preview_url,
		);

		switch ( $format ) {
			case 'html':
				$response['html'] = $html;
				$response['text'] = $full_text;
				break;

			case 'text':
				$response['text'] = $full_text;
				break;

			case 'summary':
			default:
				// Truncate to ~500 chars for AI consumption.
				$response['text'] = mb_substr( $full_text, 0, 500 );
				if ( mb_strlen( $full_text ) > 500 ) {
					$response['text'] .= '...';
				}
				break;
		}

		return $response;
	}

	/**
	 * Compute element statistics from raw Elementor JSON.
	 *
	 * @param string $elementor_data_raw Raw JSON string.
	 * @return array Stats array with sections, columns, widgets, widget_types, word_count.
	 */
	private function compute_element_stats( $elementor_data_raw ) {
		$elements = json_decode( $elementor_data_raw, true );

		$stats = array(
			'sections'     => 0,
			'columns'      => 0,
			'widgets'      => 0,
			'widget_types' => array(),
			'word_count'   => 0,
		);

		if ( ! is_array( $elements ) ) {
			return $stats;
		}

		$all_text = '';
		$this->walk_elements_for_stats( $elements, $stats, $all_text );

		$stats['widget_types'] = array_values( array_unique( $stats['widget_types'] ) );
		$stats['word_count']   = str_word_count( $all_text );

		return $stats;
	}

	/**
	 * Recursively walk elements to gather stats and text.
	 *
	 * @param array  $elements Elements array.
	 * @param array  $stats    Stats array (by reference).
	 * @param string $all_text Accumulated text (by reference).
	 */
	private function walk_elements_for_stats( $elements, &$stats, &$all_text ) {
		foreach ( $elements as $element ) {
			if ( ! is_array( $element ) || empty( $element['elType'] ) ) {
				continue;
			}

			$el_type = $element['elType'];

			if ( 'section' === $el_type || 'container' === $el_type ) {
				$stats['sections']++;
			} elseif ( 'column' === $el_type ) {
				$stats['columns']++;
			} elseif ( 'widget' === $el_type ) {
				$stats['widgets']++;
				if ( ! empty( $element['widgetType'] ) ) {
					$stats['widget_types'][] = $element['widgetType'];
				}

				// Extract text from common settings.
				$settings  = isset( $element['settings'] ) ? $element['settings'] : array();
				$text_keys = array( 'title', 'editor', 'text', 'description', 'html', 'caption', 'content', 'inner_text', 'prefix', 'suffix', 'before_text', 'after_text', 'highlighted_text', 'rotating_text' );
				foreach ( $text_keys as $key ) {
					if ( ! empty( $settings[ $key ] ) && is_string( $settings[ $key ] ) ) {
						$all_text .= ' ' . wp_strip_all_tags( $settings[ $key ] );
					}
				}
			}

			// Recurse into children.
			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$this->walk_elements_for_stats( $element['elements'], $stats, $all_text );
			}
		}
	}

	/**
	 * Convert HTML to plain text, collapsing whitespace.
	 *
	 * @param string $html HTML string.
	 * @return string Plain text.
	 */
	private function html_to_plain_text( $html ) {
		// Replace block-level tags with newlines for readability.
		$html = preg_replace( '/<\/(p|div|h[1-6]|li|tr|section|article|header|footer|blockquote)>/i', "\n", $html );
		$html = preg_replace( '/<br\s*\/?>/i', "\n", $html );

		// Strip remaining tags.
		$text = wp_strip_all_tags( $html );

		// Decode HTML entities.
		$text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );

		// Collapse whitespace: multiple spaces to single, multiple newlines to double.
		$text = preg_replace( '/[^\S\n]+/', ' ', $text );
		$text = preg_replace( '/\n{3,}/', "\n\n", $text );

		return trim( $text );
	}

	/**
	 * Extract text content from raw Elementor JSON (fallback when rendering fails).
	 *
	 * @param string $elementor_data_raw Raw JSON string.
	 * @return string Extracted text.
	 */
	private function extract_text_from_elementor_json( $elementor_data_raw ) {
		$elements = json_decode( $elementor_data_raw, true );
		if ( ! is_array( $elements ) ) {
			return '';
		}

		$text = '';
		$this->walk_elements_for_text( $elements, $text );

		return trim( $text );
	}

	/**
	 * Recursively extract text from elements.
	 *
	 * @param array  $elements Elements array.
	 * @param string $text     Accumulated text (by reference).
	 */
	private function walk_elements_for_text( $elements, &$text ) {
		foreach ( $elements as $element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}

			$settings  = isset( $element['settings'] ) ? $element['settings'] : array();
			$text_keys = array( 'title', 'editor', 'text', 'description', 'html', 'caption', 'content', 'inner_text', 'prefix', 'suffix', 'before_text', 'after_text', 'highlighted_text', 'rotating_text' );

			foreach ( $text_keys as $key ) {
				if ( ! empty( $settings[ $key ] ) && is_string( $settings[ $key ] ) ) {
					$text .= ' ' . wp_strip_all_tags( $settings[ $key ] );
				}
			}

			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$this->walk_elements_for_text( $element['elements'], $text );
			}
		}
	}

}
