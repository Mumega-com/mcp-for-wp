<?php
/**
 * Site Pilot AI
 *
 * Control WordPress with AI through the Model Context Protocol (MCP).
 * Expose your WordPress site's functionality to AI assistants like Claude.
 *
 * @package           SitePilotAI
 * @author            DigID Inc
 * @copyright         2024 DigID Inc
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Site Pilot AI
 * Plugin URI:        https://sitepilotai.mumega.com/
 * Description:       Control WordPress with AI. Expose posts, pages, media, and Elementor to AI assistants via MCP.
 * Version:           1.5.2
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            DigID Inc
 * Author URI:        https://mumega.com/
 * Text Domain:       site-pilot-ai
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle free vs premium package coexistence.
 *
 * Freemius can distribute a separate premium zip (`site-pilot-ai-premium`).
 * When both are installed, we prefer the premium package and ensure only one
 * instance is active to avoid double-loading and fatals.
 *
 * - If the premium package is active, the free package should not run.
 * - If the premium package is being activated, deactivate the free package.
 */
$spai_free_plugin_file    = 'site-pilot-ai/site-pilot-ai.php';
$spai_premium_plugin_file = 'site-pilot-ai-premium/site-pilot-ai.php';

$spai_is_plugin_active = static function ( $plugin_file ) {
	if ( ! function_exists( 'get_option' ) ) {
		return false;
	}

	// Check single-site active plugins.
	$active_plugins = get_option( 'active_plugins', array() );
	if ( is_array( $active_plugins ) && in_array( $plugin_file, $active_plugins, true ) ) {
		return true;
	}

	// Check network-wide active plugins (multisite).
	if ( function_exists( 'is_multisite' ) && is_multisite() && function_exists( 'get_site_option' ) ) {
		$network_plugins = get_site_option( 'active_sitewide_plugins', array() );
		if ( is_array( $network_plugins ) && isset( $network_plugins[ $plugin_file ] ) ) {
			return true;
		}
	}

	return false;
};

$spai_remove_from_active_plugins = static function ( $plugin_file ) {
	if ( ! function_exists( 'get_option' ) || ! function_exists( 'update_option' ) ) {
		return false;
	}

	$removed = false;

	// Remove from single-site active plugins.
	$active_plugins = get_option( 'active_plugins', array() );
	if ( is_array( $active_plugins ) ) {
		$index = array_search( $plugin_file, $active_plugins, true );
		if ( false !== $index ) {
			unset( $active_plugins[ $index ] );
			$active_plugins = array_values( $active_plugins );
			update_option( 'active_plugins', $active_plugins );
			if ( function_exists( 'wp_cache_delete' ) ) {
				wp_cache_delete( 'active_plugins', 'options' );
			}
			$removed = true;
		}
	}

	// Remove from network-wide active plugins (multisite).
	if ( function_exists( 'is_multisite' ) && is_multisite() && function_exists( 'get_site_option' ) && function_exists( 'update_site_option' ) ) {
		$network_plugins = get_site_option( 'active_sitewide_plugins', array() );
		if ( is_array( $network_plugins ) && isset( $network_plugins[ $plugin_file ] ) ) {
			unset( $network_plugins[ $plugin_file ] );
			update_site_option( 'active_sitewide_plugins', $network_plugins );
			$removed = true;
		}
	}

	return $removed;
};

// If this is the premium plugin, deactivate the free plugin (if active) and continue loading.
if ( 'site-pilot-ai-premium' === basename( __DIR__ ) ) {
	$spai_remove_from_active_plugins( $spai_free_plugin_file );
	if ( function_exists( 'update_option' ) ) {
		update_option( 'spai_premium_preferred', 1 );
	}
} else {
	// Free plugin: if premium is active, stop early so only premium runs.
	if ( $spai_is_plugin_active( $spai_premium_plugin_file ) ) {
		return;
	}
}

/**
 * Plugin version.
 */
define( 'SPAI_VERSION', '1.5.2' );

/**
 * Plugin directory path.
 */
