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
 * Version:           1.0.8
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
 * Plugin version.
 */
define( 'SPAI_VERSION', '1.0.8' );

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

/**
 * PHP version notice.
 */
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

/**
 * WordPress version notice.
 */
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

/**
 * Initialize Freemius SDK.
 */
function spai_init_freemius() {
	if ( ! function_exists( 'spai_fs' ) ) {
		require_once SPAI_PLUGIN_DIR . 'includes/freemius-init.php';
	}
}
add_action( 'plugins_loaded', 'spai_init_freemius', 5 );

/**
 * Load plugin files.
 */
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

	// Load admin
	require_once SPAI_PLUGIN_DIR . 'includes/admin/class-spai-admin.php';
	require_once SPAI_PLUGIN_DIR . 'includes/admin/class-spai-settings.php';

	// Load updater
	require_once SPAI_PLUGIN_DIR . 'includes/class-spai-updater.php';

	// Initialize the plugin
	$loader = new Spai_Loader();
	$loader->run();

	// Initialize GitHub updater (checks Digidinc/wp-ai-operator releases)
	new Spai_Updater( SPAI_PLUGIN_BASENAME, SPAI_VERSION, 'site-pilot-ai' );
}

/**
 * Activation hook.
 */
function spai_activate() {
	require_once SPAI_PLUGIN_DIR . 'includes/class-spai-activator.php';
	Spai_Activator::activate();
}

/**
 * Deactivation hook.
 */
function spai_deactivate() {
	require_once SPAI_PLUGIN_DIR . 'includes/class-spai-deactivator.php';
	Spai_Deactivator::deactivate();
}

// Register hooks
register_activation_hook( __FILE__, 'spai_activate' );
register_deactivation_hook( __FILE__, 'spai_deactivate' );

// Load plugin after WordPress is loaded
add_action( 'plugins_loaded', 'spai_load_plugin' );
