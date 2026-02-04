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

		$page = get_post( absint( $page_id ) );

		if ( ! $page || 'page' !== $page->post_type ) {
			return new WP_Error(
				'not_found',
				__( 'Page not found.', 'site-pilot-ai' ),
				array( 'status' => 404 )
			);
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

		$page = get_post( absint( $page_id ) );

		if ( ! $page || 'page' !== $page->post_type ) {
			return new WP_Error(
				'not_found',
				__( 'Page not found.', 'site-pilot-ai' ),
				array( 'status' => 404 )
			);
		}

		// Validate and encode data
		$elementor_json = null;

		if ( isset( $data['elementor_data'] ) ) {
			// If array, encode to JSON
			if ( is_array( $data['elementor_data'] ) ) {
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
			$elementor_json = $data['elementor_json'];
		}

		if ( empty( $elementor_json ) ) {
			return new WP_Error(
				'no_data',
				__( 'No Elementor data provided.', 'site-pilot-ai' ),
				array( 'status' => 400 )
			);
		}

		// Save Elementor data
		update_post_meta( $page_id, '_elementor_data', wp_slash( $elementor_json ) );
		update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );

		// Set page template to Elementor
		if ( ! get_post_meta( $page_id, '_wp_page_template', true ) ) {
			update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );
		}

		// Clear Elementor cache if available
		if ( class_exists( '\Elementor\Plugin' ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}

		return array(
			'success' => true,
			'page_id' => $page_id,
			'message' => __( 'Elementor data updated.', 'site-pilot-ai' ),
			'edit_url' => admin_url( "post.php?post={$page_id}&action=elementor" ),
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

		// Enable Elementor
		update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );

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
}
