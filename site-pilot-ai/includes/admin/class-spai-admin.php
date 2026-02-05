<?php
/**
 * Admin functionality
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin class.
 */
class Spai_Admin {

	use Spai_Api_Auth;

	/**
	 * Admin page slug.
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'site-pilot-ai';

	/**
	 * Add admin menu.
	 */
	public function add_admin_menu() {
		add_management_page(
			__( 'Site Pilot AI', 'site-pilot-ai' ),
			__( 'Site Pilot AI', 'site-pilot-ai' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Enqueue admin styles.
	 *
	 * @param string $hook Current admin page.
	 */
	public function enqueue_styles( $hook ) {
		if ( 'tools_page_' . self::PAGE_SLUG !== $hook ) {
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
	 * Enqueue admin scripts.
	 *
	 * @param string $hook Current admin page.
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'tools_page_' . self::PAGE_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'spai-admin',
			SPAI_PLUGIN_URL . 'admin/js/spai-admin.js',
			array( 'jquery' ),
			SPAI_VERSION,
			true
		);

		wp_localize_script(
			'spai-admin',
			'spaiAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'spai_admin_nonce' ),
				'strings' => array(
					'copied'     => __( 'Copied!', 'site-pilot-ai' ),
					'copyFailed' => __( 'Copy failed', 'site-pilot-ai' ),
					'confirm'    => __( 'Are you sure you want to regenerate the API key? The old key will stop working immediately.', 'site-pilot-ai' ),
				),
			)
		);
	}

	/**
	 * Render admin page.
	 */
	public function render_admin_page() {
		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'site-pilot-ai' ) );
		}

		// Handle regenerate action
		$new_key = null;
		if ( isset( $_POST['spai_regenerate_key'] ) ) {
			check_admin_referer( 'spai_regenerate_key', 'spai_nonce' );
			$new_key = $this->regenerate_api_key();
			add_settings_error(
				'spai_messages',
				'spai_key_regenerated',
				__( 'API key has been regenerated. Please copy it now as it will not be shown again.', 'site-pilot-ai' ),
				'updated'
			);
		}

		include SPAI_PLUGIN_DIR . 'admin/partials/spai-admin-display.php';
	}

	/**
	 * Display admin notices.
	 */
	public function admin_notices() {
		settings_errors( 'spai_messages' );
	}

	/**
	 * Add plugin action links.
	 *
	 * @param array $links Existing links.
	 * @return array Modified links.
	 */
	public function add_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'tools.php?page=' . self::PAGE_SLUG ),
			__( 'Settings', 'site-pilot-ai' )
		);

		array_unshift( $links, $settings_link );

		// Add upgrade link if not Pro
		if ( ! class_exists( 'Spai_Pro_Loader' ) ) {
			$links[] = sprintf(
				'<a href="%s" style="color:#00a32a;font-weight:bold;" target="_blank">%s</a>',
				'https://sitepilotai.com/pricing',
				__( 'Go Pro', 'site-pilot-ai' )
			);
		}

		return $links;
	}

	/**
	 * Get capabilities for display.
	 *
	 * @return array Capabilities.
	 */
	public function get_capabilities_display() {
		$core = new Spai_Core();
		$capabilities = $core->get_capabilities();

		$display = array();

		// Elementor
		$display['elementor'] = array(
			'label'  => __( 'Elementor', 'site-pilot-ai' ),
			'active' => $capabilities['elementor'],
			'pro'    => $capabilities['elementor_pro'],
		);

		// SEO
		$seo_active = $capabilities['yoast'] || $capabilities['rankmath'] || $capabilities['aioseo'] || $capabilities['seopress'];
		$seo_name = '';
		if ( $capabilities['yoast'] ) {
			$seo_name = 'Yoast SEO';
		} elseif ( $capabilities['rankmath'] ) {
			$seo_name = 'RankMath';
		} elseif ( $capabilities['aioseo'] ) {
			$seo_name = 'All in One SEO';
		} elseif ( $capabilities['seopress'] ) {
			$seo_name = 'SEOPress';
		}
		$display['seo'] = array(
			'label'  => __( 'SEO Plugin', 'site-pilot-ai' ),
			'active' => $seo_active,
			'name'   => $seo_name,
		);

		// Forms
		$forms_active = $capabilities['cf7'] || $capabilities['wpforms'] || $capabilities['gravityforms'] || $capabilities['ninjaforms'];
		$forms = array();
		if ( $capabilities['cf7'] ) {
			$forms[] = 'CF7';
		}
		if ( $capabilities['wpforms'] ) {
			$forms[] = 'WPForms';
		}
		if ( $capabilities['gravityforms'] ) {
			$forms[] = 'Gravity Forms';
		}
		if ( $capabilities['ninjaforms'] ) {
			$forms[] = 'Ninja Forms';
		}
		$display['forms'] = array(
			'label'  => __( 'Form Plugins', 'site-pilot-ai' ),
			'active' => $forms_active,
			'names'  => $forms,
		);

		// WooCommerce
		$display['woocommerce'] = array(
			'label'  => __( 'WooCommerce', 'site-pilot-ai' ),
			'active' => $capabilities['woocommerce'],
		);

		return $display;
	}
}
