<?php
/**
 * Rate Limiter
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles API rate limiting using transients.
 */
class Spai_Rate_Limiter {

	/**
	 * Singleton instance.
	 *
	 * @var Spai_Rate_Limiter
	 */
	private static $instance = null;

	/**
	 * Rate limit settings.
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Current request count data.
	 *
	 * @var array
	 */
	private $current_data;

	/**
	 * Get singleton instance.
	 *
	 * @return Spai_Rate_Limiter
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
		$this->load_settings();
	}

	/**
	 * Load rate limit settings.
	 */
	private function load_settings() {
		$defaults = array(
			'enabled'            => true,
			'requests_per_minute' => 60,
			'requests_per_hour'   => 1000,
			'burst_limit'         => 10,
			'whitelist'           => array(),
		);

		$saved = get_option( 'spai_rate_limit_settings', array() );
		$this->settings = wp_parse_args( $saved, $defaults );
	}

	/**
	 * Check if rate limiting is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return (bool) $this->settings['enabled'];
	}

	/**
	 * Check rate limit for current request.
	 *
	 * @param string $identifier Unique identifier (IP or API key).
	 * @return bool|WP_Error True if allowed, WP_Error if rate limited.
	 */
	public function check_limit( $identifier = null ) {
		if ( ! $this->is_enabled() ) {
			return true;
		}

		if ( null === $identifier ) {
			$identifier = $this->get_client_identifier();
		}

		// Check whitelist.
		if ( $this->is_whitelisted( $identifier ) ) {
			return true;
		}

		$cache_key_minute = 'spai_rate_' . md5( $identifier . '_minute' );
		$cache_key_hour   = 'spai_rate_' . md5( $identifier . '_hour' );

		// Get current counts.
		$minute_data = get_transient( $cache_key_minute );
		$hour_data   = get_transient( $cache_key_hour );

		$now = time();

		// Initialize or get minute data.
		if ( false === $minute_data ) {
			$minute_data = array(
				'count'  => 0,
				'reset'  => $now + 60,
			);
		}

		// Initialize or get hour data.
		if ( false === $hour_data ) {
			$hour_data = array(
				'count'  => 0,
				'reset'  => $now + 3600,
			);
		}

		// Check minute limit.
		if ( $minute_data['count'] >= $this->settings['requests_per_minute'] ) {
			$this->current_data = array(
				'limit'     => $this->settings['requests_per_minute'],
				'remaining' => 0,
				'reset'     => $minute_data['reset'],
				'window'    => 'minute',
			);

			return new WP_Error(
				'rate_limit_exceeded',
				sprintf(
					__( 'Rate limit exceeded. %d requests per minute allowed. Try again in %d seconds.', 'site-pilot-ai' ),
					$this->settings['requests_per_minute'],
					$minute_data['reset'] - $now
				),
				array(
					'status'           => 429,
					'retry_after'      => $minute_data['reset'] - $now,
					'limit'            => $this->settings['requests_per_minute'],
					'remaining'        => 0,
					'reset'            => $minute_data['reset'],
				)
			);
		}

		// Check hour limit.
		if ( $hour_data['count'] >= $this->settings['requests_per_hour'] ) {
			$this->current_data = array(
				'limit'     => $this->settings['requests_per_hour'],
				'remaining' => 0,
				'reset'     => $hour_data['reset'],
				'window'    => 'hour',
			);

			return new WP_Error(
				'rate_limit_exceeded',
				sprintf(
					__( 'Rate limit exceeded. %d requests per hour allowed. Try again in %d seconds.', 'site-pilot-ai' ),
					$this->settings['requests_per_hour'],
					$hour_data['reset'] - $now
				),
				array(
					'status'           => 429,
					'retry_after'      => $hour_data['reset'] - $now,
					'limit'            => $this->settings['requests_per_hour'],
					'remaining'        => 0,
					'reset'            => $hour_data['reset'],
				)
			);
		}

		// Increment counts.
		$minute_data['count']++;
		$hour_data['count']++;

		// Store updated counts.
		set_transient( $cache_key_minute, $minute_data, 60 );
		set_transient( $cache_key_hour, $hour_data, 3600 );

		// Store current data for headers.
		$this->current_data = array(
			'limit'     => $this->settings['requests_per_minute'],
			'remaining' => $this->settings['requests_per_minute'] - $minute_data['count'],
			'reset'     => $minute_data['reset'],
			'window'    => 'minute',
		);

		return true;
	}

