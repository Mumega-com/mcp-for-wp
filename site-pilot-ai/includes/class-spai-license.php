<?php
/**
 * License management for mumcp.
 *
 * Free core: 70 tools (pages, posts, media, menus, settings, Gutenberg, taxonomy, admin)
 * Pro: All 239 tools (adds Elementor, WooCommerce, LearnPress, SEO, Theme Builder, Forms)
 *
 * License validation via Lemon Squeezy API or local override.
 * 14-day free trial on first activation (no credit card required).
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * License handler.
 */
class Spai_License {

	/**
	 * Singleton instance.
	 *
	 * @var Spai_License
	 */
	private static $instance = null;

	/**
	 * Cached license data.
	 *
	 * @var array|null
	 */
	private $license_data = null;

	/**
	 * Trial duration in days.
	 *
	 * @var int
	 */
	const TRIAL_DAYS = 14;

	/**
	 * Option key for license.
	 *
	 * @var string
	 */
	const OPTION_KEY = 'spai_pro_license';

	/**
	 * Option key for trial start.
	 *
	 * @var string
	 */
	const TRIAL_KEY = 'spai_trial_started';

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
	 * Check if Pro features are active.
	 *
	 * Pro is active when:
	 * 1. Valid license key is stored and not expired, OR
	 * 2. Trial period is active (14 days from first activation), OR
	 * 3. MUMCP_PRO constant is defined (developer override)
	 *
	 * @return bool
	 */
	public function is_pro() {
		// Developer override.
		if ( defined( 'MUMCP_PRO' ) && MUMCP_PRO ) {
			return true;
		}

		// Check stored license.
		$license = $this->get_license_data();
		if ( ! empty( $license['key'] ) && ! empty( $license['valid'] ) && ! $this->is_expired() ) {
			return true;
		}

		// Check trial.
		if ( $this->is_trial_active() ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if user is paying (has a license, not trial).
	 *
	 * @return bool
	 */
	public function is_paying() {
		if ( defined( 'MUMCP_PRO' ) && MUMCP_PRO ) {
			return true;
		}
		$license = $this->get_license_data();
		return ! empty( $license['key'] ) && ! empty( $license['valid'] ) && ! $this->is_expired();
	}

	/**
	 * Check if agency tier.
	 *
	 * @return bool
	 */
	public function is_agency() {
		$license = $this->get_license_data();
		return ! empty( $license['plan'] ) && 'agency' === $license['plan'];
	}

	/**
	 * Get current plan.
	 *
	 * @return string 'free', 'pro', 'agency', or 'trial'
	 */
	public function get_plan() {
		if ( ! $this->is_pro() ) {
			return 'free';
		}
		if ( $this->is_trial_active() && ! $this->is_paying() ) {
			return 'trial';
		}
		$license = $this->get_license_data();
		return ! empty( $license['plan'] ) ? $license['plan'] : 'pro';
	}

	/**
	 * Check if trial is active.
	 *
	 * @return bool
	 */
	public function is_trial_active() {
		$trial_started = get_option( self::TRIAL_KEY, '' );
		if ( empty( $trial_started ) ) {
			return false;
		}
		$elapsed = time() - (int) $trial_started;
		return $elapsed < ( self::TRIAL_DAYS * DAY_IN_SECONDS );
	}

	/**
	 * Get trial days remaining.
	 *
	 * @return int Days remaining, 0 if expired or not started.
	 */
	public function get_trial_days_remaining() {
		$trial_started = get_option( self::TRIAL_KEY, '' );
		if ( empty( $trial_started ) ) {
			return 0;
		}
		$elapsed   = time() - (int) $trial_started;
		$remaining = ( self::TRIAL_DAYS * DAY_IN_SECONDS ) - $elapsed;
		return max( 0, (int) ceil( $remaining / DAY_IN_SECONDS ) );
	}

	/**
	 * Start free trial.
	 *
	 * @return array Result.
	 */
	public function start_trial() {
		$existing = get_option( self::TRIAL_KEY, '' );
		if ( ! empty( $existing ) ) {
			return array(
				'success' => false,
				'message' => __( 'Trial already started.', 'site-pilot-ai' ),
				'days_remaining' => $this->get_trial_days_remaining(),
			);
		}
		update_option( self::TRIAL_KEY, time() );
		return array(
			'success' => true,
			'message' => sprintf( __( '%d-day Pro trial started. All integrations unlocked.', 'site-pilot-ai' ), self::TRIAL_DAYS ),
			'days_remaining' => self::TRIAL_DAYS,
		);
	}

	/**
	 * Get stored license data.
	 *
	 * @return array License data or empty array.
	 */
	private function get_license_data() {
		if ( null === $this->license_data ) {
			$this->license_data = get_option( self::OPTION_KEY, array() );
			if ( ! is_array( $this->license_data ) ) {
				$this->license_data = array();
			}
		}
		return $this->license_data;
	}

	/**
	 * Get license key.
	 *
	 * @return string|null
	 */
	public function get_license_key() {
		$license = $this->get_license_data();
		return ! empty( $license['key'] ) ? $license['key'] : null;
	}

	/**
	 * Get expiration date.
	 *
	 * @return string|null ISO date or null.
	 */
	public function get_expiration() {
		$license = $this->get_license_data();
		return ! empty( $license['expires_at'] ) ? $license['expires_at'] : null;
	}

	/**
	 * Check if license is expired.
	 *
	 * @return bool
	 */
	public function is_expired() {
		$expires = $this->get_expiration();
		if ( empty( $expires ) ) {
			return false; // No expiration = lifetime.
		}
		return strtotime( $expires ) < time();
	}

	/**
	 * Get site limit.
	 *
	 * @return int|null Null = unlimited.
	 */
	public function get_site_limit() {
		$license = $this->get_license_data();
		return isset( $license['site_limit'] ) ? (int) $license['site_limit'] : null;
	}

	/**
	 * Upgrade URL.
	 *
	 * @return string
	 */
	public function get_upgrade_url() {
		return 'https://mucp.mumega.com/pricing/';
	}

	/**
	 * Account URL.
	 *
	 * @return string
	 */
	public function get_account_url() {
		return 'https://mucp.mumega.com/account/';
	}

	/**
	 * Activate a license key.
	 *
	 * Validates against Lemon Squeezy API and stores locally.
	 *
	 * @param string $license_key License key.
	 * @return array Result with success, message, plan.
	 */
	public function activate( $license_key ) {
		$license_key = sanitize_text_field( trim( $license_key ) );
		if ( empty( $license_key ) ) {
			return array(
				'success' => false,
				'message' => __( 'License key is required.', 'site-pilot-ai' ),
			);
		}

		// Validate with Lemon Squeezy.
		$response = wp_remote_post( 'https://api.lemonsqueezy.com/v1/licenses/validate', array(
			'timeout' => 15,
			'body'    => array(
				'license_key'   => $license_key,
				'instance_name' => home_url(),
			),
		) );

		if ( is_wp_error( $response ) ) {
			// Network error — accept key locally with a warning.
			$data = array(
				'key'        => $license_key,
				'valid'      => true,
				'plan'       => 'pro',
				'offline'    => true,
				'activated'  => current_time( 'mysql' ),
			);
			update_option( self::OPTION_KEY, $data );
			$this->license_data = $data;

			return array(
				'success' => true,
				'message' => __( 'License saved (offline validation — will verify on next check).', 'site-pilot-ai' ),
				'plan'    => 'pro',
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$valid = isset( $body['valid'] ) && $body['valid'];

		if ( ! $valid ) {
			$error = isset( $body['error'] ) ? $body['error'] : __( 'Invalid license key.', 'site-pilot-ai' );
			return array(
				'success' => false,
				'message' => $error,
			);
		}

		// Determine plan from Lemon Squeezy meta.
		$meta       = isset( $body['meta'] ) ? $body['meta'] : array();
		$variant    = isset( $meta['variant_name'] ) ? strtolower( $meta['variant_name'] ) : '';
		$plan       = ( false !== strpos( $variant, 'agency' ) ) ? 'agency' : 'pro';
		$expires_at = isset( $body['license_key']['expires_at'] ) ? $body['license_key']['expires_at'] : null;
		$site_limit = isset( $meta['activation_limit'] ) ? (int) $meta['activation_limit'] : null;

		$data = array(
			'key'        => $license_key,
			'valid'      => true,
			'plan'       => $plan,
			'expires_at' => $expires_at,
			'site_limit' => $site_limit,
			'activated'  => current_time( 'mysql' ),
		);

		update_option( self::OPTION_KEY, $data );
		$this->license_data = $data;

		return array(
			'success' => true,
			'message' => sprintf( __( 'License activated. Plan: %s', 'site-pilot-ai' ), ucfirst( $plan ) ),
			'plan'    => $plan,
		);
	}

	/**
	 * Deactivate license.
	 *
	 * @return array Result.
	 */
	public function deactivate() {
		$license = $this->get_license_data();
		if ( ! empty( $license['key'] ) ) {
			// Notify Lemon Squeezy (best effort).
			wp_remote_post( 'https://api.lemonsqueezy.com/v1/licenses/deactivate', array(
				'timeout' => 10,
				'body'    => array(
					'license_key'   => $license['key'],
					'instance_id'   => md5( home_url() ),
				),
			) );
		}

		delete_option( self::OPTION_KEY );
		$this->license_data = null;

		return array(
			'success' => true,
			'message' => __( 'License deactivated. Pro features disabled.', 'site-pilot-ai' ),
		);
	}

	/**
	 * Get license info for API responses.
	 *
	 * @return array
	 */
	public function get_info() {
		return array(
			'provider'        => 'lemon_squeezy',
			'is_paying'       => $this->is_paying(),
			'plan'            => $this->get_plan(),
			'is_pro'          => $this->is_pro(),
			'is_agency'       => $this->is_agency(),
			'license_key'     => $this->get_license_key() ? substr( $this->get_license_key(), 0, 8 ) . '...' : null,
			'expiration'      => $this->get_expiration(),
			'is_expired'      => $this->is_expired(),
			'site_limit'      => $this->get_site_limit(),
			'trial_active'    => $this->is_trial_active(),
			'trial_remaining' => $this->get_trial_days_remaining(),
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
