<?php
/**
 * Uninstall Site Pilot AI
 *
 * Removes all plugin data when uninstalled.
 *
 * @package SitePilotAI
 */

// Exit if not uninstalling
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete options
delete_option( 'spai_api_key' );
delete_option( 'spai_settings' );
delete_option( 'spai_version' );

// Delete transients
delete_transient( 'spai_capabilities_cache' );

// Drop activity log table
global $wpdb;
$table_name = $wpdb->prefix . 'spai_activity_log';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
$wpdb->query( "DROP TABLE IF EXISTS $table_name" );

// Clear any scheduled events
wp_clear_scheduled_hook( 'spai_cleanup_logs' );