	/**
	 * Get rate limit headers.
	 *
	 * @return array Headers array.
	 */
	public function get_headers() {
		if ( empty( $this->current_data ) ) {
			return array();
		}

		return array(
			'X-RateLimit-Limit'     => $this->current_data['limit'],
			'X-RateLimit-Remaining' => max( 0, $this->current_data['remaining'] ),
			'X-RateLimit-Reset'     => $this->current_data['reset'],
		);
	}

	/**
	 * Get client identifier.
	 *
	 * @return string Client identifier (IP address).
	 */
	private function get_client_identifier() {
		$ip_keys = array(
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_REAL_IP',
			'REMOTE_ADDR',
		);

		foreach ( $ip_keys as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
				if ( strpos( $ip, ',' ) !== false ) {
					$ip = trim( explode( ',', $ip )[0] );
				}
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return 'unknown';
	}

	/**
	 * Check if identifier is whitelisted.
	 *
	 * @param string $identifier Client identifier.
	 * @return bool True if whitelisted.
	 */
	private function is_whitelisted( $identifier ) {
		if ( empty( $this->settings['whitelist'] ) ) {
			return false;
		}

		$whitelist = array_map( 'trim', $this->settings['whitelist'] );
		return in_array( $identifier, $whitelist, true );
	}

	/**
	 * Get current settings.
	 *
	 * @return array Settings.
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * Update settings.
	 *
	 * @param array $new_settings New settings.
	 * @return bool Success.
	 */
	public function update_settings( $new_settings ) {
		$allowed = array( 'enabled', 'requests_per_minute', 'requests_per_hour', 'burst_limit', 'whitelist' );

		foreach ( $new_settings as $key => $value ) {
			if ( in_array( $key, $allowed, true ) ) {
				$this->settings[ $key ] = $value;
			}
		}

		return update_option( 'spai_rate_limit_settings', $this->settings );
	}

	/**
	 * Reset rate limit for identifier.
	 *
	 * @param string $identifier Client identifier.
	 * @return bool Success.
	 */
	public function reset_limit( $identifier ) {
		$cache_key_minute = 'spai_rate_' . md5( $identifier . '_minute' );
		$cache_key_hour   = 'spai_rate_' . md5( $identifier . '_hour' );

		delete_transient( $cache_key_minute );
		delete_transient( $cache_key_hour );

		return true;
	}

	/**
	 * Get usage stats for identifier.
	 *
	 * @param string $identifier Client identifier.
	 * @return array Usage stats.
	 */
	public function get_usage( $identifier = null ) {
		if ( null === $identifier ) {
			$identifier = $this->get_client_identifier();
		}

		$cache_key_minute = 'spai_rate_' . md5( $identifier . '_minute' );
		$cache_key_hour   = 'spai_rate_' . md5( $identifier . '_hour' );

		$minute_data = get_transient( $cache_key_minute );
		$hour_data   = get_transient( $cache_key_hour );

		return array(
			'identifier'         => $identifier,
			'minute' => array(
				'used'      => $minute_data ? $minute_data['count'] : 0,
				'limit'     => $this->settings['requests_per_minute'],
				'remaining' => $this->settings['requests_per_minute'] - ( $minute_data ? $minute_data['count'] : 0 ),
				'reset'     => $minute_data ? $minute_data['reset'] : null,
			),
			'hour' => array(
				'used'      => $hour_data ? $hour_data['count'] : 0,
				'limit'     => $this->settings['requests_per_hour'],
				'remaining' => $this->settings['requests_per_hour'] - ( $hour_data ? $hour_data['count'] : 0 ),
				'reset'     => $hour_data ? $hour_data['reset'] : null,
			),
		);
	}
}
