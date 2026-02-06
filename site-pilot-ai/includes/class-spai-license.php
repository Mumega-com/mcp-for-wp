<?php
/**
 * License Management Abstraction Layer
 *
 * Provides a unified interface for license checking.
 * Currently uses Freemius, can be switched to custom backend later.
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * License management class.
 */
class Spai_License {

	/**
	 * Singleton instance.
	 *
	 * @var Spai_License
	 */
	private static $instance = null;

	/**
	 * License provider: 'freemius' or 'custom'.
	 *
	 * @var string
	 */
	private $provider = 'freemius';

	/**
	 * Cached license data.
	 *
	 * @var array|null
	 */
	private $license_data = null;

	/**
	 * Cached Freemius instance.
	 *
	 * @var object|null
	 */
	private $freemius_instance = null;

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
	private function __construct() {
		// Provider can be overridden via constant for migration.
		if ( defined( 'SPAI_LICENSE_PROVIDER' ) ) {
			$this->provider = SPAI_LICENSE_PROVIDER;
		}
	}

	/**
	 * Get Freemius instance safely.
	 *
	 * @return object|null
	 */
	private function get_freemius_instance() {
		if ( null !== $this->freemius_instance ) {
			return $this->freemius_instance;
		}
		if ( ! function_exists( 'spa_fs' ) ) {
			return null;
		}
		$instance = spa_fs();
		if ( ! is_object( $instance ) ) {
			return null;
		}
		$this->freemius_instance = $instance;
		return $this->freemius_instance;
	}

	/**
	 * Check if user has a valid paid license.
	 *
	 * @return bool
	 */
	public function is_paying() {
		if ( 'freemius' === $this->provider ) {
			return $this->freemius_is_paying();
		}
		return $this->custom_is_paying();
	}

	/**
	 * Check if user has Pro plan.
	 *
	 * @return bool
	 */
	public function is_pro() {
		$plan = $this->get_plan();
		return in_array( $plan, array( 'pro', 'agency', 'professional', 'business' ), true );
	}

	/**
	 * Check if user has Agency plan.
	 *
	 * @return bool
	 */
	public function is_agency() {
		$plan = $this->get_plan();
		return in_array( $plan, array( 'agency', 'business' ), true );
	}

	/**
	 * Get current plan name.
	 *
	 * @return string Plan name or 'free'.
	 */
	public function get_plan() {
		if ( 'freemius' === $this->provider ) {
			return $this->freemius_get_plan();
		}
		return $this->custom_get_plan();
	}

	/**
	 * Get license key (masked).
	 *
	 * @return string|null
	 */
	public function get_license_key() {
		if ( 'freemius' === $this->provider ) {
			return $this->freemius_get_license_key();
		}
		return $this->custom_get_license_key();
	}

	/**
	 * Get license expiration date.
	 *
	 * @return string|null ISO date or null.
	 */
	public function get_expiration() {
		if ( 'freemius' === $this->provider ) {
			return $this->freemius_get_expiration();
		}
		return $this->custom_get_expiration();
	}

	/**
	 * Check if license is expired.
	 *
	 * @return bool
	 */
	public function is_expired() {
		$expiration = $this->get_expiration();
		if ( ! $expiration ) {
			return false;
		}
		return strtotime( $expiration ) < time();
	}

	/**
	 * Get site count for agency plans.
	 *
	 * @return int|null Site limit or null for unlimited.
	 */
	public function get_site_limit() {
		if ( 'freemius' === $this->provider ) {
			return $this->freemius_get_site_limit();
		}
		return $this->custom_get_site_limit();
	}

	/**
	 * Get upgrade URL.
	 *
	 * @return string
	 */
	public function get_upgrade_url() {
		$freemius = $this->get_freemius_instance();
		if ( 'freemius' === $this->provider && $freemius && method_exists( $freemius, 'get_upgrade_url' ) ) {
			return $freemius->get_upgrade_url();
		}
		return 'https://sitepilot.ai/pricing/';
	}

