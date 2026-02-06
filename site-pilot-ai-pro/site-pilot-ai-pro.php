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
 * Version:           1.0.23
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            DigID Inc
 * Author URI:        https://sitepilot.ai
 * Text Domain:       site-pilot-ai-pro
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'SPAI_PRO_VERSION', '1.0.23' );
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
 * Admin notice when Pro bootstrap fails.
 */
function spai_pro_bootstrap_error_notice() {
	$error = get_transient( 'spai_pro_bootstrap_error' );
	if ( empty( $error ) ) {
		return;
	}
	delete_transient( 'spai_pro_bootstrap_error' );
	?>
	<div class="notice notice-error">
		<p>
			<strong><?php esc_html_e( 'Site Pilot AI Pro failed to initialize.', 'site-pilot-ai-pro' ); ?></strong>
		</p>
		<p><?php echo esc_html( $error ); ?></p>
	</div>
	<?php
}

/**
 * Load file safely.
 *
 * @param string $relative_path Relative path from plugin root.
 * @return bool
 */
function spai_pro_safe_require( $relative_path ) {
	$file = SPAI_PRO_PLUGIN_DIR . ltrim( $relative_path, '/' );
	if ( ! file_exists( $file ) ) {
		set_transient(
			'spai_pro_bootstrap_error',
			sprintf(
				/* translators: %s: Relative file path. */
				__( 'Missing Pro plugin file: %s', 'site-pilot-ai-pro' ),
				$relative_path
			),
			MINUTE_IN_SECONDS * 10
		);
		error_log( 'Site Pilot AI Pro bootstrap missing file: ' . $file );
		return false;
	}
	require_once $file;
	return true;
}

/**
 * Check if user has valid Pro license.
 *
 * @return bool
 */
function spai_pro_has_license() {
	if ( ! function_exists( 'spai_license' ) ) {
		return false;
	}
	return spai_license()->is_pro();
}

/**
 * Admin notice when license is not valid.
 */
function spai_pro_license_required_notice() {
	$upgrade_url = function_exists( 'spai_license' ) ? spai_license()->get_upgrade_url() : 'https://sitepilot.ai/pricing/';
	?>
	<div class="notice notice-warning">
		<p>
			<strong><?php esc_html_e( 'Site Pilot AI Pro - License Required', 'site-pilot-ai-pro' ); ?></strong>
		</p>
		<p>
			<?php esc_html_e( 'Please activate your Pro license to unlock all features.', 'site-pilot-ai-pro' ); ?>
			<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary" style="margin-left: 10px;">
				<?php esc_html_e( 'Get Pro License', 'site-pilot-ai-pro' ); ?>
			</a>
		</p>
	</div>
	<?php
}

/**
 * Initialize the Pro plugin.
 */
function spai_pro_init() {
	try {
	// Check if base plugin is active and fully loaded.
	if ( ! spai_pro_is_base_active() ) {
		add_action( 'admin_notices', 'spai_pro_base_required_notice' );
		return;
	}

	// Verify the base REST API class is available (required for Pro controllers).
	if ( ! class_exists( 'Spai_REST_API' ) ) {
		return;
	}

	// Check for valid license.
	if ( ! spai_pro_has_license() ) {
		add_action( 'admin_notices', 'spai_pro_license_required_notice' );
		// Still load Pro for trial/limited features, but show notice.
	}

	// Load Pro dependencies.
	if ( ! spai_pro_safe_require( 'includes/class-spai-pro-loader.php' ) ) {
		add_action( 'admin_notices', 'spai_pro_bootstrap_error_notice' );
		return;
	}
	if ( ! spai_pro_safe_require( 'includes/class-spai-pro-activator.php' ) ) {
		add_action( 'admin_notices', 'spai_pro_bootstrap_error_notice' );
		return;
	}

	// Load core modules.
	$core_files = array(
		'includes/core/class-spai-elementor-pro.php',
		'includes/core/class-spai-seo.php',
		'includes/core/class-spai-forms.php',
		'includes/core/class-spai-site-manager.php',
		'includes/core/class-spai-theme-builder.php',
		'includes/core/class-spai-users.php',
		'includes/core/class-spai-widgets.php',
		'includes/core/class-spai-themes.php',
		'includes/core/class-spai-woocommerce.php',
		'includes/core/class-spai-multilang.php',
	);
	foreach ( $core_files as $core_file ) {
		if ( ! spai_pro_safe_require( $core_file ) ) {
			add_action( 'admin_notices', 'spai_pro_bootstrap_error_notice' );
			return;
		}
	}

	// Load REST API controllers.
	$api_files = array(
		'includes/api/class-spai-rest-elementor-pro.php',
		'includes/api/class-spai-rest-seo.php',
		'includes/api/class-spai-rest-forms.php',
		'includes/api/class-spai-rest-site-manager.php',
		'includes/api/class-spai-rest-theme-builder.php',
		'includes/api/class-spai-rest-users.php',
		'includes/api/class-spai-rest-widgets.php',
		'includes/api/class-spai-rest-themes.php',
		'includes/api/class-spai-rest-woocommerce.php',
		'includes/api/class-spai-rest-multilang.php',
	);
	foreach ( $api_files as $api_file ) {
		if ( ! spai_pro_safe_require( $api_file ) ) {
			add_action( 'admin_notices', 'spai_pro_bootstrap_error_notice' );
			return;
		}
	}

	// Load admin.
	if ( ! spai_pro_safe_require( 'includes/admin/class-spai-pro-admin.php' ) ) {
		add_action( 'admin_notices', 'spai_pro_bootstrap_error_notice' );
		return;
	}

	// Initialize loader.
	$loader = new Spai_Pro_Loader();
	$loader->run();

	// Note: Plugin updates are handled by Freemius SDK (via base plugin).
	} catch ( Throwable $e ) {
		set_transient( 'spai_pro_bootstrap_error', $e->getMessage(), MINUTE_IN_SECONDS * 10 );
		add_action( 'admin_notices', 'spai_pro_bootstrap_error_notice' );
		error_log( 'Site Pilot AI Pro bootstrap fatal: ' . $e->getMessage() );
	}
}
add_action( 'plugins_loaded', 'spai_pro_init', 20 );

/**
 * Activation hook.
 */
function spai_pro_activate() {
	// Check if base plugin is active.
	// During activation, plugins_loaded may not have fired yet,
	// so check for the constant (defined at file level) rather than classes.
	if ( ! defined( 'SPAI_VERSION' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'Site Pilot AI Pro requires the free Site Pilot AI plugin to be installed and activated.', 'site-pilot-ai-pro' ),
			esc_html__( 'Plugin Activation Error', 'site-pilot-ai-pro' ),
			array( 'back_link' => true )
		);
	}

	try {
		if ( ! spai_pro_safe_require( 'includes/class-spai-pro-activator.php' ) || ! class_exists( 'Spai_Pro_Activator' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die(
				esc_html__( 'Site Pilot AI Pro activation failed because required files are missing. Please reinstall the Pro package.', 'site-pilot-ai-pro' ),
				esc_html__( 'Plugin Activation Error', 'site-pilot-ai-pro' ),
				array( 'back_link' => true )
			);
		}
		Spai_Pro_Activator::activate();
	} catch ( Throwable $e ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html( $e->getMessage() ),
			esc_html__( 'Plugin Activation Error', 'site-pilot-ai-pro' ),
			array( 'back_link' => true )
		);
	}
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
