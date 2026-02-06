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
		// Create API role and user
		self::create_api_role_and_user();

		// Generate API key if not exists
		if ( ! get_option( 'spai_api_key' ) ) {
			$api_key = self::generate_api_key();
			update_option( 'spai_api_key', wp_hash_password( $api_key ) );
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
	 * Create API role and user.
	 */
	private static function create_api_role_and_user() {
		// Add Role
		add_role( 'spai_api_agent', 'Site Pilot API Agent', array(
			'read'               => true,
			'edit_posts'         => true,
			'edit_pages'         => true,
			'edit_others_posts'  => true,
			'edit_others_pages'  => true,
			'publish_posts'      => true,
			'publish_pages'      => true,
			'delete_posts'       => true,
			'delete_pages'       => true,
			'manage_options'     => true,
			'upload_files'       => true,
			'list_users'         => true,
			'edit_theme_options' => true,
		) );

		// Create User
		$user = get_user_by( 'login', 'spai_bot' );
		if ( ! $user ) {
			wp_insert_user( array(
				'user_login'   => 'spai_bot',
				'user_pass'    => wp_generate_password( 64 ),
				'role'         => 'spai_api_agent',
				'display_name' => 'Site Pilot AI',
				'description'  => 'Service account for Site Pilot AI API',
			) );
		}
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

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Activity log table
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
		dbDelta( $sql );

		// Webhooks table
		$webhooks_table = $wpdb->prefix . 'spai_webhooks';
		$sql_webhooks = "CREATE TABLE IF NOT EXISTS $webhooks_table (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			url varchar(2048) NOT NULL,
			secret varchar(255) DEFAULT NULL,
			events text NOT NULL,
			status varchar(20) DEFAULT 'active',
			retry_count int(11) DEFAULT 0,
			last_triggered datetime DEFAULT NULL,
			last_status varchar(50) DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY status (status)
		) $charset_collate;";
		dbDelta( $sql_webhooks );

		// Webhook logs table
		$logs_table = $wpdb->prefix . 'spai_webhook_logs';
		$sql_logs = "CREATE TABLE IF NOT EXISTS $logs_table (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			webhook_id bigint(20) unsigned NOT NULL,
			event varchar(100) NOT NULL,
			payload longtext NOT NULL,
			response_code int(11) DEFAULT NULL,
			response_body text DEFAULT NULL,
			duration float DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY webhook_id (webhook_id),
			KEY created_at (created_at)
		) $charset_collate;";
		dbDelta( $sql_logs );
	}
}