	/**
	 * Get account URL.
	 *
	 * @return string
	 */
	public function get_account_url() {
		$freemius = $this->get_freemius_instance();
		if ( 'freemius' === $this->provider && $freemius && method_exists( $freemius, 'get_account_url' ) ) {
			return $freemius->get_account_url();
		}
		return 'https://sitepilot.ai/account/';
	}

	/**
	 * Get license info array.
	 *
	 * @return array
	 */
	public function get_info() {
		return array(
			'provider'    => $this->provider,
			'is_paying'   => $this->is_paying(),
			'plan'        => $this->get_plan(),
			'is_pro'      => $this->is_pro(),
			'is_agency'   => $this->is_agency(),
			'license_key' => $this->get_license_key(),
			'expiration'  => $this->get_expiration(),
			'is_expired'  => $this->is_expired(),
			'site_limit'  => $this->get_site_limit(),
		);
	}

	// =========================================================================
	// Freemius Provider Methods
	// =========================================================================

	/**
	 * Freemius: Check if paying.
	 *
	 * @return bool
	 */
	private function freemius_is_paying() {
		$freemius = $this->get_freemius_instance();
		if ( ! $freemius || ! method_exists( $freemius, 'is_paying' ) ) {
			return false;
		}
		return (bool) $freemius->is_paying();
	}

	/**
	 * Freemius: Get plan name.
	 *
	 * @return string
	 */
	private function freemius_get_plan() {
		$freemius = $this->get_freemius_instance();
		if ( ! $freemius || ! method_exists( $freemius, 'is_paying' ) || ! $freemius->is_paying() ) {
			return 'free';
		}

		$plan = method_exists( $freemius, 'get_plan' ) ? $freemius->get_plan() : null;
		return $plan ? $plan->name : 'free';
	}

	/**
	 * Freemius: Get license key.
	 *
	 * @return string|null
	 */
	private function freemius_get_license_key() {
		$freemius = $this->get_freemius_instance();
		if ( ! $freemius || ! method_exists( $freemius, '_get_license' ) ) {
			return null;
		}

		try {
			$license = $freemius->_get_license();
		} catch ( Exception $e ) {
			return null;
		}

		if ( $license && isset( $license->secret_key ) ) {
			// Return masked key.
			return substr( $license->secret_key, 0, 8 ) . '...';
		}
		return null;
	}

	/**
	 * Freemius: Get expiration date.
	 *
	 * @return string|null
	 */
	private function freemius_get_expiration() {
		$freemius = $this->get_freemius_instance();
		if ( ! $freemius || ! method_exists( $freemius, '_get_license' ) ) {
			return null;
		}

		try {
			$license = $freemius->_get_license();
		} catch ( Exception $e ) {
			return null;
		}

		if ( $license && isset( $license->expiration ) ) {
			return $license->expiration;
		}
		return null;
	}

	/**
	 * Freemius: Get site limit.
	 *
	 * @return int|null
	 */
	private function freemius_get_site_limit() {
		$freemius = $this->get_freemius_instance();
		if ( ! $freemius || ! method_exists( $freemius, '_get_license' ) ) {
			return null;
		}

		try {
			$license = $freemius->_get_license();
		} catch ( Exception $e ) {
			return null;
		}

		if ( $license && isset( $license->quota ) ) {
			return (int) $license->quota;
		}
		return null;
	}

	// =========================================================================
	// Custom Provider Methods (For Future Migration)
	// =========================================================================

	/**
	 * Custom: Check if paying.
	 *
	 * @return bool
	 */
	private function custom_is_paying() {
		$license = $this->get_custom_license_data();
		return ! empty( $license['valid'] );
	}

	/**
	 * Custom: Get plan name.
	 *
	 * @return string
	 */
	private function custom_get_plan() {
		$license = $this->get_custom_license_data();
		return $license['plan'] ?? 'free';
	}

	/**
	 * Custom: Get license key.
	 *
	 * @return string|null
	 */
	private function custom_get_license_key() {
		$key = get_option( 'spai_license_key' );
		if ( $key ) {
			return substr( $key, 0, 8 ) . '...';
		}
		return null;
	}

