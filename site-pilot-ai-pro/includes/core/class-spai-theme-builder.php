<?php
/**
 * Elementor Theme Builder Handler
 *
 * @package SitePilotAI_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Builder functionality.
 *
 * Manages Elementor Theme Builder locations and display conditions.
 */
class Spai_Theme_Builder {

	/**
	 * Check if Elementor Pro Theme Builder is available.
	 *
	 * @return bool
	 */
	public function is_available() {
		return defined( 'ELEMENTOR_PRO_VERSION' )
			&& class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' );
	}

	/**
	 * Get Theme Builder status.
	 *
	 * @return array Status info.
	 */
	public function get_status() {
		return array(
			'available'        => $this->is_available(),
			'elementor_pro'    => defined( 'ELEMENTOR_PRO_VERSION' ) ? ELEMENTOR_PRO_VERSION : null,
			'locations'        => $this->is_available() ? array_keys( $this->get_locations() ) : array(),
		);
	}

	/**
	 * Get all theme locations.
	 *
	 * @return array Locations with their active templates.
	 */
	public function get_locations() {
		if ( ! $this->is_available() ) {
			return array();
		}

		$locations_manager = \ElementorPro\Modules\ThemeBuilder\Module::instance()->get_locations_manager();
		$locations = $locations_manager->get_locations();

		$result = array();
		foreach ( $locations as $location => $settings ) {
			$result[ $location ] = array(
				'label'           => $settings['label'] ?? $location,
				'multiple'        => $settings['multiple'] ?? false,
				'edit_in_content' => $settings['edit_in_content'] ?? true,
				'active_template' => $this->get_location_template( $location ),
			);
		}

		return $result;
	}

	/**
	 * Get active template for a location.
	 *
	 * @param string $location Location name.
	 * @return array|null Template info or null.
	 */
	public function get_location_template( $location ) {
		if ( ! $this->is_available() ) {
			return null;
		}

		$conditions_manager = \ElementorPro\Modules\ThemeBuilder\Module::instance()->get_conditions_manager();
		$documents = $conditions_manager->get_documents_for_location( $location );

		if ( empty( $documents ) ) {
			return null;
		}

		// Get the first (highest priority) document.
		$document_id = reset( $documents );
		$document = \Elementor\Plugin::instance()->documents->get( $document_id );

		if ( ! $document ) {
			return null;
		}

		return array(
			'id'       => $document_id,
			'title'    => get_the_title( $document_id ),
			'edit_url' => $document->get_edit_url(),
		);
	}

