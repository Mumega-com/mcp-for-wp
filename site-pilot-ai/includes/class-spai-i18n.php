<?php
/**
 * Internationalization
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define internationalization functionality.
 */
class Spai_i18n {

	/**
	 * Load the plugin text domain.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'site-pilot-ai',
			false,
			dirname( SPAI_PLUGIN_BASENAME ) . '/languages/'
		);
	}
}
