<?php
/**
 * Elementor Pro Handler
 *
 * @package SitePilotAI_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Pro functionality.
 *
 * Provides advanced Elementor features including templates,
 * landing pages, cloning, and widget management.
 */
class Spai_Elementor_Pro {

	/**
	 * Check if Elementor is active.
	 *
	 * @return bool
	 */
	public function is_elementor_active() {
		return did_action( 'elementor/loaded' );
	}

	/**
	 * Check if Elementor Pro is active.
	 *
	 * @return bool
	 */
	public function is_elementor_pro_active() {
		return defined( 'ELEMENTOR_PRO_VERSION' );
	}

	/**
	 * Save Elementor data using Document::save() API with meta fallback.
	 *
	 * @param int          $post_id  Post/page/template ID.
	 * @param array|string $elements Elementor elements array or JSON string.
	 * @return string Save method used: 'document_save' or 'meta_direct'.
	 */
	private function save_elementor_data( $post_id, $elements ) {
		if ( is_string( $elements ) ) {
			$elements = json_decode( $elements, true );
		}

		$elementor_ok = class_exists( '\Elementor\Plugin' ) && ! empty( \Elementor\Plugin::$instance->documents );

		if ( $elementor_ok ) {
			wp_cache_delete( $post_id, 'post_meta' );
			clean_post_cache( $post_id );
			$document = \Elementor\Plugin::$instance->documents->get( $post_id, false );
			if ( $document && method_exists( $document, 'save' ) ) {
				$result = $document->save( array( 'elements' => $elements ) );
				if ( ! is_wp_error( $result ) ) {
					return 'document_save';
				}
			}
		}

		// Fallback: direct meta write.
		$json = is_string( $elements ) ? $elements : wp_json_encode( $elements );
		update_post_meta( $post_id, '_elementor_data', wp_slash( $json ) );

		// Manual CSS regeneration.
		if ( class_exists( '\Elementor\Plugin' ) && ! empty( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
		delete_post_meta( $post_id, '_elementor_css' );
		wp_cache_delete( $post_id, 'post_meta' );
		clean_post_cache( $post_id );
		if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			$css_file = \Elementor\Core\Files\CSS\Post::create( $post_id );
			$css_file->update();
		}

		return 'meta_direct';
	}

	/**
	 * Check if templates are supported.
	 *
	 * @return bool
	 */
	public function supports_templates() {
		return $this->is_elementor_active();
	}

	/**
	 * Check if landing pages are supported.
	 *
	 * @return bool
	 */
	public function supports_landing_pages() {
		return $this->is_elementor_pro_active();
	}

	/**
	 * Check if globals are supported.
	 *
	 * @return bool
	 */
	public function supports_globals() {
		return $this->is_elementor_pro_active();
	}

	/**
	 * Get available widgets, or full controls for a specific widget.
	 *
	 * @param string|null $widget_name Optional widget type name to get controls for.
	 * @return array|WP_Error List of widgets or single widget with controls.
	 */
	public function get_available_widgets( $widget_name = null ) {
		if ( ! $this->is_elementor_active() ) {
			return array();
		}

		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return array();
		}

		$widget_manager = \Elementor\Plugin::instance()->widgets_manager;
		if ( ! $widget_manager ) {
			return array();
		}

		// Single widget with controls.
		if ( $widget_name ) {
			$widget = $widget_manager->get_widget_types( $widget_name );
			if ( ! $widget ) {
				return new \WP_Error( 'widget_not_found', sprintf( 'Widget "%s" not found.', $widget_name ), array( 'status' => 404 ) );
			}

			$controls = $widget->get_controls();
			$formatted = array();
			foreach ( $controls as $key => $control ) {
				$item = array(
					'type'  => isset( $control['type'] ) ? $control['type'] : '',
					'label' => isset( $control['label'] ) ? $control['label'] : '',
				);
				if ( ! empty( $control['default'] ) ) {
					$item['default'] = $control['default'];
				}
				if ( ! empty( $control['options'] ) ) {
					$item['options'] = $control['options'];
				}
				if ( ! empty( $control['selectors'] ) ) {
					$item['selectors'] = $control['selectors'];
				}
				$formatted[ $key ] = $item;
			}

			return array(
				'name'     => $widget->get_name(),
				'title'    => $widget->get_title(),
				'icon'     => $widget->get_icon(),
				'category' => $widget->get_categories(),
				'controls' => $formatted,
			);
		}

		// List all widgets.
		$widgets    = array();
		$registered = $widget_manager->get_widget_types();
		foreach ( $registered as $widget ) {
			$widgets[] = array(
				'name'     => $widget->get_name(),
				'title'    => $widget->get_title(),
				'icon'     => $widget->get_icon(),
				'category' => $widget->get_categories(),
			);
		}

		return $widgets;
	}

