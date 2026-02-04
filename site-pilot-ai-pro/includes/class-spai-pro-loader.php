<?php
/**
 * Pro Plugin Loader
 *
 * @package SitePilotAI_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pro loader class.
 *
 * Hooks into the base plugin to register Pro features.
 */
class Spai_Pro_Loader {

	/**
	 * Elementor Pro handler.
	 *
	 * @var Spai_Elementor_Pro
	 */
	private $elementor_pro;

	/**
	 * SEO handler.
	 *
	 * @var Spai_SEO
	 */
	private $seo;

	/**
	 * Forms handler.
	 *
	 * @var Spai_Forms
	 */
	private $forms;

	/**
	 * Site manager handler.
	 *
	 * @var Spai_Site_Manager
	 */
	private $site_manager;

	/**
	 * Theme Builder handler.
	 *
	 * @var Spai_Theme_Builder
	 */
	private $theme_builder;

	/**
	 * Users handler.
	 *
	 * @var Spai_Users
	 */
	private $users;

	/**
	 * Widgets handler.
	 *
	 * @var Spai_Widgets
	 */
	private $widgets;

	/**
	 * Themes handler.
	 *
	 * @var Spai_Themes
	 */
	private $themes;

	/**
	 * WooCommerce handler.
	 *
	 * @var Spai_WooCommerce
	 */
	private $woocommerce;

	/**
	 * Initialize the loader.
	 */
	public function __construct() {
		$this->elementor_pro = new Spai_Elementor_Pro();
		$this->seo           = new Spai_SEO();
		$this->forms         = new Spai_Forms();
		$this->site_manager  = new Spai_Site_Manager();
		$this->theme_builder = new Spai_Theme_Builder();
		$this->users         = new Spai_Users();
		$this->widgets       = new Spai_Widgets();
		$this->themes        = new Spai_Themes();
		$this->woocommerce   = new Spai_WooCommerce();
	}

	/**
	 * Run the loader - register all hooks.
	 */
	public function run() {
		// Hook into base plugin's REST route registration.
		add_action( 'spai_register_rest_routes', array( $this, 'register_pro_routes' ) );

		// Add Pro capabilities to site info.
		add_filter( 'spai_site_capabilities', array( $this, 'add_pro_capabilities' ) );

		// Admin hooks.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add Pro indicator to admin.
		add_filter( 'plugin_action_links_' . SPAI_PRO_PLUGIN_BASENAME, array( $this, 'add_action_links' ) );
	}

	/**
	 * Register Pro REST routes.
	 */
	public function register_pro_routes() {
		// Elementor Pro endpoints.
		$elementor_controller = new Spai_REST_Elementor_Pro( $this->elementor_pro );
		$elementor_controller->register_routes();

		// SEO endpoints.
		$seo_controller = new Spai_REST_SEO( $this->seo );
		$seo_controller->register_routes();

		// Forms endpoints.
		$forms_controller = new Spai_REST_Forms( $this->forms );
		$forms_controller->register_routes();

		// Site manager endpoints (menus, settings, theme, templates).
		$site_manager_controller = new Spai_REST_Site_Manager( $this->site_manager );
		$site_manager_controller->register_routes();

		// Theme Builder endpoints (locations, conditions).
		$theme_builder_controller = new Spai_REST_Theme_Builder( $this->theme_builder );
		$theme_builder_controller->register_routes();

		// Users endpoints.
		$users_controller = new Spai_REST_Users( $this->users );
		$users_controller->register_routes();

		// Widgets endpoints.
		$widgets_controller = new Spai_REST_Widgets( $this->widgets );
		$widgets_controller->register_routes();

		// Themes endpoints.
		$themes_controller = new Spai_REST_Themes( $this->themes );
		$themes_controller->register_routes();

		// WooCommerce endpoints.
		$woocommerce_controller = new Spai_REST_WooCommerce( $this->woocommerce );
		$woocommerce_controller->register_routes();
	}

	/**
	 * Add Pro capabilities to site info response.
	 *
	 * @param array $capabilities Current capabilities.
	 * @return array Updated capabilities.
	 */
	public function add_pro_capabilities( $capabilities ) {
		$capabilities['pro_active'] = true;
		$capabilities['pro_version'] = SPAI_PRO_VERSION;

		// Elementor Pro features.
		$capabilities['elementor_pro_features'] = array(
			'templates'    => $this->elementor_pro->supports_templates(),
			'landing_page' => $this->elementor_pro->supports_landing_pages(),
			'clone'        => true,
			'widgets'      => $this->elementor_pro->get_available_widgets(),
			'globals'      => $this->elementor_pro->supports_globals(),
		);

		// SEO features.
		$capabilities['seo'] = array(
			'yoast'     => $this->seo->is_yoast_active(),
			'rankmath'  => $this->seo->is_rankmath_active(),
			'aioseo'    => $this->seo->is_aioseo_active(),
			'seopress'  => $this->seo->is_seopress_active(),
		);

		// Forms features.
		$capabilities['forms'] = array(
			'cf7'          => $this->forms->is_cf7_active(),
			'wpforms'      => $this->forms->is_wpforms_active(),
			'gravityforms' => $this->forms->is_gravityforms_active(),
			'ninjaforms'   => $this->forms->is_ninjaforms_active(),
		);

		// Theme Builder features.
		$capabilities['theme_builder'] = array(
			'available' => $this->theme_builder->is_available(),
		);

		// Users features.
		$capabilities['users'] = array(
			'management' => true,
		);

		// Widgets features.
		$capabilities['widgets'] = array(
			'management' => true,
		);

		// Themes features.
		$theme_info = $this->themes->detect_theme();
		$capabilities['themes'] = array(
			'active_theme'    => $theme_info['name'],
			'theme_slug'      => $theme_info['slug'],
			'is_supported'    => $theme_info['is_supported'],
			'is_block_theme'  => $theme_info['is_block_theme'],
			'supported_list'  => $this->themes->get_supported_themes(),
		);

		// WooCommerce features.
		$wc_status = $this->woocommerce->get_status();
		$capabilities['woocommerce'] = array(
			'active'  => $wc_status['active'],
			'version' => isset( $wc_status['version'] ) ? $wc_status['version'] : null,
		);

		return $capabilities;
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook Current admin page.
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( 'tools_page_site-pilot-ai' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'spai-pro-admin',
			SPAI_PRO_PLUGIN_URL . 'admin/css/spai-pro-admin.css',
			array( 'spai-admin' ),
			SPAI_PRO_VERSION
		);
	}

	/**
	 * Add action links to plugin page.
	 *
	 * @param array $links Existing links.
	 * @return array Modified links.
	 */
	public function add_action_links( $links ) {
		$pro_links = array(
			'<span style="color: #9b59b6; font-weight: bold;">PRO</span>',
		);
		return array_merge( $pro_links, $links );
	}
}
