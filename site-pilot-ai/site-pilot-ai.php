<?php
/**
 * Mumega MCP for WordPress
 *
 * Connect WordPress to AI assistants via the Model Context Protocol (MCP).
 * Expose your WordPress site's functionality to AI assistants like Claude.
 *
 * @package           MumegaSitePilotAI
 * @author            Mumega
 * @copyright         2026 Mumega
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Mumega MCP for WordPress
 * Plugin URI:        https://mcpforwp.mumega.com
 * Description:       Connect WordPress to AI assistants via the Model Context Protocol (MCP). Manage posts, pages, media, and Elementor through natural language.
 * Version:           2.3.2
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            Mumega
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
 * Plugin version.
 */
define( 'SPAI_VERSION', '2.3.2' );

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
				esc_html__( 'Mumega MCP for WordPress requires PHP %1$s or higher. You are running PHP %2$s.', 'site-pilot-ai' ),
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
				esc_html__( 'Mumega MCP for WordPress requires WordPress %1$s or higher. You are running WordPress %2$s.', 'site-pilot-ai' ),
				esc_html( SPAI_MIN_WP_VERSION ),
				esc_html( $wp_version )
			);
			?>
		</p>
	</div>
	<?php
	}
}

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
	require_once SPAI_PLUGIN_DIR . 'includes/class-spai-updater.php';
	require_once SPAI_PLUGIN_DIR . 'includes/class-spai-ai-presence.php';

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
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-design-references.php';
	require_once SPAI_PLUGIN_DIR . 'includes/core/class-spai-figma.php';
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
	require_once SPAI_PLUGIN_DIR . 'includes/mcp/class-spai-mcp-figma-integration.php';

	// Load Pro modules (always available — all features are free).
	$pro_bootstrap = SPAI_PLUGIN_DIR . 'includes/pro/class-spai-pro-bootstrap.php';
	if ( file_exists( $pro_bootstrap ) ) {
		require_once $pro_bootstrap;
		if ( class_exists( 'Spai_Pro_Bootstrap' ) ) {
			Spai_Pro_Bootstrap::init();
		}
	}

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
	require_once SPAI_PLUGIN_DIR . 'includes/api/class-spai-rest-figma.php';

	// Load admin
	require_once SPAI_PLUGIN_DIR . 'includes/admin/class-spai-admin.php';
	require_once SPAI_PLUGIN_DIR . 'includes/admin/class-spai-activity-log.php';
	require_once SPAI_PLUGIN_DIR . 'includes/admin/class-spai-settings.php';
	require_once SPAI_PLUGIN_DIR . 'includes/admin/class-spai-integrations-admin.php';
	require_once SPAI_PLUGIN_DIR . 'includes/admin/class-spai-tools-admin.php';

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

	// Self-hosted update checker.
	new Spai_Updater();

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
 * Provision Mumega MCP tables/options when a new site is created in a multisite network.
 *
 * @param WP_Site $new_site New site object.
 */
if ( ! function_exists( 'spai_on_new_site' ) ) {
	function spai_on_new_site( $new_site ) {
	if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	// Only run if Mumega MCP for WordPress is network-activated.
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