	/**
	 * Get all Elementor templates.
	 *
	 * @param array $args Query arguments.
	 * @return array Templates list.
	 */
	public function get_templates( $args = array() ) {
		if ( ! $this->is_elementor_active() ) {
			return array();
		}

		$defaults = array(
			'post_type'      => 'elementor_library',
			'posts_per_page' => 50,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		);

		// Filter by template type if specified.
		if ( ! empty( $args['type'] ) ) {
			$defaults['meta_query'] = array(
				array(
					'key'   => '_elementor_template_type',
					'value' => sanitize_text_field( $args['type'] ),
				),
			);
		}

		$query_args = wp_parse_args( $args, $defaults );
		$templates  = get_posts( $query_args );

		$result = array();
		foreach ( $templates as $template ) {
			$result[] = $this->format_template( $template );
		}

		return $result;
	}

	/**
	 * Get single template.
	 *
	 * @param int $template_id Template ID.
	 * @return array|WP_Error Template data.
	 */
	public function get_template( $template_id ) {
		if ( ! $this->is_elementor_active() ) {
			return new WP_Error( 'elementor_inactive', __( 'Elementor is not active.', 'site-pilot-ai' ) );
		}

		$template = get_post( $template_id );

		if ( ! $template || 'elementor_library' !== $template->post_type ) {
			return new WP_Error( 'not_found', __( 'Template not found.', 'site-pilot-ai' ) );
		}

		return $this->format_template( $template, true );
	}

	/**
	 * Format template for API response.
	 *
	 * @param WP_Post $template    Template post.
	 * @param bool    $include_data Include Elementor data.
	 * @return array Formatted template.
	 */
	private function format_template( $template, $include_data = false ) {
		$data = array(
			'id'         => $template->ID,
			'title'      => $template->post_title,
			'slug'       => $template->post_name,
			'type'       => get_post_meta( $template->ID, '_elementor_template_type', true ),
			'created'    => $template->post_date,
			'modified'   => $template->post_modified,
			'edit_url'   => admin_url( 'post.php?post=' . $template->ID . '&action=elementor' ),
		);

		if ( $include_data ) {
			$data['elementor_data'] = get_post_meta( $template->ID, '_elementor_data', true );
			$data['page_settings']  = get_post_meta( $template->ID, '_elementor_page_settings', true );
		}

		return $data;
	}

	/**
	 * Create a new template.
	 *
	 * @param array $data Template data.
	 * @return array|WP_Error Created template or error.
	 */
	public function create_template( $data ) {
		if ( ! $this->is_elementor_active() ) {
			return new WP_Error( 'elementor_inactive', __( 'Elementor is not active.', 'site-pilot-ai' ) );
		}

		$title = ! empty( $data['title'] ) ? sanitize_text_field( $data['title'] ) : __( 'Untitled Template', 'site-pilot-ai' );
		$type  = ! empty( $data['type'] ) ? sanitize_text_field( $data['type'] ) : 'page';

		// Valid template types.
		$valid_types = array( 'page', 'section', 'header', 'footer', 'single', 'archive', 'popup', 'loop-item' );
		if ( ! in_array( $type, $valid_types, true ) ) {
			$type = 'page';
		}

		$post_data = array(
			'post_title'  => $title,
			'post_status' => 'publish',
			'post_type'   => 'elementor_library',
		);

		$template_id = wp_insert_post( $post_data );

		if ( is_wp_error( $template_id ) ) {
			return $template_id;
		}

		// Set template type.
		update_post_meta( $template_id, '_elementor_template_type', $type );
		update_post_meta( $template_id, '_elementor_edit_mode', 'builder' );

		// Set Elementor data if provided.
		if ( ! empty( $data['elementor_data'] ) ) {
			$this->save_elementor_data( $template_id, $data['elementor_data'] );
		}

		return $this->get_template( $template_id );
	}

	/**
	 * Update a template.
	 *
	 * @param int   $template_id Template ID.
	 * @param array $data        Update data.
	 * @return array|WP_Error Updated template or error.
	 */
	public function update_template( $template_id, $data ) {
		$template = get_post( $template_id );

		if ( ! $template || 'elementor_library' !== $template->post_type ) {
			return new WP_Error( 'not_found', __( 'Template not found.', 'site-pilot-ai' ) );
		}

		// Update title if provided.
		if ( ! empty( $data['title'] ) ) {
			wp_update_post( array(
				'ID'         => $template_id,
				'post_title' => sanitize_text_field( $data['title'] ),
			) );
		}

		// Update Elementor data if provided.
		if ( ! empty( $data['elementor_data'] ) ) {
			$this->save_elementor_data( $template_id, $data['elementor_data'] );
		}

		return $this->get_template( $template_id );
	}

