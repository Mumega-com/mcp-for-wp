<?php
/**
 * Elementor Pro Handler
 *
 * @package SitePilotAI
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

	public function is_elementor_active() {
		return did_action( 'elementor/loaded' );
	}

	public function is_elementor_pro_active() {
		return defined( 'ELEMENTOR_PRO_VERSION' );
	}

	public function supports_templates() {
		return $this->is_elementor_active();
	}

	public function supports_landing_pages() {
		return $this->is_elementor_pro_active();
	}

	public function supports_globals() {
		return $this->is_elementor_pro_active();
	}

	public function get_available_widgets() {
		if ( ! $this->is_elementor_active() ) {
			return array();
		}

		$widgets = array();

		if ( class_exists( '\\Elementor\\Plugin' ) ) {
			$widget_manager = \Elementor\Plugin::instance()->widgets_manager;
			if ( $widget_manager ) {
				$registered = $widget_manager->get_widget_types();
				foreach ( $registered as $widget ) {
					$widgets[] = array(
						'name'     => $widget->get_name(),
						'title'    => $widget->get_title(),
						'icon'     => $widget->get_icon(),
						'category' => $widget->get_categories(),
					);
				}
			}
		}

		return $widgets;
	}

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

	private function format_template( $template, $include_data = false ) {
		$data = array(
			'id'       => $template->ID,
			'title'    => $template->post_title,
			'slug'     => $template->post_name,
			'type'     => get_post_meta( $template->ID, '_elementor_template_type', true ),
			'created'  => $template->post_date,
			'modified' => $template->post_modified,
			'edit_url' => admin_url( 'post.php?post=' . $template->ID . '&action=elementor' ),
		);

		if ( $include_data ) {
			$data['elementor_data'] = get_post_meta( $template->ID, '_elementor_data', true );
			$data['page_settings']  = get_post_meta( $template->ID, '_elementor_page_settings', true );
		}

		return $data;
	}

	public function create_template( $data ) {
		if ( ! $this->is_elementor_active() ) {
			return new WP_Error( 'elementor_inactive', __( 'Elementor is not active.', 'site-pilot-ai' ) );
		}

		$title = ! empty( $data['title'] ) ? sanitize_text_field( $data['title'] ) : __( 'Untitled Template', 'site-pilot-ai' );
		$type  = ! empty( $data['type'] ) ? sanitize_text_field( $data['type'] ) : 'page';

		$valid_types = array( 'page', 'section', 'header', 'footer', 'single', 'archive', 'popup', 'loop-item' );
		if ( ! in_array( $type, $valid_types, true ) ) {
			$type = 'page';
		}

		$template_id = wp_insert_post(
			array(
				'post_title'  => $title,
				'post_status' => 'publish',
				'post_type'   => 'elementor_library',
			)
		);

		if ( is_wp_error( $template_id ) ) {
			return $template_id;
		}

		update_post_meta( $template_id, '_elementor_template_type', $type );
		update_post_meta( $template_id, '_elementor_edit_mode', 'builder' );

		if ( ! empty( $data['elementor_data'] ) ) {
			$elementor_data = $data['elementor_data'];
			if ( is_array( $elementor_data ) ) {
				$elementor_data = wp_json_encode( $elementor_data );
			}
			update_post_meta( $template_id, '_elementor_data', wp_slash( $elementor_data ) );
		}

		return $this->get_template( $template_id );
	}

	public function update_template( $template_id, $data ) {
		$template = get_post( $template_id );

		if ( ! $template || 'elementor_library' !== $template->post_type ) {
			return new WP_Error( 'not_found', __( 'Template not found.', 'site-pilot-ai' ) );
		}

		if ( ! empty( $data['title'] ) ) {
			wp_update_post(
				array(
					'ID'         => $template_id,
					'post_title' => sanitize_text_field( $data['title'] ),
				)
			);
		}

		if ( ! empty( $data['elementor_data'] ) ) {
			$elementor_data = $data['elementor_data'];
			if ( is_array( $elementor_data ) ) {
				$elementor_data = wp_json_encode( $elementor_data );
			}
			update_post_meta( $template_id, '_elementor_data', wp_slash( $elementor_data ) );
		}

		return $this->get_template( $template_id );
	}

	public function delete_template( $template_id, $force = false ) {
		$template = get_post( $template_id );

		if ( ! $template || 'elementor_library' !== $template->post_type ) {
			return new WP_Error( 'not_found', __( 'Template not found.', 'site-pilot-ai' ) );
		}

		$result = wp_delete_post( $template_id, (bool) $force );

		if ( ! $result ) {
			return new WP_Error( 'delete_failed', __( 'Unable to delete template.', 'site-pilot-ai' ) );
		}

		return true;
	}

	public function apply_template( $template_id, $page_id ) {
		if ( ! $this->is_elementor_active() ) {
			return new WP_Error( 'elementor_inactive', __( 'Elementor is not active.', 'site-pilot-ai' ) );
		}

		$template = $this->get_template( $template_id );
		if ( is_wp_error( $template ) ) {
			return $template;
		}

		$page = get_post( $page_id );
		if ( ! $page || 'page' !== $page->post_type ) {
			return new WP_Error( 'not_found', __( 'Target page not found.', 'site-pilot-ai' ) );
		}

		$data = get_post_meta( $template_id, '_elementor_data', true );
		if ( empty( $data ) ) {
			return new WP_Error( 'empty_template', __( 'Template has no Elementor data.', 'site-pilot-ai' ) );
		}

		update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $page_id, '_elementor_data', $data );

		return array(
			'applied'    => true,
			'templateId' => $template_id,
			'pageId'     => $page_id,
			'edit_url'   => admin_url( 'post.php?post=' . $page_id . '&action=elementor' ),
		);
	}

	public function clone_page( $data ) {
		$source_id = isset( $data['source_id'] ) ? absint( $data['source_id'] ) : 0;
		if ( ! $source_id ) {
			return new WP_Error( 'invalid_source', __( 'Invalid source page ID.', 'site-pilot-ai' ) );
		}

		$source = get_post( $source_id );
		if ( ! $source || 'page' !== $source->post_type ) {
			return new WP_Error( 'not_found', __( 'Source page not found.', 'site-pilot-ai' ) );
		}

		$title  = ! empty( $data['title'] ) ? sanitize_text_field( $data['title'] ) : ( $source->post_title . ' (Copy)' );
		$status = ! empty( $data['status'] ) ? sanitize_text_field( $data['status'] ) : 'draft';

		$new_id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => $status,
				'post_title'  => $title,
			)
		);

		if ( is_wp_error( $new_id ) ) {
			return $new_id;
		}

		$elementor_data = get_post_meta( $source_id, '_elementor_data', true );
		if ( ! empty( $elementor_data ) ) {
			update_post_meta( $new_id, '_elementor_edit_mode', 'builder' );
			update_post_meta( $new_id, '_elementor_data', $elementor_data );
		}

		return array(
			'id'       => $new_id,
			'title'    => $title,
			'status'   => $status,
			'edit_url' => admin_url( 'post.php?post=' . $new_id . '&action=elementor' ),
		);
	}

	public function create_landing_page( $data ) {
		if ( ! $this->is_elementor_pro_active() ) {
			return new WP_Error( 'elementor_pro_inactive', __( 'Elementor Pro is not active.', 'site-pilot-ai' ) );
		}

		$title  = ! empty( $data['title'] ) ? sanitize_text_field( $data['title'] ) : __( 'Landing Page', 'site-pilot-ai' );
		$status = ! empty( $data['status'] ) ? sanitize_text_field( $data['status'] ) : 'draft';

		$page_id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => $status,
				'post_title'  => $title,
			)
		);

		if ( is_wp_error( $page_id ) ) {
			return $page_id;
		}

		update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );

		if ( ! empty( $data['content'] ) ) {
			$elementor_data = $data['content'];
			if ( is_array( $elementor_data ) ) {
				$elementor_data = wp_json_encode( $elementor_data );
			}
			update_post_meta( $page_id, '_elementor_data', wp_slash( $elementor_data ) );
		}

		return array(
			'id'       => $page_id,
			'title'    => $title,
			'status'   => $status,
			'edit_url' => admin_url( 'post.php?post=' . $page_id . '&action=elementor' ),
		);
	}

	public function get_globals() {
		if ( ! $this->is_elementor_pro_active() ) {
			return new WP_Error( 'elementor_pro_inactive', __( 'Elementor Pro is not active.', 'site-pilot-ai' ) );
		}

		if ( ! class_exists( '\\Elementor\\Plugin' ) ) {
			return new WP_Error( 'elementor_missing', __( 'Elementor is not available.', 'site-pilot-ai' ) );
		}

		$kit_id   = \Elementor\Plugin::$instance->kits_manager->get_active_id();
		$settings = get_post_meta( $kit_id, '_elementor_page_settings', true );

		return array(
			'kit_id'   => $kit_id,
			'settings' => $settings,
		);
	}
}