define( 'SPAI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'SPAI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 */
define( 'SPAI_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Minimum WordPress version.
 */
define( 'SPAI_MIN_WP_VERSION', '5.0' );

/**
 * Minimum PHP version.
 */
define( 'SPAI_MIN_PHP_VERSION', '7.4' );

/**
 * Check requirements before loading.
 *
 * @return bool True if requirements met.
 */
if ( ! function_exists( 'spai_requirements_met' ) ) {
	function spai_requirements_met() {
	global $wp_version;

	if ( version_compare( PHP_VERSION, SPAI_MIN_PHP_VERSION, '<' ) ) {
		add_action( 'admin_notices', 'spai_php_version_notice' );
		return false;
	}

	if ( version_compare( $wp_version, SPAI_MIN_WP_VERSION, '<' ) ) {
		add_action( 'admin_notices', 'spai_wp_version_notice' );
		return false;
	}

	return true;
	}
}

/**
 * PHP version notice.
 */
if ( ! function_exists( 'spai_php_version_notice' ) ) {
	function spai_php_version_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: 1: Required PHP version 2: Current PHP version */
				esc_html__( 'Site Pilot AI requires PHP %1$s or higher. You are running PHP %2$s.', 'site-pilot-ai' ),
				esc_html( SPAI_MIN_PHP_VERSION ),
				esc_html( PHP_VERSION )
			);
			?>
		</p>
	</div>
	<?php
	}
}

/**
 * WordPress version notice.
 */
if ( ! function_exists( 'spai_wp_version_notice' ) ) {
	function spai_wp_version_notice() {
	global $wp_version;
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: 1: Required WP version 2: Current WP version */
				esc_html__( 'Site Pilot AI requires WordPress %1$s or higher. You are running WordPress %2$s.', 'site-pilot-ai' ),
				esc_html( SPAI_MIN_WP_VERSION ),
				esc_html( $wp_version )
			);
			?>
		</p>
	</div>
	<?php
	}
}

// Check if premium version is active - deactivate free if so.
if ( function_exists( 'spa_fs' ) ) {
	$spa_fs_instance = spa_fs();
	if ( is_object( $spa_fs_instance ) && method_exists( $spa_fs_instance, 'set_basename' ) ) {
		$spa_fs_instance->set_basename( true, __FILE__ );
		return;
	}
}

/**
 * Initialize Freemius SDK.
 */
if ( ! function_exists( 'spai_init_freemius' ) ) {
	function spai_init_freemius() {
	if ( ! function_exists( 'spa_fs' ) ) {
		require_once SPAI_PLUGIN_DIR . 'includes/freemius-init.php';
	}
	}
}
add_action( 'plugins_loaded', 'spai_init_freemius', 5 );

/**
 * Load plugin files.
 */
