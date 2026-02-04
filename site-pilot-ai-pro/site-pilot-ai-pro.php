<?php
/**
 * Site Pilot AI Pro
 *
 * @package           SitePilotAI_Pro
 * @author            DigID Inc
 * @copyright         2024 DigID Inc
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Site Pilot AI Pro
 * Plugin URI:        https://sitepilot.ai/pro
 * Description:       Pro add-on for Site Pilot AI. Adds advanced Elementor integration, SEO tools, and forms support.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            DigID Inc
 * Author URI:        https://digid.ca
 * Text Domain:       site-pilot-ai-pro
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins:  site-pilot-ai
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'SPAI_PRO_VERSION', '1.0.0' );
define( 'SPAI_PRO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SPAI_PRO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SPAI_PRO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Check if Site Pilot AI (free) is active.
 *
 * @return bool
 */
function spai_pro_is_base_active() {
	return defined( 'SPAI_VERSION' ) && class_exists( 'Spai_Loader' );
}

/**
 * Admin notice when base plugin is not active.
 */
function spai_pro_base_required_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<strong><?php esc_html_e( 'Site Pilot AI Pro requires Site Pilot AI', 'site-pilot-ai-pro' ); ?></strong>
		</p>
		<p>
			<?php esc_html_e( 'Please install and activate the free Site Pilot AI plugin to use Pro features.', 'site-pilot-ai-pro' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Initialize the Pro plugin.
 */
function spai_pro_init() {
	// Check if base plugin is active.
	if ( ! spai_pro_is_base_active() ) {
		add_action( 'admin_notices', 'spai_pro_base_required_notice' );
		return;
	}

	// Load Pro dependencies.
	require_once SPAI_PRO_PLUGIN_DIR . 'includes/class-spai-pro-loader.php';
	require_once SPAI_PRO_PLUGIN_DIR . 'includes/class-spai-pro-activator.php';

	// Load core modules.
	require_once SPAI_PRO_PLUGIN_DIR . 'includes/core/class-spai-elementor-pro.php';
	require_once SPAI_PRO_PLUGIN_DIR . 'includes/core/class-spai-seo.php';
	require_once SPAI_PRO_PLUGIN_DIR . 'includes/core/class-spai-forms.php';
	require_once SPAI_PRO_PLUGIN_DIR . 'includes/core/class-spai-site-manager.php';
	require_once SPAI_PRO_PLUGIN_DIR . 'includes/core/class-spai-theme-builder.php';
	require_once SPAI_PRO_PLUGIN_DIR . 'includes/core/class-spai-users.php';
	require_once SPAI_PRO_PLUGIN_DIR . 'includes/core/class-spai-widgets.php';

	// Load REST API controllers.
	require_once SPAI_PRO_PLUGIN_DIR . 'includes/api/class-spai-rest-elementor-pro.php';
	require_once SPAI_PRO_PLUGIN_DIR . 'includes/api/class-spai-rest-seo.php';
	require_once SPAI_PRO_PLUGIN_DIR . 'includes/api/class-spai-rest-forms.php';
	require_once SPAI_PRO_PLUGIN_DIR . 'includes/api/class-spai-rest-site-manager.php';
	require_once SPAI_PRO_PLUGIN_DIR . 'includes/api/class-spai-rest-theme-builder.php';
	require_once SPAI_PRO_PLUGIN_DIR . 'includes/api/class-spai-rest-users.php';
	require_once SPAI_PRO_PLUGIN_DIR . 'includes/api/class-spai-rest-widgets.php';

	// Load admin.
	require_once SPAI_PRO_PLUGIN_DIR . 'includes/admin/class-spai-pro-admin.php';

	// Initialize loader.
	$loader = new Spai_Pro_Loader();
	$loader->run();
}
add_action( 'plugins_loaded', 'spai_pro_init', 20 );

/**
 * Activation hook.
 */
function spai_pro_activate() {
	// Check if base plugin is active.
	if ( ! spai_pro_is_base_active() ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'Site Pilot AI Pro requires the free Site Pilot AI plugin to be installed and activated.', 'site-pilot-ai-pro' ),
			esc_html__( 'Plugin Activation Error', 'site-pilot-ai-pro' ),
			array( 'back_link' => true )
		);
	}

	require_once SPAI_PRO_PLUGIN_DIR . 'includes/class-spai-pro-activator.php';
	Spai_Pro_Activator::activate();
}
register_activation_hook( __FILE__, 'spai_pro_activate' );

/**
 * Deactivation hook.
 */
function spai_pro_deactivate() {
	// Clean up transients.
	delete_transient( 'spai_pro_license_check' );
}
register_deactivation_hook( __FILE__, 'spai_pro_deactivate' );
