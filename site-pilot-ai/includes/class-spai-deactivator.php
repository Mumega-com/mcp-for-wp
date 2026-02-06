<?php
/**
 * Plugin Deactivator
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fired during plugin deactivation.
 */
class Spai_Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * Clean up transients, flush rewrite rules.
	 * Note: API key and settings are preserved for reactivation.
	 * Full cleanup happens in uninstall.php.
	 */
	public static function deactivate() {
		// Clear any transients
		delete_transient( 'spai_capabilities_cache' );
		wp_clear_scheduled_hook( 'spai_cleanup_logs' );

		// Flush rewrite rules
		flush_rewrite_rules();
	}
}
