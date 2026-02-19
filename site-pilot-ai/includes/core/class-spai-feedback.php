<?php
/**
 * Feedback Core
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles feedback submission, listing, and optional GitHub issue creation.
 */
class Spai_Feedback {

	/**
	 * Valid feedback types.
	 *
	 * @var array
	 */
	const TYPES = array( 'bug_report', 'feature_request', 'feedback' );

	/**
	 * Valid priorities.
	 *
	 * @var array
	 */
	const PRIORITIES = array( 'low', 'medium', 'high', 'critical' );

	/**
	 * Valid statuses.
	 *
	 * @var array
	 */
	const STATUSES = array( 'open', 'acknowledged', 'resolved', 'closed' );

	/**
	 * Map feedback type to GitHub issue label.
	 *
	 * @var array
	 */
	const GITHUB_LABELS = array(
		'bug_report'      => 'bug',
		'feature_request' => 'enhancement',
		'feedback'        => 'feedback',
	);

	/**
	 * Submit feedback.
	 *
	 * @param array $args {
	 *     @type string $type        Required. bug_report, feature_request, or feedback.
	 *     @type string $title       Required. Short summary.
	 *     @type string $description Required. Detailed description.
	 *     @type string $agent       Optional. AI model name.
	 *     @type string $priority    Optional. low, medium, high, critical.
	 *     @type array  $meta        Optional. Extra context (page_id, tool_name, error_message).
	 * }
	 * @return array|WP_Error Feedback entry on success.
	 */
	public static function submit( $args ) {
		global $wpdb;

		$type = isset( $args['type'] ) ? sanitize_text_field( $args['type'] ) : '';
		if ( ! in_array( $type, self::TYPES, true ) ) {
			return new WP_Error( 'invalid_type', 'Type must be one of: ' . implode( ', ', self::TYPES ), array( 'status' => 400 ) );
		}

		$title = isset( $args['title'] ) ? sanitize_text_field( $args['title'] ) : '';
		if ( '' === $title ) {
			return new WP_Error( 'missing_title', 'Title is required.', array( 'status' => 400 ) );
		}

		$description = isset( $args['description'] ) ? sanitize_textarea_field( $args['description'] ) : '';
		if ( '' === $description ) {
			return new WP_Error( 'missing_description', 'Description is required.', array( 'status' => 400 ) );
		}

		$agent    = isset( $args['agent'] ) ? sanitize_text_field( $args['agent'] ) : '';
		$priority = isset( $args['priority'] ) && in_array( $args['priority'], self::PRIORITIES, true )
			? $args['priority']
			: 'medium';

		$meta = isset( $args['meta'] ) ? $args['meta'] : array();
		if ( is_string( $meta ) ) {
			$decoded = json_decode( $meta, true );
			$meta    = is_array( $decoded ) ? $decoded : array();
		}
		$meta_json = wp_json_encode( $meta );

		$table = $wpdb->prefix . 'spai_feedback';
		$now   = current_time( 'mysql', true );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$inserted = $wpdb->insert(
			$table,
			array(
				'type'        => $type,
				'title'       => $title,
				'description' => $description,
				'agent'       => $agent,
				'priority'    => $priority,
				'status'      => 'open',
				'meta'        => $meta_json,
				'created_at'  => $now,
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( false === $inserted ) {
			return new WP_Error( 'db_error', 'Failed to save feedback.', array( 'status' => 500 ) );
		}

		$feedback_id = (int) $wpdb->insert_id;

		// Attempt GitHub issue creation.
		$github_url = self::maybe_create_github_issue( $type, $title, $description, $agent, $priority, $meta );
		if ( ! empty( $github_url ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update(
				$table,
				array( 'github_issue_url' => $github_url ),
				array( 'id' => $feedback_id ),
				array( '%s' ),
				array( '%d' )
			);
		}

		return array(
			'id'               => $feedback_id,
			'type'             => $type,
			'title'            => $title,
			'status'           => 'open',
			'priority'         => $priority,
			'github_issue_url' => $github_url ?: null,
			'created_at'       => $now,
		);
	}

	/**
	 * List feedback entries.
	 *
	 * @param array $args {
	 *     @type string $type   Optional. Filter by type.
	 *     @type string $status Optional. Filter by status. Default 'open'.
	 *     @type int    $limit  Optional. Max results. Default 20.
	 * }
	 * @return array Feedback entries.
	 */
	public static function list_entries( $args = array() ) {
		global $wpdb;

		$table  = $wpdb->prefix . 'spai_feedback';
		$where  = array();
		$values = array();

		if ( ! empty( $args['type'] ) && in_array( $args['type'], self::TYPES, true ) ) {
			$where[]  = 'type = %s';
			$values[] = $args['type'];
		}

		$status = isset( $args['status'] ) ? $args['status'] : 'open';
		if ( 'all' !== $status && in_array( $status, self::STATUSES, true ) ) {
			$where[]  = 'status = %s';
			$values[] = $status;
		}

		$limit    = isset( $args['limit'] ) ? min( 100, max( 1, absint( $args['limit'] ) ) ) : 20;
		$where_sql = ! empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';

		$query = "SELECT * FROM {$table} {$where_sql} ORDER BY created_at DESC LIMIT %d";
		$values[] = $limit;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- $query built from safe table name and validated where clauses.
		$results = $wpdb->get_results( $wpdb->prepare( $query, $values ), ARRAY_A );

		if ( ! is_array( $results ) ) {
			return array();
		}

		foreach ( $results as &$row ) {
			$row['id']   = (int) $row['id'];
			$row['meta'] = json_decode( $row['meta'], true ) ?: array();
		}

		return $results;
	}

	/**
	 * Create a GitHub issue if GitHub integration is configured.
	 *
	 * @param string $type        Feedback type.
	 * @param string $title       Feedback title.
	 * @param string $description Feedback description.
	 * @param string $agent       AI agent name.
	 * @param string $priority    Priority level.
	 * @param array  $meta        Extra context.
	 * @return string GitHub issue URL, or empty string.
	 */
	private static function maybe_create_github_issue( $type, $title, $description, $agent, $priority, $meta ) {
		$settings = get_option( 'spai_settings', array() );
		$token    = isset( $settings['github_token'] ) ? $settings['github_token'] : '';
		$repo     = isset( $settings['github_repo'] ) ? $settings['github_repo'] : '';

		if ( empty( $token ) || empty( $repo ) ) {
			return '';
		}

		// Validate repo format (owner/repo).
		if ( ! preg_match( '/^[a-zA-Z0-9._-]+\/[a-zA-Z0-9._-]+$/', $repo ) ) {
			return '';
		}

		$label = isset( self::GITHUB_LABELS[ $type ] ) ? self::GITHUB_LABELS[ $type ] : 'feedback';

		$body = "## {$title}\n\n";
		$body .= "**Type:** {$type}\n";
		$body .= "**Priority:** {$priority}\n";
		if ( ! empty( $agent ) ) {
			$body .= "**AI Agent:** {$agent}\n";
		}
		$body .= "\n### Description\n\n{$description}\n";

		if ( ! empty( $meta ) ) {
			$body .= "\n### Context\n\n";
			foreach ( $meta as $key => $value ) {
				$display_value = is_array( $value ) ? wp_json_encode( $value ) : (string) $value;
				$body .= "- **{$key}:** {$display_value}\n";
			}
		}

		$body .= "\n---\n*Submitted via Site Pilot AI feedback system*";

		$response = wp_remote_post(
			"https://api.github.com/repos/{$repo}/issues",
			array(
				'headers' => array(
					'Authorization' => "Bearer {$token}",
					'Accept'        => 'application/vnd.github+json',
					'Content-Type'  => 'application/json',
					'User-Agent'    => 'SitePilotAI/' . SPAI_VERSION,
				),
				'body'    => wp_json_encode( array(
					'title'  => "[{$type}] {$title}",
					'body'   => $body,
					'labels' => array( $label, 'site-pilot-ai' ),
				) ),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return '';
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 201 !== $code ) {
			return '';
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		return isset( $data['html_url'] ) ? esc_url_raw( $data['html_url'] ) : '';
	}
}
