<?php
/**
 * Freemius SDK Integration
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'spa_fs' ) ) {
	/**
	 * Create a helper function for easy SDK access.
	 *
	 * @return Freemius
	 */
	function spa_fs() {
		global $spa_fs;

		if ( ! isset( $spa_fs ) ) {
			// Activate multisite network integration.
			if ( ! defined( 'WP_FS__PRODUCT_23824_MULTISITE' ) ) {
				define( 'WP_FS__PRODUCT_23824_MULTISITE', true );
			}

			// Include Freemius SDK.
			require_once SPAI_PLUGIN_DIR . 'freemius/start.php';

			$spa_fs = fs_dynamic_init( array(
				'id'                  => '23824',
				'slug'                => 'site-pilot-ai',
				'type'                => 'plugin',
				'public_key'          => 'pk_24f806380f2ccf8a5e3283dac895b',
				'is_premium'          => true,
				'has_premium_version' => true,
				'has_addons'          => false,
				'has_paid_plans'      => true,
				'trial'               => array(
					'days'               => 14,
					'is_require_payment' => true,
				),
				'menu'                => array(
					'slug'       => 'site-pilot-ai',
					'first-path' => 'wp-admin/tools.php?page=site-pilot-ai',
					'support'    => false,
				),
				'is_live'             => true,
			) );
		}

		return $spa_fs;
	}

	// Init Freemius.
	spa_fs();

	// Signal that SDK was initiated.
	do_action( 'spa_fs_loaded' );
}

/**
 * Freemius customizations.
 */

// Custom icon for opt-in screen.
function spa_fs_custom_icon() {
	return SPAI_PLUGIN_DIR . 'assets/icon-128x128.png';
}
spa_fs()->add_filter( 'plugin_icon', 'spa_fs_custom_icon' );

// Custom connect message.
function spa_fs_custom_connect_message(
	$message,
	$user_first_name,
	$product_title,
	$user_login,
	$site_link,
	$freemius_link
) {
	return sprintf(
		/* translators: %1$s: User first name, %2$s: Product title */
		__( 'Hey %1$s, allow %2$s to collect diagnostic data to help improve the plugin and enable license management.', 'site-pilot-ai' ),
		$user_first_name,
		'<b>' . $product_title . '</b>'
	);
}
spa_fs()->add_filter( 'connect_message', 'spa_fs_custom_connect_message', 10, 6 );

// Uninstall hook.
spa_fs()->add_action( 'after_uninstall', 'spa_fs_uninstall_cleanup' );

/**
 * Cleanup on uninstall.
 */
function spa_fs_uninstall_cleanup() {
	// Clean up options.
	delete_option( 'spai_api_key' );
	delete_option( 'spai_settings' );
	delete_option( 'spai_version' );
	delete_option( 'spai_rate_limit_settings' );

	// Clean up tables.
	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}spai_activity_log" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}spai_webhooks" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}spai_webhook_logs" );
}
