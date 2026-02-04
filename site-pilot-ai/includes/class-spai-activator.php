<?php
/**
 * Plugin Activator
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fired during plugin activation.
 */
class Spai_Activator {

	/**
	 * Activate the plugin.
	 *
	 * Generate API key if not exists, set up options.
	 */
	public static function activate() {
		// Generate API key if not exists
		if ( ! get_option( 'spai_api_key' ) ) {
			$api_key = self::generate_api_key();
			update_option( 'spai_api_key', $api_key );
		}

		// Set default options
		if ( false === get_option( 'spai_settings' ) ) {
			$defaults = array(
				'enable_logging'     => true,
				'log_retention_days' => 30,
				'allowed_origins'    => '',
			);
			update_option( 'spai_settings', $defaults );
		}

		// Create activity log table
		self::create_tables();

		// Set version
		update_option( 'spai_version', SPAI_VERSION );

		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Generate a secure API key.
	 *
	 * @return string API key.
	 */
	private static function generate_api_key() {
		return 'spai_' . wp_generate_password( 32, false );
	}

	/**
	 * Create database tables.
	 */
	private static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'spai_activity_log';

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			action varchar(100) NOT NULL,
			endpoint varchar(255) NOT NULL,
			method varchar(10) NOT NULL,
			status_code int(3) DEFAULT NULL,
			ip_address varchar(45) DEFAULT NULL,
			user_agent text DEFAULT NULL,
			request_data longtext DEFAULT NULL,
			response_data longtext DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY action (action),
			KEY created_at (created_at)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
