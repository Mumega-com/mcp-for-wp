<?php
/**
 * Logging Trait
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activity logging functionality.
 */
trait Spai_Logging {

	/**
	 * Log API activity.
	 *
	 * @param string          $action      Action name.
	 * @param WP_REST_Request $request     Request object.
	 * @param mixed           $response    Response data.
	 * @param int             $status_code HTTP status code.
	 */
	protected function log_activity( $action, $request, $response = null, $status_code = 200 ) {
		$settings = get_option( 'spai_settings', array() );

		if ( empty( $settings['enable_logging'] ) ) {
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'spai_activity_log';

		$data = array(
			'action'      => sanitize_key( $action ),
			'endpoint'    => $request->get_route(),
			'method'      => $request->get_method(),
			'status_code' => absint( $status_code ),
			'ip_address'  => $this->get_client_ip_for_logging(),
			'user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
			'created_at'  => current_time( 'mysql' ),
		);

		// Optionally log request data (excluding sensitive info)
		$request_data = $this->get_loggable_request_data( $request );
		if ( ! empty( $request_data ) ) {
			$data['request_data'] = wp_json_encode( $request_data );
		}

		// Log response size if available
		if ( null !== $response ) {
			$response_json = wp_json_encode( $response );
			$data['response_data'] = strlen( $response_json ) > 1000 ? null : $response_json;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert(
			$table,
			$data,
			array( '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s' )
		);
	}

	/**
	 * Get loggable request data (excluding sensitive fields).
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return array Loggable data.
	 */
	protected function get_loggable_request_data( $request ) {
		$params = $request->get_params();

		// Remove sensitive fields
		$sensitive_keys = array( 'api_key', 'password', 'secret', 'token', 'key' );
		foreach ( $sensitive_keys as $key ) {
			unset( $params[ $key ] );
		}

		// Truncate large fields
		foreach ( $params as $key => $value ) {
			if ( is_string( $value ) && strlen( $value ) > 500 ) {
				$params[ $key ] = substr( $value, 0, 500 ) . '...[truncated]';
			}
		}

		return $params;
	}

	/**
	 * Get client IP address.
	 *
	 * Note: This method may also be defined in Spai_Api_Auth trait.
	 * When both traits are used, PHP will use one of them.
	 *
	 * @return string IP address.
	 */
	protected function get_client_ip_for_logging() {
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
	 * Clean old log entries.
	 *
	 * @param int $days Days to retain.
	 * @return int Number of deleted rows.
	 */
	public function clean_old_logs( $days = 30 ) {
		global $wpdb;
		$table = $wpdb->prefix . 'spai_activity_log';

		$cutoff = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $table WHERE created_at < %s",
				$cutoff
			)
		);

		return $deleted;
	}

	/**
	 * Get recent activity.
	 *
	 * @param int $limit Number of entries.
	 * @return array Activity entries.
	 */
	public function get_recent_activity( $limit = 50 ) {
		global $wpdb;
		$table = $wpdb->prefix . 'spai_activity_log';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table ORDER BY created_at DESC LIMIT %d",
				absint( $limit )
			),
			ARRAY_A
		);
	}
}
