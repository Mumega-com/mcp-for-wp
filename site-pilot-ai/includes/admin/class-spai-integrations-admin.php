<?php
/**
 * Integrations Admin Page
 *
 * Handles the admin UI for managing third-party AI provider integrations.
 *
 * @package SitePilotAI
 * @since   1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin page for AI integrations.
 */
class Spai_Integrations_Admin {

	/**
	 * Page slug.
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'site-pilot-ai-integrations';

	/**
	 * Render the admin page.
	 */
	public function render() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'site-pilot-ai' ) );
		}

		$manager   = Spai_Integration_Manager::get_instance();
		$providers = $manager->get_available_providers();
		$is_pro    = true;

		include SPAI_PLUGIN_DIR . 'admin/partials/spai-integrations-display.php';
	}

	/**
	 * Get the admin URL used to start Figma OAuth.
	 *
	 * @return string
	 */
	public function get_figma_oauth_start_url() {
		return wp_nonce_url(
			admin_url( 'admin-post.php?action=spai_figma_oauth_start' ),
			'spai_figma_oauth_start',
			'spai_nonce'
		);
	}

	/**
	 * Enqueue admin assets for integrations page.
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

		wp_enqueue_script(
			'spai-integrations',
			SPAI_PLUGIN_URL . 'admin/js/spai-admin.js',
			array( 'jquery' ),
			SPAI_VERSION,
			true
		);

		wp_localize_script(
			'spai-integrations',
			'spaiIntegrations',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'spai_integrations_nonce' ),
				'strings' => array(
					'saving'     => __( 'Saving...', 'site-pilot-ai' ),
					'saved'      => __( 'Saved!', 'site-pilot-ai' ),
					'saveFailed' => __( 'Save failed', 'site-pilot-ai' ),
					'testing'    => __( 'Testing...', 'site-pilot-ai' ),
					'connected'  => __( 'Connected!', 'site-pilot-ai' ),
					'testFailed' => __( 'Connection failed', 'site-pilot-ai' ),
					'removing'   => __( 'Removing...', 'site-pilot-ai' ),
					'removed'    => __( 'Removed!', 'site-pilot-ai' ),
					'confirmRemove' => __( 'Are you sure you want to remove this API key?', 'site-pilot-ai' ),
				),
			)
		);
	}

	/**
	 * AJAX: Save integration key.
	 */
	public function ajax_save_key() {
		check_ajax_referer( 'spai_integrations_nonce', 'nonce' );

		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		$provider = isset( $_POST['provider'] ) ? sanitize_key( wp_unslash( $_POST['provider'] ) ) : '';
		if ( empty( $provider ) ) {
			wp_send_json_error( array( 'message' => __( 'Provider is required.', 'site-pilot-ai' ) ) );
		}

		$manager = Spai_Integration_Manager::get_instance();

		// Multi-field providers (e.g. screenshot worker: URL + token).
		if ( $manager->is_multi_field_provider( $provider ) ) {
			$config = isset( $_POST['config'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['config'] ) ) : array();
			if ( empty( $config ) ) {
				wp_send_json_error( array( 'message' => __( 'Configuration fields are required.', 'site-pilot-ai' ) ) );
			}
			// Sanitize URL field specifically.
			if ( isset( $config['url'] ) ) {
				$config['url'] = esc_url_raw( $config['url'] );
			}
			$result = $manager->set_provider_config( $provider, $config );
		} else {
			$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
			if ( empty( $key ) ) {
				wp_send_json_error( array( 'message' => __( 'API key is required.', 'site-pilot-ai' ) ) );
			}
			$result = $manager->set_provider_key( $provider, $key );
		}

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Configuration saved.', 'site-pilot-ai' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to save configuration.', 'site-pilot-ai' ) ) );
		}
	}

	/**
	 * AJAX: Remove integration key.
	 */
	public function ajax_remove_key() {
		check_ajax_referer( 'spai_integrations_nonce', 'nonce' );

		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		$provider = isset( $_POST['provider'] ) ? sanitize_key( wp_unslash( $_POST['provider'] ) ) : '';
		if ( empty( $provider ) ) {
			wp_send_json_error( array( 'message' => __( 'Provider is required.', 'site-pilot-ai' ) ) );
		}

		$manager = Spai_Integration_Manager::get_instance();
		$manager->remove_provider_key( $provider );

		wp_send_json_success( array( 'message' => __( 'API key removed.', 'site-pilot-ai' ) ) );
	}

	/**
	 * AJAX: Test integration connection.
	 */
	public function ajax_test_connection() {
		check_ajax_referer( 'spai_integrations_nonce', 'nonce' );

		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		$provider = isset( $_POST['provider'] ) ? sanitize_key( wp_unslash( $_POST['provider'] ) ) : '';
		if ( empty( $provider ) ) {
			wp_send_json_error( array( 'message' => __( 'Provider is required.', 'site-pilot-ai' ) ) );
		}

		$manager = Spai_Integration_Manager::get_instance();
		$result  = $manager->test_provider( $provider );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Start the Figma OAuth handshake.
	 *
	 * @return void
	 */
	public function handle_figma_oauth_start() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'site-pilot-ai' ) );
		}

		check_admin_referer( 'spai_figma_oauth_start', 'spai_nonce' );

		$figma = new Spai_Figma();
		$url   = $figma->get_oauth_authorize_url();
		if ( is_wp_error( $url ) ) {
			wp_safe_redirect(
				add_query_arg(
					array(
						'page'            => self::PAGE_SLUG,
						'spai_figma_oauth' => 'error',
						'message'         => rawurlencode( $url->get_error_message() ),
					),
					admin_url( 'admin.php' )
				)
			);
			exit;
		}

		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Complete the Figma OAuth callback.
	 *
	 * @return void
	 */
	public function handle_figma_oauth_callback() {
		$code  = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '';
		$state = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : '';
		$error = isset( $_GET['error'] ) ? sanitize_text_field( wp_unslash( $_GET['error'] ) ) : '';

		if ( '' !== $error ) {
			wp_safe_redirect(
				add_query_arg(
					array(
						'page'            => self::PAGE_SLUG,
						'spai_figma_oauth' => 'error',
						'message'         => rawurlencode( $error ),
					),
					admin_url( 'admin.php' )
				)
			);
			exit;
		}

		$figma  = new Spai_Figma();
		$result = $figma->exchange_oauth_code( $code, $state );

		$args = array(
			'page'             => self::PAGE_SLUG,
			'spai_figma_oauth' => is_wp_error( $result ) ? 'error' : 'success',
			'message'          => rawurlencode( is_wp_error( $result ) ? $result->get_error_message() : $result['message'] ),
		);

		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}
}