	/**
	 * Delete a template.
	 *
	 * @param int  $template_id Template ID.
	 * @param bool $force       Force delete (bypass trash).
	 * @return bool|WP_Error True on success, error on failure.
	 */
	public function delete_template( $template_id, $force = false ) {
		$template = get_post( $template_id );

		if ( ! $template || 'elementor_library' !== $template->post_type ) {
			return new WP_Error( 'not_found', __( 'Template not found.', 'site-pilot-ai' ) );
		}

		$result = wp_delete_post( $template_id, $force );

		if ( ! $result ) {
			return new WP_Error( 'delete_failed', __( 'Failed to delete template.', 'site-pilot-ai' ) );
		}

		return true;
	}

	/**
	 * Clone a page with Elementor data.
	 *
	 * @param int   $source_id Source page/post ID.
	 * @param array $args      Clone arguments.
	 * @return array|WP_Error Cloned page data or error.
	 */
	public function clone_page( $source_id, $args = array() ) {
		$source = get_post( $source_id );

		if ( ! $source ) {
			return new WP_Error( 'not_found', __( 'Source page not found.', 'site-pilot-ai' ) );
		}

		$title  = ! empty( $args['title'] ) ? sanitize_text_field( $args['title'] ) : $source->post_title . ' (Copy)';
		$status = ! empty( $args['status'] ) ? sanitize_text_field( $args['status'] ) : 'draft';

		// Create new post.
		$new_post = array(
			'post_title'   => $title,
			'post_content' => $source->post_content,
			'post_excerpt' => $source->post_excerpt,
			'post_status'  => $status,
			'post_type'    => $source->post_type,
			'post_author'  => get_current_user_id(),
		);

		if ( ! empty( $args['parent'] ) ) {
			$new_post['post_parent'] = absint( $args['parent'] );
		}

		$new_id = wp_insert_post( $new_post );

		if ( is_wp_error( $new_id ) ) {
			return $new_id;
		}

		// Copy all post meta.
		$meta = get_post_meta( $source_id );
		foreach ( $meta as $key => $values ) {
			foreach ( $values as $value ) {
				add_post_meta( $new_id, $key, maybe_unserialize( $value ) );
			}
		}

		// Copy taxonomies.
		$taxonomies = get_object_taxonomies( $source->post_type );
		foreach ( $taxonomies as $taxonomy ) {
			$terms = wp_get_object_terms( $source_id, $taxonomy, array( 'fields' => 'ids' ) );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				wp_set_object_terms( $new_id, $terms, $taxonomy );
			}
		}

		// Get the cloned page with Elementor data.
		$result = array(
			'id'             => $new_id,
			'title'          => $title,
			'status'         => $status,
			'source_id'      => $source_id,
			'url'            => get_permalink( $new_id ),
			'edit_url'       => admin_url( 'post.php?post=' . $new_id . '&action=elementor' ),
			'elementor_data' => get_post_meta( $new_id, '_elementor_data', true ),
		);