	/**
	 * Get all Theme Builder templates.
	 *
	 * @param array $args Query arguments.
	 * @return array Templates list.
	 */
	public function get_templates( $args = array() ) {
		if ( ! $this->is_available() ) {
			return array();
		}

		$query_args = array(
			'post_type'      => 'elementor_library',
			'posts_per_page' => isset( $args['per_page'] ) ? absint( $args['per_page'] ) : 50,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'     => '_elementor_template_type',
					'value'   => array( 'header', 'footer', 'single', 'single-post', 'single-page', 'archive', 'search-results', 'error-404', 'loop-item' ),
					'compare' => 'IN',
				),
			),
		);

		// Filter by type if specified.
		if ( ! empty( $args['type'] ) ) {
			$query_args['meta_query'] = array(
				array(
					'key'   => '_elementor_template_type',
					'value' => sanitize_text_field( $args['type'] ),
				),
			);
		}

		$templates = get_posts( $query_args );
		$result = array();

		foreach ( $templates as $template ) {
			$result[] = $this->format_template( $template );
		}

		return $result;
	}

	/**
	 * Get single template with conditions.
	 *
	 * @param int $template_id Template ID.
	 * @return array|WP_Error Template data.
	 */
	public function get_template( $template_id ) {
		if ( ! $this->is_available() ) {
			return new WP_Error( 'not_available', __( 'Elementor Pro Theme Builder is not available.', 'site-pilot-ai-pro' ) );
		}

		$template = get_post( $template_id );

		if ( ! $template || 'elementor_library' !== $template->post_type ) {
			return new WP_Error( 'not_found', __( 'Template not found.', 'site-pilot-ai-pro' ) );
		}

		$data = $this->format_template( $template );
		$data['conditions'] = $this->get_template_conditions( $template_id );

		return $data;
	}

	/**
	 * Format template for response.
	 *
	 * @param WP_Post $template Template post.
	 * @return array Formatted template.
	 */
	private function format_template( $template ) {
		$type = get_post_meta( $template->ID, '_elementor_template_type', true );
		$document = \Elementor\Plugin::instance()->documents->get( $template->ID );

		return array(
			'id'       => $template->ID,
			'title'    => $template->post_title,
			'type'     => $type,
			'location' => $this->get_location_for_type( $type ),
			'status'   => $template->post_status,
			'created'  => $template->post_date,
			'modified' => $template->post_modified,
			'edit_url' => $document ? $document->get_edit_url() : admin_url( 'post.php?post=' . $template->ID . '&action=elementor' ),
		);
	}

	/**
	 * Get location name for template type.
	 *
	 * @param string $type Template type.
	 * @return string Location name.
	 */
	private function get_location_for_type( $type ) {
		$map = array(
			'header'         => 'header',
			'footer'         => 'footer',
			'single'         => 'single',
			'single-post'    => 'single',
			'single-page'    => 'single',
			'archive'        => 'archive',
			'search-results' => 'archive',
			'error-404'      => 'single',
			'loop-item'      => 'loop-item',
		);

		return isset( $map[ $type ] ) ? $map[ $type ] : $type;
	}

	/**
	 * Get conditions for a template.
	 *
	 * @param int $template_id Template ID.
	 * @return array Conditions.
	 */
	public function get_template_conditions( $template_id ) {
		if ( ! $this->is_available() ) {
			return array();
		}

		$conditions_manager = \ElementorPro\Modules\ThemeBuilder\Module::instance()->get_conditions_manager();
		$document = \Elementor\Plugin::instance()->documents->get( $template_id );

		if ( ! $document ) {
			return array();
		}

		$conditions = $conditions_manager->get_document_conditions( $document );

		// Format conditions for API response.
		$formatted = array();
		foreach ( $conditions as $condition ) {
			$formatted[] = array(
				'type'        => $condition['type'] ?? 'include',
				'name'        => $condition['name'] ?? '',
				'sub_name'    => $condition['sub_name'] ?? '',
				'sub_id'      => $condition['sub_id'] ?? '',
			);
		}

		return $formatted;
	}

	/**
	 * Set conditions for a template.
	 *
	 * @param int   $template_id Template ID.
	 * @param array $conditions  Conditions to set.
	 * @return array|WP_Error Updated conditions or error.
	 */
	public function set_template_conditions( $template_id, $conditions ) {
		if ( ! $this->is_available() ) {
			return new WP_Error( 'not_available', __( 'Elementor Pro Theme Builder is not available.', 'site-pilot-ai-pro' ) );
		}

		$document = \Elementor\Plugin::instance()->documents->get( $template_id );

		if ( ! $document ) {
			return new WP_Error( 'not_found', __( 'Template not found.', 'site-pilot-ai-pro' ) );
		}

		// Validate and format conditions.
		$formatted_conditions = array();
		foreach ( $conditions as $condition ) {
			$formatted = array(
				'type'     => isset( $condition['type'] ) && 'exclude' === $condition['type'] ? 'exclude' : 'include',
				'name'     => isset( $condition['name'] ) ? sanitize_text_field( $condition['name'] ) : 'general',
				'sub_name' => isset( $condition['sub_name'] ) ? sanitize_text_field( $condition['sub_name'] ) : '',
				'sub_id'   => isset( $condition['sub_id'] ) ? sanitize_text_field( $condition['sub_id'] ) : '',
			);
			$formatted_conditions[] = $formatted;
		}

		// Save conditions.
		$conditions_manager = \ElementorPro\Modules\ThemeBuilder\Module::instance()->get_conditions_manager();
		$conditions_manager->save_conditions( $template_id, $formatted_conditions );

		// Clear cache.
		\ElementorPro\Modules\ThemeBuilder\Module::instance()->get_conditions_manager()->get_cache()->regenerate();

		return $this->get_template_conditions( $template_id );
	}

	/**
	 * Assign template to location (shortcut for common conditions).
	 *
	 * @param int    $template_id Template ID.
	 * @param string $location    Location (header, footer, single, archive).
	 * @param string $scope       Scope (entire_site, singular, archive, specific).
	 * @param array  $options     Additional options (post_type, post_ids, etc.).
	 * @return array|WP_Error Result.
	 */
	public function assign_to_location( $template_id, $location, $scope = 'entire_site', $options = array() ) {
		$conditions = array();

		switch ( $scope ) {
			case 'entire_site':
				$conditions[] = array(
					'type' => 'include',
					'name' => 'general',
				);
				break;

			case 'singular':
				$post_type = isset( $options['post_type'] ) ? $options['post_type'] : 'post';
				$conditions[] = array(
					'type'     => 'include',
					'name'     => 'singular',
					'sub_name' => $post_type,
				);
				break;

			case 'archive':
				$archive_type = isset( $options['archive_type'] ) ? $options['archive_type'] : '';
				$conditions[] = array(
					'type'     => 'include',
					'name'     => 'archive',
					'sub_name' => $archive_type,
				);
				break;

			case 'specific':
				// Specific posts/pages by ID.
				$post_ids = isset( $options['post_ids'] ) ? (array) $options['post_ids'] : array();
				foreach ( $post_ids as $post_id ) {
					$post = get_post( $post_id );
					if ( $post ) {
						$conditions[] = array(
							'type'     => 'include',
							'name'     => 'singular',
							'sub_name' => $post->post_type,
							'sub_id'   => (string) $post_id,
						);
					}
				}
				break;

			case 'front_page':
				$conditions[] = array(
					'type'     => 'include',
					'name'     => 'singular',
					'sub_name' => 'front_page',
				);
				break;

			case '404':
				$conditions[] = array(
					'type'     => 'include',
					'name'     => 'singular',
					'sub_name' => 'not_found404',
				);
				break;
		}

		if ( empty( $conditions ) ) {
			return new WP_Error( 'invalid_scope', __( 'Invalid scope specified.', 'site-pilot-ai-pro' ) );
		}

		return $this->set_template_conditions( $template_id, $conditions );
	}

	/**
	 * Remove template from all locations.
	 *
	 * @param int $template_id Template ID.
	 * @return bool|WP_Error True on success.
	 */
	public function remove_from_locations( $template_id ) {
		return $this->set_template_conditions( $template_id, array() );
	}

	/**
	 * Get available condition options.
	 *
	 * @return array Available conditions.
	 */
	public function get_available_conditions() {
		return array(
			'scopes' => array(
				'entire_site' => __( 'Entire Site', 'site-pilot-ai-pro' ),
				'singular'    => __( 'Singular', 'site-pilot-ai-pro' ),
				'archive'     => __( 'Archive', 'site-pilot-ai-pro' ),
				'specific'    => __( 'Specific Pages/Posts', 'site-pilot-ai-pro' ),
				'front_page'  => __( 'Front Page', 'site-pilot-ai-pro' ),
				'404'         => __( '404 Page', 'site-pilot-ai-pro' ),
			),
			'post_types' => get_post_types( array( 'public' => true ), 'objects' ),
			'taxonomies' => get_taxonomies( array( 'public' => true ), 'objects' ),
		);
	}
}