if ( ! function_exists( 'spai_load_plugin' ) ) {
	function spai_load_plugin() {
	// Check requirements
	if ( ! spai_requirements_met() ) {
		return;
	}

	// Load traits first
	require_once SPAI_PLUGIN_DIR . 'includes/traits/trait-spai-api-auth.php';
	require_once SPAI_PLUGIN_DIR . 'includes/traits/trait-spai-sanitization.php';
	require_once SPAI_PLUGIN_DIR . 'includes/traits/trait-spai-logging.php';

	// Load core classes
	require_once SPAI_PLUGIN_DIR . 'includes/class-spai-loader.php';
	require_once SPAI_PLUGIN_DIR . 'includes/class-spai-i18n.php';
	require_once SPAI_PLUGIN_DIR . 'includes/class-spai-activator.php';
	require_once SPAI_PLUGIN_DIR . 'includes/class-spai-deactivator.php';
	require_once SPAI_PLUGIN_DIR . 'includes/class-spai-rate-limiter.php';
	require_once SPAI_PLUGIN_DIR . 'includes/class-spai-error-hints.php';
	require_once SPAI_PLUGIN_DIR . 'includes/class-spai-security.php';
	require_once SPAI_PLUGIN_DIR . 'includes/class-spai-webhooks.php';
	require_once SPAI_PLUGIN_DIR . 'includes/class-spai-alerts.php';
	require_once SPAI_PLUGIN_DIR . 'includes/class-spai-license.php';

	// Load core functionality
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-core.php';
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-posts.php';
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-pages.php';
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-media.php';
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-drafts.php';
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-elementor-basic.php';
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-elementor-widgets.php';
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-screenshot.php';
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-guides.php';
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-workflows.php';
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-feedback.php';
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-encryption.php';
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-integration-manager.php';
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-provider-openai.php';
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-provider-gemini.php';
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-provider-elevenlabs.php';
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-provider-pexels.php';

	// Load MCP tool registries
	require_once SPAI_PLUGIN_DIR . 'includes/mcp/class-spai-mcp-tool-registry.php';
	require_once SPAI_PLUGIN_DIR . 'includes/mcp/class-spai-integration.php';
	require_once SPAI_PLUGIN_DIR . 'includes/mcp/class-spai-mcp-free-tools.php';
	require_once SPAI_PLUGIN_DIR . 'includes/mcp/class-spai-mcp-pro-tools.php';
	require_once SPAI_PLUGIN_DIR . 'includes/mcp/class-spai-mcp-ai-integration.php';

	// Load REST API
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-api.php';
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-posts.php';
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-pages.php';
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-media.php';
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-site.php';
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-menus.php';
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-content.php';
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-elementor.php';
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-webhooks.php';
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-screenshot.php';
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-feedback.php';
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-blocks.php';
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-mcp.php';
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-batch.php';
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-integrations.php';

	// Load admin
	require_once SPAI_PLUGIN_DIR . 'includes/admin/class-spai-admin.php';
	require_once SPAI_PLUGIN_DIR . 'includes/admin/class-spai-activity-log.php';
	require_once SPAI_PLUGIN_DIR . 'includes/admin/class-spai-settings.php';
	require_once SPAI_PLUGIN_DIR . 'includes/admin/class-spai-integrations-admin.php';
	require_once SPAI_PLUGIN_DIR . 'includes/admin/class-spai-tools-admin.php';

	// Load Pro modules (premium package only).
	$pro_bootstrap = SPAI_PLUGIN_DIR . 'includes/pro/class-spai-pro-bootstrap.php';
	if ( file_exists( $pro_bootstrap ) ) {
		require_once $pro_bootstrap;
		if ( class_exists( 'Spai_Pro_Bootstrap' ) ) {
			Spai_Pro_Bootstrap::init();
		}
	}

	// Check if database needs updating
	$installed_db_version = get_option( 'spai_db_version', '0' );
	if ( version_compare( $installed_db_version, SPAI_VERSION, '<' ) ) {
		require_once SPAI_PLUGIN_DIR . 'includes/class-spai-activator.php';
		Spai_Activator::activate();
		update_option( 'spai_db_version', SPAI_VERSION );
	}

	// Initialize the plugin
	$loader = new Spai_Loader();
	$loader->run();

	// Note: Plugin updates are handled by Freemius SDK automatically.
	// Upload new versions at: https://dashboard.freemius.com
	}
}

/**
 * Activation hook — supports network-wide activation on multisite.
 *
 * @param bool $network_wide True when activated network-wide.
 */
if ( ! function_exists( 'spai_activate' ) ) {
	function spai_activate( $network_wide = false ) {
	require_once SPAI_PLUGIN_DIR . 'includes/class-spai-activator.php';

	if ( $network_wide && function_exists( 'is_multisite' ) && is_multisite() ) {
		$sites = get_sites( array( 'fields' => 'ids' ) );
		foreach ( $sites as $blog_id ) {
			switch_to_blog( $blog_id );
			Spai_Activator::activate();
			restore_current_blog();
		}
	} else {
		Spai_Activator::activate();
	}
	}
}

/**
 * Provision Site Pilot AI tables/options when a new site is created in a multisite network.
 *
 * @param WP_Site $new_site New site object.
 */
if ( ! function_exists( 'spai_on_new_site' ) ) {
	function spai_on_new_site( $new_site ) {
	if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	// Only run if Site Pilot AI is network-activated.
	if ( ! is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
		return;
	}

	require_once SPAI_PLUGIN_DIR . 'includes/class-spai-activator.php';

	switch_to_blog( $new_site->blog_id );
	Spai_Activator::activate();
	restore_current_blog();
	}
}
add_action( 'wp_insert_site', 'spai_on_new_site' );

/**
 * Deactivation hook.
 */
if ( ! function_exists( 'spai_deactivate' ) ) {
	function spai_deactivate() {
	require_once SPAI_PLUGIN_DIR . 'includes/class-spai-deactivator.php';
	Spai_Deactivator::deactivate();
	}
}

// Register hooks
register_activation_hook( __FILE__, 'spai_activate' );
register_deactivation_hook( __FILE__, 'spai_deactivate' );

// Load plugin after WordPress is loaded
add_action( 'plugins_loaded', 'spai_load_plugin' );