	/**
	 * Custom: Get expiration.
	 *
	 * @return string|null
	 */
	private function custom_get_expiration() {
		$license = $this->get_custom_license_data();
		return $license['expires_at'] ?? null;
	}

	/**
	 * Custom: Get site limit.
	 *
	 * @return int|null
	 */
	private function custom_get_site_limit() {
		$license = $this->get_custom_license_data();
		return $license['site_limit'] ?? null;
	}

	/**
	 * Get custom license data from cache or API.
	 *
	 * @return array
	 */
	private function get_custom_license_data() {
		if ( null !== $this->license_data ) {
			return $this->license_data;
		}

		// Check transient cache.
		$cached = get_transient( 'spai_license_data' );
		if ( false !== $cached ) {
			$this->license_data = $cached;
			return $cached;
		}

		// Get license key.
		$license_key = get_option( 'spai_license_key' );
		if ( ! $license_key ) {
			$this->license_data = array( 'valid' => false, 'plan' => 'free' );
			return $this->license_data;
		}

		// Validate with custom server.
		$response = wp_remote_post(
			'https://license.sitepilot.ai/api/validate',
			array(
				'timeout' => 15,
				'body'    => array(
					'license_key' => $license_key,
					'site_url'    => get_site_url(),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			// On error, use cached or assume invalid.
			$this->license_data = array( 'valid' => false, 'plan' => 'free' );
			return $this->license_data;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		$this->license_data = array(
			'valid'      => ! empty( $body['valid'] ),
			'plan'       => $body['plan'] ?? 'free',
			'expires_at' => $body['expires_at'] ?? null,
			'site_limit' => $body['site_limit'] ?? null,
		);

		// Cache for 12 hours.
		set_transient( 'spai_license_data', $this->license_data, 12 * HOUR_IN_SECONDS );

		return $this->license_data;
	}

	/**
	 * Activate a custom license key.
	 *
	 * @param string $license_key License key to activate.
	 * @return array Result with 'success' and 'message'.
	 */
	public function activate( $license_key ) {
		if ( 'freemius' === $this->provider ) {
			// Freemius handles activation through its own UI.
			return array(
				'success' => false,
				'message' => __( 'Please use the Account page to manage your license.', 'site-pilot-ai' ),
			);
		}

		// Custom activation.
		$response = wp_remote_post(
			'https://license.sitepilot.ai/api/activate',
			array(
				'timeout' => 15,
				'body'    => array(
					'license_key' => $license_key,
					'site_url'    => get_site_url(),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => $response->get_error_message(),
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! empty( $body['success'] ) ) {
			update_option( 'spai_license_key', $license_key );
			delete_transient( 'spai_license_data' );
			$this->license_data = null;

			return array(
				'success' => true,
				'message' => __( 'License activated successfully.', 'site-pilot-ai' ),
			);
		}

		return array(
			'success' => false,
			'message' => $body['message'] ?? __( 'Activation failed.', 'site-pilot-ai' ),
		);
	}

	/**
	 * Deactivate license.
	 *
	 * @return array Result.
	 */
	public function deactivate() {
		if ( 'freemius' === $this->provider ) {
			return array(
				'success' => false,
				'message' => __( 'Please use the Account page to manage your license.', 'site-pilot-ai' ),
			);
		}

		$license_key = get_option( 'spai_license_key' );
		if ( ! $license_key ) {
			return array(
				'success' => false,
				'message' => __( 'No license to deactivate.', 'site-pilot-ai' ),
			);
		}

		wp_remote_post(
			'https://license.sitepilot.ai/api/deactivate',
			array(
				'timeout' => 15,
				'body'    => array(
					'license_key' => $license_key,
					'site_url'    => get_site_url(),
				),
			)
		);

		delete_option( 'spai_license_key' );
		delete_transient( 'spai_license_data' );
		$this->license_data = null;

		return array(
			'success' => true,
			'message' => __( 'License deactivated.', 'site-pilot-ai' ),
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
