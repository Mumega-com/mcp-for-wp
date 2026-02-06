<?php
/**
 * API Authentication Trait
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API authentication functionality.
 */
trait Spai_Api_Auth {

	/**
	 * Verify API key from request.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool|WP_Error True if valid, error otherwise.
	 */
	public function verify_api_key( $request ) {
		$api_key = $this->get_api_key_from_request( $request );

		if ( empty( $api_key ) ) {
			$rate_limit_check = $this->check_rate_limit( 'missing:' . $this->get_client_ip() );
			if ( is_wp_error( $rate_limit_check ) ) {
				return $rate_limit_check;
			}

			return new WP_Error(
				'missing_api_key',
				__( 'API key is required.', 'site-pilot-ai' ),
				array( 'status' => 401 )
			);
		}

		$stored_key = get_option( 'spai_api_key' );

		if ( empty( $stored_key ) ) {
			$rate_limit_check = $this->check_rate_limit( 'unconfigured:' . $this->get_client_ip() );
			if ( is_wp_error( $rate_limit_check ) ) {
				return $rate_limit_check;
			}

			return new WP_Error(
				'api_not_configured',
				__( 'API key not configured. Please visit the Site Pilot AI settings.', 'site-pilot-ai' ),
				array( 'status' => 500 )
			);
		}

		$is_valid_key = $this->is_api_key_match( $api_key, $stored_key );

		// Rate limit by verified principal where possible, else by source IP.
		$identifier = $is_valid_key
			? 'key:' . hash( 'sha256', $api_key )
			: 'invalid:' . $this->get_client_ip();

		$rate_limit_check = $this->check_rate_limit( $identifier );
		if ( is_wp_error( $rate_limit_check ) ) {
			return $rate_limit_check;
		}

		if ( $is_valid_key ) {
			// Auto-migrate legacy plain text keys to hashed storage.
			if ( hash_equals( $stored_key, $api_key ) ) {
				update_option( 'spai_api_key', wp_hash_password( $api_key ) );
			}

			if ( ! $this->set_api_user_context() ) {
				return new WP_Error(
					'api_user_missing',
					__( 'API user context is not configured. Re-activate Site Pilot AI to provision the service account.', 'site-pilot-ai' ),
					array( 'status' => 500 )
				);
			}

			return true;
		}

		// Log failed attempt
		$this->log_auth_failure( $request );

		return new WP_Error(
			'invalid_api_key',
			__( 'Invalid API key.', 'site-pilot-ai' ),
			array( 'status' => 401 )
		);
	}

	/**
	 * Check rate limit for current request.
	 *
	 * @return bool|WP_Error True if allowed, error if rate limited.
	 */
	protected function check_rate_limit( $identifier = null ) {
		if ( ! class_exists( 'Spai_Rate_Limiter' ) ) {
			return true;
		}

		$limiter = Spai_Rate_Limiter::get_instance();
		return $limiter->check_limit( $identifier );
	}

	/**
	 * Get API key from request.
	 *
	 * Checks header first, then query parameter.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return string|null API key or null.
	 */
	protected function get_api_key_from_request( $request ) {
		// Check header first (X-API-Key)
		$api_key = $request->get_header( 'X-API-Key' );

		if ( ! empty( $api_key ) ) {
			return sanitize_text_field( $api_key );
		}

		// Check Authorization header (Bearer token)
		$auth_header = $request->get_header( 'Authorization' );
		if ( ! empty( $auth_header ) && 0 === strpos( $auth_header, 'Bearer ' ) ) {
			return sanitize_text_field( substr( $auth_header, 7 ) );
		}

		return null;
	}

	/**
	 * Log authentication failure.
	 *
	 * @param WP_REST_Request $request Request object.
	 */
	protected function log_auth_failure( $request ) {
		$settings = get_option( 'spai_settings', array() );

		if ( empty( $settings['enable_logging'] ) ) {
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'spai_activity_log';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert(
			$table,
			array(
				'action'      => 'auth_failure',
				'endpoint'    => $request->get_route(),
				'method'      => $request->get_method(),
				'status_code' => 401,
				'ip_address'  => $this->get_client_ip(),
				'user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}

	/**
	 * Get client IP address.
	 *
	 * @return string IP address.
	 */
	protected function get_client_ip() {
		$ip_keys = array(
			'HTTP_CF_CONNECTING_IP', // Cloudflare
			'HTTP_X_FORWARDED_FOR',  // Proxy
			'HTTP_X_REAL_IP',        // Nginx
			'REMOTE_ADDR',           // Standard
		);

		foreach ( $ip_keys as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
				// Handle comma-separated IPs (X-Forwarded-For)
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
	 * Set the current user context for API requests.
	 *
	 * Sets the current user to the dedicated API agent role
	 * so that capability checks work correctly.
	 */
	protected function set_api_user_context() {
		// Try to find a user with the spai_api_agent role
		$users = get_users( array(
			'role'    => 'spai_api_agent',
			'number'  => 1,
			'orderby' => 'ID',
			'order'   => 'ASC',
		) );

		if ( ! empty( $users ) ) {
			wp_set_current_user( $users[0]->ID );
			return true;
		}

		return false;
	}

	/**
	 * Check whether incoming key matches stored key (hash or legacy plain text).
	 *
	 * @param string $api_key    Incoming API key.
	 * @param string $stored_key Stored API key value.
	 * @return bool True if key is valid.
	 */
	protected function is_api_key_match( $api_key, $stored_key ) {
		if ( empty( $api_key ) || empty( $stored_key ) ) {
			return false;
		}

		if ( wp_check_password( $api_key, $stored_key ) ) {
			return true;
		}

		return hash_equals( $stored_key, $api_key );
	}

	/**
	 * Generate a new API key.
	 *
	 * @return string New API key.
	 */
	public function generate_api_key() {
		return 'spai_' . bin2hex( random_bytes( 24 ) );
	}

	/**
	 * Regenerate API key.
	 *
	 * @return string New API key (plain text).
	 */
	public function regenerate_api_key() {
		$new_key = $this->generate_api_key();
		update_option( 'spai_api_key', wp_hash_password( $new_key ) );
		return $new_key;
	}
}
