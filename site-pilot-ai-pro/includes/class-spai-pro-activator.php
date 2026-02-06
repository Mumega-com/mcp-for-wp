<?php
/**
 * Pro Plugin Activator
 *
 * @package SitePilotAI_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pro activator class.
 *
 * Handles Pro plugin activation tasks.
 */
class Spai_Pro_Activator {

	/**
	 * Activate the Pro plugin.
	 */
	public static function activate() {
		// Store Pro version.
		update_option( 'spai_pro_version', SPAI_PRO_VERSION );

		// Create Pro-specific database tables if needed.
		self::create_tables();

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Create Pro-specific database tables.
	 */
	private static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// SEO snapshots table (for tracking SEO changes).
		$seo_table = $wpdb->prefix . 'spai_seo_snapshots';

		$sql = "CREATE TABLE IF NOT EXISTS $seo_table (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id BIGINT(20) UNSIGNED NOT NULL,
			seo_plugin VARCHAR(50) NOT NULL,
			title VARCHAR(255) DEFAULT NULL,
			description TEXT DEFAULT NULL,
			focus_keyword VARCHAR(255) DEFAULT NULL,
			meta_data LONGTEXT DEFAULT NULL,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY post_id (post_id),
			KEY seo_plugin (seo_plugin),
			KEY created_at (created_at)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
