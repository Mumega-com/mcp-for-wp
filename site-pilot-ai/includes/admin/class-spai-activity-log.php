<?php
/**
 * Activity Log admin page
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activity log page renderer.
 */
class Spai_Activity_Log_Page {

	/**
	 * Render page.
	 */
	public function render() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- log_id is an integer ID used for read-only display, verified via admin page access.
		$log_id = isset( $_GET['log_id'] ) ? absint( wp_unslash( $_GET['log_id'] ) ) : 0;

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Mumega MCP Activity Log', 'site-pilot-ai' ) . '</h1>';

		$settings = get_option( 'spai_settings', array() );
		$enabled  = ! empty( $settings['enable_logging'] );

		if ( ! $enabled ) {
			echo '<div class="notice notice-warning"><p>' .
				esc_html__( 'Activity logging is currently disabled. Enable it in Mumega MCP settings to capture new entries.', 'site-pilot-ai' ) .
				'</p></div>';
		}

		if ( $log_id > 0 ) {
			$this->render_detail_view( $log_id );
			echo '</div>';
			return;
		}

		$this->render_list_view();
		echo '</div>';
	}

	/**
	 * Render list view.
	 */
	private function render_list_view() {
		global $wpdb;

		$table = $wpdb->prefix . 'spai_activity_log';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin page with sanitized inputs; nonce used in filter form.
		$paged    = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : 1;
		$per_page = 50;
		$offset   = ( $paged - 1 ) * $per_page;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display with sanitized inputs.
		$search      = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$method      = isset( $_GET['method'] ) ? sanitize_key( wp_unslash( $_GET['method'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status_code = isset( $_GET['status_code'] ) ? absint( wp_unslash( $_GET['status_code'] ) ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action      = isset( $_GET['action_name'] ) ? sanitize_key( wp_unslash( $_GET['action_name'] ) ) : '';

		$where      = array( '1=1' );
		$arguments  = array();

		if ( '' !== $search ) {
			$where[] = '(action LIKE %s OR endpoint LIKE %s OR ip_address LIKE %s)';
			$like    = '%' . $wpdb->esc_like( $search ) . '%';
			$arguments[] = $like;
			$arguments[] = $like;
			$arguments[] = $like;
		}

		if ( '' !== $method ) {
			$where[] = 'method = %s';
			$arguments[] = strtoupper( $method );
		}

		if ( $status_code > 0 ) {
			$where[] = 'status_code = %d';
			$arguments[] = $status_code;
		}

		if ( '' !== $action ) {
			$where[] = 'action = %s';
			$arguments[] = $action;
		}

		$where_sql = implode( ' AND ', $where );

		$count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
		$list_sql  = "SELECT id, action, endpoint, method, status_code, ip_address, created_at
			FROM {$table}
			WHERE {$where_sql}
			ORDER BY created_at DESC
			LIMIT %d OFFSET %d";

		if ( empty( $arguments ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- $count_sql uses only $wpdb->prefix table name.
			$total = (int) $wpdb->get_var( $count_sql );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- $count_sql uses sanitized where clauses with prepare().
			$total = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $arguments ) );
		}

		$list_args = array_merge( $arguments, array( $per_page, $offset ) );
		if ( empty( $arguments ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- $list_sql uses $wpdb->prefix table name.
			$rows = $wpdb->get_results( $wpdb->prepare( $list_sql, $per_page, $offset ), ARRAY_A );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- $list_sql uses sanitized where clauses with prepare().
			$rows = $wpdb->get_results( $wpdb->prepare( $list_sql, $list_args ), ARRAY_A );
		}

		$this->render_filters( array(
			's'           => $search,
			'method'      => $method,
			'status_code' => $status_code,
			'action_name' => $action,
		) );

		echo '<table class="widefat fixed striped">';
		echo '<thead><tr>';
		echo '<th style="width: 120px">' . esc_html__( 'When', 'site-pilot-ai' ) . '</th>';
		echo '<th style="width: 140px">' . esc_html__( 'Action', 'site-pilot-ai' ) . '</th>';
		echo '<th>' . esc_html__( 'Endpoint', 'site-pilot-ai' ) . '</th>';
		echo '<th style="width: 70px">' . esc_html__( 'Method', 'site-pilot-ai' ) . '</th>';
		echo '<th style="width: 70px">' . esc_html__( 'Status', 'site-pilot-ai' ) . '</th>';
		echo '<th style="width: 140px">' . esc_html__( 'IP', 'site-pilot-ai' ) . '</th>';
		echo '</tr></thead>';
		echo '<tbody>';

		if ( empty( $rows ) ) {
			echo '<tr><td colspan="6">' . esc_html__( 'No activity found.', 'site-pilot-ai' ) . '</td></tr>';
		} else {
			foreach ( $rows as $row ) {
				$detail_url = add_query_arg(
					array(
						'page'   => Spai_Admin::ACTIVITY_LOG_PAGE_SLUG,
						'log_id' => (int) $row['id'],
					),
					admin_url( 'admin.php' )
				);

				echo '<tr>';
				echo '<td>' . esc_html( $this->format_datetime( $row['created_at'] ) ) . '</td>';
				echo '<td><a href="' . esc_url( $detail_url ) . '">' . esc_html( (string) $row['action'] ) . '</a></td>';
				echo '<td><code>' . esc_html( (string) $row['endpoint'] ) . '</code></td>';
				echo '<td>' . esc_html( (string) $row['method'] ) . '</td>';
				echo '<td>' . esc_html( (string) $row['status_code'] ) . '</td>';
				echo '<td>' . esc_html( (string) $row['ip_address'] ) . '</td>';
				echo '</tr>';
			}
		}

		echo '</tbody>';
		echo '</table>';

		$this->render_pagination( $total, $per_page, $paged );
	}

	/**
	 * Render detail view for a specific log entry.
	 *
	 * @param int $log_id Log entry ID.
	 */
	private function render_detail_view( $log_id ) {
		global $wpdb;

		$table = $wpdb->prefix . 'spai_activity_log';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from $wpdb->prefix.
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d",
				$log_id
			),
			ARRAY_A
		);

		$back_url = add_query_arg(
			array(
				'page' => Spai_Admin::ACTIVITY_LOG_PAGE_SLUG,
			),
			admin_url( 'admin.php' )
		);

		echo '<p><a class="button" href="' . esc_url( $back_url ) . '">' . esc_html__( 'Back to list', 'site-pilot-ai' ) . '</a></p>';

		if ( empty( $row ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Log entry not found.', 'site-pilot-ai' ) . '</p></div>';
			return;
		}

		echo '<h2>' . esc_html__( 'Log Entry', 'site-pilot-ai' ) . ' #' . esc_html( (string) $row['id'] ) . '</h2>';

		echo '<table class="widefat striped" style="max-width: 980px">';
		echo '<tbody>';
		$this->render_kv_row( __( 'When', 'site-pilot-ai' ), $this->format_datetime( $row['created_at'] ) );
		$this->render_kv_row( __( 'Action', 'site-pilot-ai' ), (string) $row['action'] );
		$this->render_kv_row( __( 'Endpoint', 'site-pilot-ai' ), (string) $row['endpoint'], true );
		$this->render_kv_row( __( 'Method', 'site-pilot-ai' ), (string) $row['method'] );
		$this->render_kv_row( __( 'Status', 'site-pilot-ai' ), (string) $row['status_code'] );
		$this->render_kv_row( __( 'IP', 'site-pilot-ai' ), (string) $row['ip_address'] );
		$this->render_kv_row( __( 'User Agent', 'site-pilot-ai' ), (string) $row['user_agent'] );
		echo '</tbody>';
		echo '</table>';

		$request_data  = $this->decode_json_maybe( $row['request_data'] );
		$response_data = $this->decode_json_maybe( $row['response_data'] );

		$request_data  = $this->redact_sensitive( $request_data );
		$response_data = $this->redact_sensitive( $response_data );

		echo '<h2>' . esc_html__( 'Request Data (redacted)', 'site-pilot-ai' ) . '</h2>';
		// render_pretty_json_block() returns pre-escaped HTML via esc_html().
		echo wp_kses_post( $this->render_pretty_json_block( $request_data ) );

		echo '<h2>' . esc_html__( 'Response Data (redacted)', 'site-pilot-ai' ) . '</h2>';
		echo wp_kses_post( $this->render_pretty_json_block( $response_data ) );
	}

	/**
	 * Render filter/search controls.
	 *
	 * @param array $values Current values.
	 */
	private function render_filters( $values ) {
		$base_url = add_query_arg(
			array(
				'page' => Spai_Admin::ACTIVITY_LOG_PAGE_SLUG,
			),
			admin_url( 'admin.php' )
		);

		echo '<form method="get" action="' . esc_url( admin_url( 'admin.php' ) ) . '" style="margin: 16px 0">';
		echo '<input type="hidden" name="page" value="' . esc_attr( Spai_Admin::ACTIVITY_LOG_PAGE_SLUG ) . '" />';

		echo '<p class="search-box" style="margin: 0 0 10px">';
		echo '<label class="screen-reader-text" for="spai-log-search-input">' . esc_html__( 'Search Activity', 'site-pilot-ai' ) . '</label>';
		echo '<input type="search" id="spai-log-search-input" name="s" value="' . esc_attr( (string) $values['s'] ) . '" />';
		submit_button( __( 'Search', 'site-pilot-ai' ), 'button', false, false, array( 'id' => 'search-submit' ) );
		echo '</p>';

		echo '<div style="display:flex; gap: 8px; align-items: center; flex-wrap: wrap; margin-bottom: 10px">';

		echo '<select name="method">';
		echo '<option value="">' . esc_html__( 'All methods', 'site-pilot-ai' ) . '</option>';
		foreach ( array( 'GET', 'POST', 'PUT', 'PATCH', 'DELETE' ) as $method ) {
			echo '<option value="' . esc_attr( strtolower( $method ) ) . '"' . selected( strtoupper( (string) $values['method'] ), $method, false ) . '>' . esc_html( $method ) . '</option>';
		}
		echo '</select>';

		echo '<input type="number" name="status_code" placeholder="' . esc_attr__( 'Status', 'site-pilot-ai' ) . '" value="' . esc_attr( (string) $values['status_code'] ) . '" style="width: 110px" />';
		echo '<input type="text" name="action_name" placeholder="' . esc_attr__( 'Action', 'site-pilot-ai' ) . '" value="' . esc_attr( (string) $values['action_name'] ) . '" style="width: 180px" />';

		submit_button( __( 'Filter', 'site-pilot-ai' ), 'secondary', 'filter', false );

		echo '<a class="button-link" href="' . esc_url( $base_url ) . '">' . esc_html__( 'Reset', 'site-pilot-ai' ) . '</a>';
		echo '</div>';

		echo '</form>';
	}

	/**
	 * Render pagination links.
	 *
	 * @param int $total Total items.
	 * @param int $per_page Per page.
	 * @param int $paged Current page.
	 */
	private function render_pagination( $total, $per_page, $paged ) {
		$total_pages = (int) ceil( $total / $per_page );
		if ( $total_pages <= 1 ) {
			return;
		}

		$current_args = array();
		foreach ( array( 's', 'method', 'status_code', 'action_name' ) as $key ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( isset( $_GET[ $key ] ) && '' !== (string) $_GET[ $key ] ) {
				$current_args[ $key ] = sanitize_text_field( wp_unslash( (string) $_GET[ $key ] ) );
			}
		}

		echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 12px 0">';
		// paginate_links() returns safe HTML with escaped URLs.
		echo wp_kses_post( paginate_links( array(
			'base'      => esc_url_raw( add_query_arg( array_merge( $current_args, array(
				'page'  => Spai_Admin::ACTIVITY_LOG_PAGE_SLUG,
				'paged' => '%#%',
			) ), admin_url( 'admin.php' ) ) ),
			'format'    => '',
			'prev_text' => __( '&laquo;', 'site-pilot-ai' ),
			'next_text' => __( '&raquo;', 'site-pilot-ai' ),
			'total'     => $total_pages,
			'current'   => $paged,
		) ) );
		echo '</div></div>';
	}

	/**
	 * Render a key/value row.
	 *
	 * @param string $label Label.
	 * @param string $value Value.
	 * @param bool   $code Whether to wrap value in code.
	 */
	private function render_kv_row( $label, $value, $code = false ) {
		echo '<tr>';
		echo '<th style="width: 180px">' . esc_html( $label ) . '</th>';
		echo '<td>' . ( $code ? '<code>' . esc_html( $value ) . '</code>' : esc_html( $value ) ) . '</td>';
		echo '</tr>';
	}

	/**
	 * Format datetime for display.
	 *
	 * @param string $mysql_datetime MySQL datetime string.
	 * @return string
	 */
	private function format_datetime( $mysql_datetime ) {
		$time = strtotime( (string) $mysql_datetime );
		if ( ! $time ) {
			return (string) $mysql_datetime;
		}

		$format = 'Y-m-d H:i:s';
		if ( function_exists( 'wp_date' ) ) {
			return wp_date( $format, $time, wp_timezone() );
		}

		return date_i18n( $format, $time, false );
	}

	/**
	 * Decode JSON when possible.
	 *
	 * @param string|null $value Stored value.
	 * @return mixed
	 */
	private function decode_json_maybe( $value ) {
		if ( null === $value || '' === (string) $value ) {
			return null;
		}

		if ( ! is_string( $value ) ) {
			return $value;
		}

		$decoded = json_decode( $value, true );
		return ( json_last_error() === JSON_ERROR_NONE ) ? $decoded : $value;
	}

	/**
	 * Render JSON or scalar value in a <pre>.
	 *
	 * @param mixed $value Value to render.
	 * @return string HTML.
	 */
	private function render_pretty_json_block( $value ) {
		if ( null === $value || '' === $value ) {
			return '<p><em>' . esc_html__( '(none)', 'site-pilot-ai' ) . '</em></p>';
		}

		if ( is_string( $value ) ) {
			return '<pre style="max-width: 980px; white-space: pre-wrap; word-break: break-word; background: #fff; border: 1px solid #dcdcde; padding: 12px;">' . esc_html( $value ) . '</pre>';
		}

		$json = wp_json_encode( $value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		return '<pre style="max-width: 980px; white-space: pre; overflow: auto; background: #fff; border: 1px solid #dcdcde; padding: 12px;">' . esc_html( $json ) . '</pre>';
	}

	/**
	 * Redact sensitive fields from arrays/objects.
	 *
	 * @param mixed $data Data.
	 * @return mixed Redacted data.
	 */
	private function redact_sensitive( $data ) {
		$sensitive_keys = array(
			'api_key',
			'x-api-key',
			'authorization',
			'bearer',
			'secret',
			'password',
			'token',
			'access_token',
			'refresh_token',
			'client_secret',
			'private_key',
		);

		$settings = get_option( 'spai_settings', array() );
		if ( isset( $settings['log_redaction_keys'] ) ) {
			$custom = $settings['log_redaction_keys'];
			if ( is_string( $custom ) ) {
				$custom = preg_split( '/[\r\n,]+/', $custom );
			}
			if ( is_array( $custom ) ) {
				foreach ( $custom as $item ) {
					$item = strtolower( trim( sanitize_text_field( (string) $item ) ) );
					if ( '' === $item ) {
						continue;
					}
					$sensitive_keys[] = $item;
				}
				$sensitive_keys = array_values( array_unique( $sensitive_keys ) );
			}
		}

		if ( is_array( $data ) ) {
			$out = array();
			foreach ( $data as $key => $value ) {
				$key_normalized = is_string( $key ) ? strtolower( $key ) : $key;
				if ( is_string( $key_normalized ) && in_array( $key_normalized, $sensitive_keys, true ) ) {
					$out[ $key ] = '[redacted]';
					continue;
				}
				$out[ $key ] = $this->redact_sensitive( $value );
			}
			return $out;
		}

		return $data;
	}
}
