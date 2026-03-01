<?php
/**
 * License stub — all features are free.
 *
 * This replaces the Freemius-based license system.
 * All methods return values indicating full access.
 *
 * @package MumegaSitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * License class — always returns full access.
 */
class Spai_License {

	/**
	 * Singleton instance.
	 *
	 * @var Spai_License
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Spai_License
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {}

	/**
	 * Always paying — all features are free.
	 *
	 * @return bool
	 */
	public function is_paying() {
		return true;
	}

	/**
	 * Always Pro — all features are free.
	 *
	 * @return bool
	 */
	public function is_pro() {
		return true;
	}

	/**
	 * Not agency tier (no longer relevant).
	 *
	 * @return bool
	 */
	public function is_agency() {
		return false;
	}

	/**
	 * Plan is free (all features included).
	 *
	 * @return string
	 */
	public function get_plan() {
		return 'free';
	}

	/**
	 * No license key needed.
	 *
	 * @return string|null
	 */
	public function get_license_key() {
		return null;
	}

	/**
	 * No expiration.
	 *
	 * @return string|null
	 */
	public function get_expiration() {
		return null;
	}

	/**
	 * License never expires.
	 *
	 * @return bool
	 */
	public function is_expired() {
		return false;
	}

	/**
	 * No site limit.
	 *
	 * @return int|null
	 */
	public function get_site_limit() {
		return null;
	}

	/**
	 * Upgrade URL — points to mumega.com.
	 *
	 * @return string
	 */
	public function get_upgrade_url() {
		return 'https://mumega.com/';
	}

	/**
	 * Account URL.
	 *
	 * @return string
	 */
	public function get_account_url() {
		return 'https://mumega.com/';
	}

	/**
	 * License info array.
	 *
	 * @return array
	 */
	public function get_info() {
		return array(
			'provider'    => 'free',
			'is_paying'   => true,
			'plan'        => 'free',
			'is_pro'      => true,
			'is_agency'   => false,
			'license_key' => null,
			'expiration'  => null,
			'is_expired'  => false,
			'site_limit'  => null,
		);
	}

	/**
	 * No-op activation.
	 *
	 * @param string $license_key Unused.
	 * @return array
	 */
	public function activate( $license_key ) {
		return array(
			'success' => true,
			'message' => __( 'All features are free. No license needed.', 'site-pilot-ai' ),
		);
	}

	/**
	 * No-op deactivation.
	 *
	 * @return array
	 */
	public function deactivate() {
		return array(
			'success' => true,
			'message' => __( 'No license to deactivate.', 'site-pilot-ai' ),
		);
	}
}

/**
 * Get license instance.
 *
 * @return Spai_License
 */
function spai_license() {
	return Spai_License::get_instance();
}
