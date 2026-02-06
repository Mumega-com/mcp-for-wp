<?php
/**
 * Pro Module Bootstrap
 *
 * Loads additional endpoints/features for Pro users.
 *
 * This file is intended to exist ONLY in the premium package zip
 * (`site-pilot-ai-premium`). The free package should not include it.
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
		// Only enable Pro endpoints when the license/trial grants access.
		if ( ! function_exists( 'spai_license' ) || ! spai_license()->is_pro() ) {
			return;
		}

		add_action( 'spai_register_rest_routes', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Register Pro REST API routes.
	 */
	public static function register_routes() {
		// Elementor Pro endpoints.
		if ( ! class_exists( 'Spai_REST_API' ) ) {
			return;
		}

		require_once SPAI_PLUGIN_DIR . 'includes/pro/core/class-spai-elementor-pro.php';
		require_once SPAI_PLUGIN_DIR . 'includes/pro/api/class-spai-rest-elementor-pro.php';

		$elementor_pro = new Spai_Elementor_Pro();
		$controller    = new Spai_REST_Elementor_Pro( $elementor_pro );
		$controller->register_routes();
	}
}

