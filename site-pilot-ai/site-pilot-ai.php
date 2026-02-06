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
 * Plugin URI:        https://github.com/Digidinc/site-pilot-ai
 * Description:       Control WordPress with AI. Expose posts, pages, media, and Elementor to AI assistants via MCP.
 * Version:           1.0.29
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            DigID Inc
 * Author URI:        https://digid.ca
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
 * Prevent fatal errors when both free + premium packages are installed.
 *
 * Freemius "premium version" installs a separate plugin directory (e.g.
 * `site-pilot-ai-premium`). If the free plugin is still active when the premium
 * plugin is activated, WordPress will load the free plugin first, and then load
 * the premium plugin for activation, which would otherwise redeclare globals
 * and fatal.
 *
 * Strategy:
 * - If this is the premium package and the free plugin is active, deactivate the
 *   free plugin and bail out for this request (so activation can complete).
 *   On the next request, only the premium package will load.
 * - If this is the free package and the premium plugin is active, deactivate the
 *   free plugin and bail out immediately.
 */
$spai_is_premium_package  = ( 'site-pilot-ai-premium' === basename( __DIR__ ) );
$spai_free_plugin_file    = 'site-pilot-ai/site-pilot-ai.php';
$spai_premium_plugin_file = 'site-pilot-ai-premium/site-pilot-ai.php';

$spai_remove_from_active_plugins = static function ( $plugin_file ) {
	if ( ! function_exists( 'get_option' ) || ! function_exists( 'update_option' ) ) {
		return false;
	}

	$active_plugins = get_option( 'active_plugins', array() );
	if ( ! is_array( $active_plugins ) ) {
		return false;
	}

	$index = array_search( $plugin_file, $active_plugins, true );
	if ( false === $index ) {
		return false;
	}

	unset( $active_plugins[ $index ] );
	$active_plugins = array_values( $active_plugins );
	update_option( 'active_plugins', $active_plugins );

	if ( function_exists( 'wp_cache_delete' ) ) {
		wp_cache_delete( 'active_plugins', 'options' );
	}

	return true;
};

if ( $spai_is_premium_package ) {
	// Ensure the free plugin is deactivated for subsequent requests.
	$spai_remove_from_active_plugins( $spai_free_plugin_file );

	// If the free package already loaded in this request, avoid redeclare fatals.
	if ( function_exists( 'spai_requirements_met' ) || defined( 'SPAI_VERSION' ) ) {
		return;
	}
} else {
	// If the premium package already loaded, don't load the free package.
	if ( defined( 'SPAI_VERSION' ) ) {
		return;
	}

	// If premium is active, deactivate the free plugin and bail out.
	$active_plugins = function_exists( 'get_option' ) ? get_option( 'active_plugins', array() ) : array();
	if ( is_array( $active_plugins ) && in_array( $spai_premium_plugin_file, $active_plugins, true ) ) {
		$spai_remove_from_active_plugins( $spai_free_plugin_file );
		return;
	}
}

/**
 * Plugin version.
 */
define( 'SPAI_VERSION', '1.0.29' );

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
	require_once SPAI_PLUGIN_DIR . 'includes/class-spai-security.php';
	require_once SPAI_PLUGIN_DIR . 'includes/class-spai-webhooks.php';
	require_once SPAI_PLUGIN_DIR . 'includes/class-spai-license.php';

	// Load core functionality
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-core.php';
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-posts.php';
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-pages.php';
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-media.php';
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-drafts.php';
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-elementor-basic.php';

	// Load REST API
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-api.php';
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-posts.php';
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-pages.php';
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-media.php';
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-site.php';
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-elementor.php';
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-webhooks.php';
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-mcp.php';

	// Load admin
	require_once SPAI_PLUGIN_DIR . 'includes/admin/class-spai-admin.php';
	require_once SPAI_PLUGIN_DIR . 'includes/admin/class-spai-settings.php';

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
 * Activation hook.
 */
if ( ! function_exists( 'spai_activate' ) ) {
	function spai_activate() {
	require_once SPAI_PLUGIN_DIR . 'includes/class-spai-activator.php';
	Spai_Activator::activate();
	}
}

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
