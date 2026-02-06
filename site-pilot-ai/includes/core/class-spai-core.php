<?php
/**
 * Core functionality
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core class with shared functionality.
 */
class Spai_Core {

	use Spai_Api_Auth;
	use Spai_Sanitization;
	use Spai_Logging;

	/**
	 * Get site information.
	 *
	 * @return array Site info.
	 */
	public function get_site_info() {
		global $wp_version;

		$theme = wp_get_theme();

		return array(
			'name'         => get_bloginfo( 'name' ),
			'description'  => get_bloginfo( 'description' ),
			'url'          => home_url(),
			'admin_url'    => admin_url(),
			'wp_version'   => $wp_version,
			'php_version'  => PHP_VERSION,
			'theme'        => array(
				'name'    => $theme->get( 'Name' ),
				'version' => $theme->get( 'Version' ),
			),
			'timezone'     => wp_timezone_string(),
			'language'     => get_locale(),
			'capabilities' => $this->get_capabilities(),
			'plugin'       => array(
				'name'    => 'Site Pilot AI',
				'version' => SPAI_VERSION,
			),
		);
	}

	/**
	 * Get site capabilities (detected plugins).
	 *
	 * @return array Capabilities.
	 */
	public function get_capabilities() {
		$cached = get_transient( 'spai_capabilities_cache' );
		if ( false !== $cached ) {
			return $cached;
		}

		$capabilities = array(
			'elementor'      => defined( 'ELEMENTOR_VERSION' ),
			'elementor_pro'  => defined( 'ELEMENTOR_PRO_VERSION' ),
			'woocommerce'    => class_exists( 'WooCommerce' ),
			'yoast'          => defined( 'WPSEO_VERSION' ),
			'rankmath'       => class_exists( 'RankMath' ),
			'aioseo'         => defined( 'AIOSEO_VERSION' ),
			'seopress'       => defined( 'SEOPRESS_VERSION' ),
			'cf7'            => class_exists( 'WPCF7' ),
			'wpforms'        => class_exists( 'WPForms' ),
			'gravityforms'   => class_exists( 'GFForms' ),
			'ninjaforms'     => class_exists( 'Ninja_Forms' ),
		);

		// Allow premium package to extend capabilities (e.g., Pro status).
		if ( function_exists( 'apply_filters' ) ) {
			$capabilities = apply_filters( 'spai_site_capabilities', $capabilities );
		}

		// Cache for 1 hour
		set_transient( 'spai_capabilities_cache', $capabilities, HOUR_IN_SECONDS );

		return $capabilities;
	}

	/**
	 * Get analytics data.
	 *
	 * @param int $days Number of days.
	 * @return array Analytics data.
	 */
	public function get_analytics( $days = 30 ) {
		global $wpdb;

		$table = $wpdb->prefix . 'spai_activity_log';
		$since = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$total_requests = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table WHERE created_at >= %s",
				$since
			)
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$by_action = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT action, COUNT(*) as count FROM $table WHERE created_at >= %s GROUP BY action ORDER BY count DESC LIMIT 10",
				$since
			),
			ARRAY_A
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$by_day = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE(created_at) as date, COUNT(*) as count FROM $table WHERE created_at >= %s GROUP BY DATE(created_at) ORDER BY date DESC",
				$since
			),
			ARRAY_A
		);

		return array(
			'period_days'    => $days,
			'total_requests' => (int) $total_requests,
			'by_action'      => $by_action,
			'by_day'         => $by_day,
		);
	}

	/**
	 * Detect installed plugins with capabilities.
	 *
	 * @return array Plugin info.
	 */
	public function detect_plugins() {
		$plugins = array();
		$capabilities = $this->get_capabilities();

		// Elementor
		if ( $capabilities['elementor'] ) {
			$plugins['elementor'] = array(
				'name'    => 'Elementor',
				'version' => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : 'unknown',
				'pro'     => $capabilities['elementor_pro'],
			);
		}

		// SEO plugins
		$seo_plugins = array(
			'yoast'    => array( 'name' => 'Yoast SEO', 'const' => 'WPSEO_VERSION' ),
			'rankmath' => array( 'name' => 'RankMath', 'class' => 'RankMath' ),
			'aioseo'   => array( 'name' => 'All in One SEO', 'const' => 'AIOSEO_VERSION' ),
			'seopress' => array( 'name' => 'SEOPress', 'const' => 'SEOPRESS_VERSION' ),
		);

		foreach ( $seo_plugins as $key => $info ) {
			if ( $capabilities[ $key ] ) {
				$version = 'unknown';
				if ( isset( $info['const'] ) && defined( $info['const'] ) ) {
					$version = constant( $info['const'] );
				}
				$plugins['seo'] = array(
					'name'    => $info['name'],
					'version' => $version,
					'slug'    => $key,
				);
				break;
			}
		}

		// Form plugins
		$form_plugins = array(
			'cf7'          => array( 'name' => 'Contact Form 7', 'const' => 'WPCF7_VERSION' ),
			'wpforms'      => array( 'name' => 'WPForms', 'const' => 'WPFORMS_VERSION' ),
			'gravityforms' => array( 'name' => 'Gravity Forms', 'class' => 'GFForms' ),
			'ninjaforms'   => array( 'name' => 'Ninja Forms', 'const' => 'NINJA_FORMS_VERSION' ),
		);

		$plugins['forms'] = array();
		foreach ( $form_plugins as $key => $info ) {
			if ( $capabilities[ $key ] ) {
				$version = 'unknown';
				if ( isset( $info['const'] ) && defined( $info['const'] ) ) {
					$version = constant( $info['const'] );
				}
				$plugins['forms'][] = array(
					'name'    => $info['name'],
					'version' => $version,
					'slug'    => $key,
				);
			}
		}

		// WooCommerce
		if ( $capabilities['woocommerce'] ) {
			$plugins['woocommerce'] = array(
				'name'    => 'WooCommerce',
				'version' => defined( 'WC_VERSION' ) ? WC_VERSION : 'unknown',
			);
		}

		return $plugins;
	}
}