		return $result;
	}

	/**
	 * Create a landing page with Elementor.
	 *
	 * @param array $data Landing page data.
	 * @return array|WP_Error Created page data or error.
	 */
	public function create_landing_page( $data ) {
		if ( ! $this->is_elementor_active() ) {
			return new WP_Error( 'elementor_inactive', __( 'Elementor is not active.', 'site-pilot-ai' ) );
		}

		$title = ! empty( $data['title'] ) ? sanitize_text_field( $data['title'] ) : __( 'Landing Page', 'site-pilot-ai' );

		// Create the page.
		$page_data = array(
			'post_title'  => $title,
			'post_status' => ! empty( $data['status'] ) ? sanitize_text_field( $data['status'] ) : 'draft',
			'post_type'   => 'page',
			'post_author' => get_current_user_id(),
		);

		$page_id = wp_insert_post( $page_data );

		if ( is_wp_error( $page_id ) ) {
			return $page_id;
		}

		// Set Elementor template.
		update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );
		update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );

		// Build Elementor structure if sections provided.
		if ( ! empty( $data['sections'] ) ) {
			$elementor_data = $this->build_landing_page_structure( $data['sections'] );
			$this->save_elementor_data( $page_id, $elementor_data );
		} elseif ( ! empty( $data['elementor_data'] ) ) {
			$this->save_elementor_data( $page_id, $data['elementor_data'] );
		}

		// Apply template if specified.
		if ( ! empty( $data['template_id'] ) ) {
			$this->apply_template_to_page( $page_id, absint( $data['template_id'] ) );
		}

		return array(
			'id'             => $page_id,
			'title'          => $title,
			'status'         => get_post_status( $page_id ),
			'url'            => get_permalink( $page_id ),
			'edit_url'       => admin_url( 'post.php?post=' . $page_id . '&action=elementor' ),
			'elementor_data' => get_post_meta( $page_id, '_elementor_data', true ),
		);
	}

	/**
	 * Build landing page structure from section definitions.
	 *
	 * @param array $sections Section definitions.
	 * @return array Elementor data structure.
	 */
	private function build_landing_page_structure( $sections ) {
		$data = array();

		foreach ( $sections as $section ) {
			$section_id = $this->generate_element_id();
			$section_data = array(
				'id'       => $section_id,
				'elType'   => 'section',
				'settings' => array(),
				'elements' => array(),
			);

			// Apply section settings.
			if ( ! empty( $section['settings'] ) ) {
				$section_data['settings'] = $section['settings'];
			}

			// Add columns.
			$columns = ! empty( $section['columns'] ) ? $section['columns'] : array( array() );
			foreach ( $columns as $column ) {
				$column_id   = $this->generate_element_id();
				$column_data = array(
					'id'       => $column_id,
					'elType'   => 'column',
					'settings' => ! empty( $column['settings'] ) ? $column['settings'] : array(
						'_column_size' => floor( 100 / count( $columns ) ),
					),
					'elements' => array(),
				);

				// Add widgets to column.
				if ( ! empty( $column['widgets'] ) ) {
					foreach ( $column['widgets'] as $widget ) {
						$widget_id   = $this->generate_element_id();
						$widget_data = array(
							'id'         => $widget_id,
							'elType'     => 'widget',
							'widgetType' => $widget['type'],
							'settings'   => ! empty( $widget['settings'] ) ? $widget['settings'] : array(),
						);
						$column_data['elements'][] = $widget_data;
					}
				}

				$section_data['elements'][] = $column_data;
			}

			$data[] = $section_data;
		}

		return $data;
	}

	/**
	 * Apply a template to a page.
	 *
	 * @param int $page_id     Target page ID.
	 * @param int $template_id Template ID.
	 * @return bool|WP_Error True on success, error on failure.
	 */
	public function apply_template_to_page( $page_id, $template_id ) {
		$template = get_post( $template_id );

		if ( ! $template || 'elementor_library' !== $template->post_type ) {
			return new WP_Error( 'not_found', __( 'Template not found.', 'site-pilot-ai' ) );
		}

		// Get template data.
		$template_data = get_post_meta( $template_id, '_elementor_data', true );

		if ( empty( $template_data ) ) {
			return new WP_Error( 'empty_template', __( 'Template has no content.', 'site-pilot-ai' ) );
		}

		// Set required meta for frontend rendering.
		update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );

		$post_type  = get_post_type( $page_id );
		$type_value = ( 'elementor_library' === $post_type ) ? 'section' : 'wp-page';
		update_post_meta( $page_id, '_elementor_template_type', $type_value );

		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			update_post_meta( $page_id, '_elementor_version', ELEMENTOR_VERSION );
		}
		if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			update_post_meta( $page_id, '_elementor_pro_version', ELEMENTOR_PRO_VERSION );
		}

		// Save via Document::save() for proper frontend rendering.
		$this->save_elementor_data( $page_id, $template_data );

		return true;
	}

	/**
	 * Get global colors and fonts (Elementor Pro).
	 *
	 * @return array|WP_Error Global settings or error.
	 */
	public function get_globals() {
		if ( ! $this->is_elementor_pro_active() ) {
			return new WP_Error( 'pro_required', __( 'Elementor Pro is required for global settings.', 'site-pilot-ai' ) );
		}

		$globals = array(
			'colors' => array(),
			'fonts'  => array(),
		);

		// Get kit settings.
		if ( class_exists( '\Elementor\Plugin' ) ) {
			$kit = \Elementor\Plugin::instance()->kits_manager->get_active_kit();
			if ( $kit ) {
				$kit_settings = $kit->get_settings();

				// Extract global colors.
				if ( ! empty( $kit_settings['custom_colors'] ) ) {
					$globals['colors'] = $kit_settings['custom_colors'];
				}

				// Extract global fonts.
				if ( ! empty( $kit_settings['custom_typography'] ) ) {
					$globals['fonts'] = $kit_settings['custom_typography'];
				}
			}
		}

		return $globals;
	}

	/**
	 * Generate unique element ID.
	 *
	 * @return string Element ID.
	 */
	private function generate_element_id() {
		return substr( md5( uniqid( wp_rand(), true ) ), 0, 8 );
	}
}
