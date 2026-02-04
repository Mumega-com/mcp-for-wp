<?php
/**
 * Freemius SDK Integration
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize Freemius SDK.
 *
 * @return Freemius
 */
function spai_fs() {
	global $spai_fs;

	if ( ! isset( $spai_fs ) ) {
		// Include Freemius SDK.
		require_once SPAI_PLUGIN_DIR . 'freemius/start.php';

		$spai_fs = fs_dynamic_init( array(
			'id'                  => '23824',
			'slug'                => 'site-pilot-ai',
			'type'                => 'plugin',
			'public_key'          => 'pk_24f806380f2ccf8a5e3283dac895b',
			'is_premium'          => false,
			'has_addons'          => true,
			'has_paid_plans'      => true,
			'menu'                => array(
				'slug'       => 'site-pilot-ai',
				'first-path' => 'tools.php?page=site-pilot-ai',
				'support'    => false,
			),
			'is_live'             => true,
		) );
	}

	return $spai_fs;
}

// Initialize Freemius.
spai_fs();

// Signal that SDK was initiated.
do_action( 'spai_fs_loaded' );

/**
 * Freemius customizations.
 */

// Custom icon for opt-in screen.
function spai_fs_custom_icon() {
	return SPAI_PLUGIN_DIR . 'assets/icon-128x128.png';
}
spai_fs()->add_filter( 'plugin_icon', 'spai_fs_custom_icon' );

// Custom connect message.
function spai_fs_custom_connect_message(
	$message,
	$user_first_name,
	$product_title,
	$user_login,
	$site_link,
	$freemius_link
) {
	return sprintf(
		__( 'Hey %1$s, allow %2$s to collect diagnostic data to help improve the plugin.', 'site-pilot-ai' ),
		$user_first_name,
		'<b>' . $product_title . '</b>'
	);
}
spai_fs()->add_filter( 'connect_message', 'spai_fs_custom_connect_message', 10, 6 );

// Uninstall hook.
spai_fs()->add_action( 'after_uninstall', 'spai_fs_uninstall_cleanup' );

/**
 * Cleanup on uninstall.
 */
function spai_fs_uninstall_cleanup() {
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
