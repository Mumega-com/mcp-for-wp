<?php
/**
 * Pro Module Bootstrap
 *
 * Loads additional endpoints/features for Pro users.
 *
 * All features are free — no Pro/premium split.
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pro bootstrap class.
 */
class Spai_Pro_Bootstrap {

	/**
	 * Register hooks for pro features.
	 */
	public static function init() {
		add_action( 'spai_register_rest_routes', array( __CLASS__, 'register_routes' ) );
		add_filter( 'spai_site_capabilities', array( __CLASS__, 'add_pro_capabilities' ) );
	}

	/**
	 * Register Pro REST API routes.
	 */
	public static function register_routes() {
		if ( ! class_exists( 'Spai_REST_API' ) ) {
			return;
		}

		// Core handlers.
		require_once SPAI_PLUGIN_DIR . 'includes/pro/core/class-spai-elementor-pro.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/core/class-spai-seo.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/core/class-spai-forms.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/core/class-spai-site-manager.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/core/class-spai-theme-builder.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/core/class-spai-users.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/core/class-spai-widgets.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/core/class-spai-themes.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/core/class-spai-woocommerce.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/core/class-spai-multilang.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/core/class-spai-page-builder.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/core/class-spai-google-indexing.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/core/class-spai-learnpress.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/core/class-spai-events.php';

		// REST controllers.
		require_once SPAI_PLUGIN_DIR . 'includes/pro/api/class-spai-rest-elementor-pro.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/api/class-spai-rest-seo.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/api/class-spai-rest-forms.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/api/class-spai-rest-site-manager.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/api/class-spai-rest-theme-builder.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/api/class-spai-rest-users.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/api/class-spai-rest-widgets.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/api/class-spai-rest-themes.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/api/class-spai-rest-woocommerce.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/api/class-spai-rest-multilang.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/api/class-spai-rest-google-indexing.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/api/class-spai-rest-learnpress.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/api/class-spai-rest-events.php';

		$elementor_pro = new Spai_Elementor_Pro();
		$seo           = new Spai_SEO();
		$forms         = new Spai_Forms();
		$site_manager  = new Spai_Site_Manager();
		$theme_builder = new Spai_Theme_Builder();
		$users         = new Spai_Users();
		$widgets       = new Spai_Widgets();
		$themes        = new Spai_Themes();
		$woocommerce   = new Spai_WooCommerce();
		$multilang     = new Spai_Multilang();
		$google_indexing = new Spai_Google_Indexing();
		$learnpress      = new Spai_LearnPress();
		$events          = new Spai_Events();

		( new Spai_REST_Elementor_Pro( $elementor_pro ) )->register_routes();
		( new Spai_REST_SEO( $seo ) )->register_routes();
		( new Spai_REST_Forms( $forms ) )->register_routes();
		( new Spai_REST_Site_Manager( $site_manager ) )->register_routes();
		( new Spai_REST_Theme_Builder( $theme_builder ) )->register_routes();
		( new Spai_REST_Users( $users ) )->register_routes();
		( new Spai_REST_Widgets( $widgets ) )->register_routes();
		( new Spai_REST_Themes( $themes ) )->register_routes();
		( new Spai_REST_WooCommerce( $woocommerce ) )->register_routes();
		( new Spai_REST_Multilang( $multilang ) )->register_routes();
		( new Spai_REST_Google_Indexing( $google_indexing ) )->register_routes();

		// LearnPress LMS — only register if LearnPress is active.
		if ( class_exists( 'LearnPress' ) || post_type_exists( 'lp_course' ) ) {
			( new Spai_REST_LearnPress( $learnpress ) )->register_routes();
		}

		// TP Events — only register if tp_event post type exists.
		if ( post_type_exists( 'tp_event' ) ) {
			( new Spai_REST_Events( $events ) )->register_routes();
		}
	}

	/**
	 * Add Pro capabilities to the capabilities array.
	 *
	 * @param array $capabilities Capabilities array.
	 * @return array
	 */
	public static function add_pro_capabilities( $capabilities ) {
		$capabilities['pro_active']  = true;
		$capabilities['plan']        = 'free';
		$capabilities['learnpress']  = class_exists( 'LearnPress' ) || post_type_exists( 'lp_course' );
		$capabilities['tp_events']   = post_type_exists( 'tp_event' );
		return $capabilities;
	}
}
