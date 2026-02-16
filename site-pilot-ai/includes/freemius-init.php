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
				// Single-plugin distribution: paid plans unlock features via license gates.
				// This avoids the "download premium zip" step after checkout.
				'is_premium'          => true,
				'is_premium_only'     => false,
				'has_premium_version' => false,
				'has_addons'          => false,
				'has_paid_plans'      => true,
				'trial'               => array(
					'days'               => 14,
					'is_require_payment' => true,
				),
				'menu'                => array(
					'slug'       => 'site-pilot-ai',
					'parent'     => array(
						'slug' => 'site-pilot-ai',
					),
					'first-path' => 'admin.php?page=site-pilot-ai',
					'support'    => false,
					'account'    => true,
					'pricing'    => true,
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
 * Get Freemius instance safely.
 *
 * @return object|null
 */
function spai_get_fs_instance() {
	if ( ! function_exists( 'spa_fs' ) ) {
		return null;
	}
	$instance = spa_fs();
	return is_object( $instance ) ? $instance : null;
}

/**
 * Freemius customizations.
 */

// Custom icon for opt-in screen.
function spa_fs_custom_icon() {
	return SPAI_PLUGIN_DIR . 'assets/icon-128x128.png';
}
$spai_fs_instance = spai_get_fs_instance();
if ( $spai_fs_instance && method_exists( $spai_fs_instance, 'add_filter' ) ) {
	$spai_fs_instance->add_filter( 'plugin_icon', 'spa_fs_custom_icon' );
}

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
if ( $spai_fs_instance && method_exists( $spai_fs_instance, 'add_filter' ) ) {
	$spai_fs_instance->add_filter( 'connect_message', 'spa_fs_custom_connect_message', 10, 6 );
}

// Make "Download latest" links trigger Freemius auto-install (one-click upgrade).
function spa_fs_download_latest_url_auto_install( $url ) {
	if ( ! is_string( $url ) || '' === $url ) {
		return $url;
	}

	$separator = ( strpos( $url, '?' ) === false ) ? '?' : '&';
	return $url . $separator . 'auto_install=true';
}
if ( $spai_fs_instance && method_exists( $spai_fs_instance, 'add_filter' ) ) {
	$spai_fs_instance->add_filter( 'download_latest_url', 'spa_fs_download_latest_url_auto_install' );
}

// Force Freemius to re-check for updates when visiting the plugins page.
// This fixes the delay between deploying a new version and seeing the update notice.
if ( $spai_fs_instance && method_exists( $spai_fs_instance, 'add_action' ) ) {
	add_action( 'load-plugins.php', 'spa_fs_maybe_flush_update_cache' );
	add_action( 'load-update-core.php', 'spa_fs_maybe_flush_update_cache' );
}

/**
 * Clear Freemius update cache on plugins page to ensure updates appear immediately.
 */
function spa_fs_maybe_flush_update_cache() {
	if ( ! function_exists( 'spa_fs' ) ) {
		return;
	}

	$fs = spa_fs();

	// Only flush once per hour to avoid hammering Freemius API.
	$last_flush = get_option( 'spai_last_update_flush', 0 );
	if ( time() - $last_flush < 3600 ) {
		return;
	}

	// Delete the WordPress update transient so it re-checks.
	delete_site_transient( 'update_plugins' );
	update_option( 'spai_last_update_flush', time() );

	// Trigger Freemius to re-sync if available.
	if ( is_object( $fs ) && method_exists( $fs, 'get_update' ) ) {
		$fs->get_update( false, false );
	}
}

// Uninstall hook.
if ( $spai_fs_instance && method_exists( $spai_fs_instance, 'add_action' ) ) {
	$spai_fs_instance->add_action( 'after_uninstall', 'spa_fs_uninstall_cleanup' );
}

/**
 * Cleanup on uninstall.
 */
function spa_fs_uninstall_cleanup() {
	// Clean up options.
	delete_option( 'spai_api_key' );
	delete_option( 'spai_api_keys' );
	delete_option( 'spai_settings' );
	delete_option( 'spai_version' );
	delete_option( 'spai_rate_limit_settings' );

	// Clean up transients.
	delete_transient( 'spai_capabilities_cache' );

	// Clear scheduled events.
	wp_clear_scheduled_hook( 'spai_cleanup_logs' );

	// Clean up tables.
	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}spai_activity_log" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}spai_webhooks" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}spai_webhook_logs" );
}
