<?php
/**
 * Admin functionality
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin class.
 */
class Spai_Admin {

	use Spai_Api_Auth;

	/**
	 * Admin page slug.
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'site-pilot-ai';

	/**
	 * Activity log page slug.
	 *
	 * @var string
	 */
	const ACTIVITY_LOG_PAGE_SLUG = 'site-pilot-ai-activity-log';

	/**
	 * SVG icon for menu (base64 encoded).
	 *
	 * @var string
	 */
	const MENU_ICON = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0iIzljYTJhNyI+PHBhdGggZD0iTTEwIDJjLTQuNCAwLTggMy42LTggOHMzLjYgOCA4IDggOC0zLjYgOC04LTMuNi04LTgtOHptMCAyYzEuNyAwIDMuMi43IDQuMyAxLjhMNy41IDE0LjZDNS42IDEzLjIgNC41IDExIDQuNSAxMCA0LjUgNi45IDcgNC41IDEwIDQuNXptMCAxMWMtMS43IDAtMy4yLS43LTQuMy0xLjhsNi44LTguOGMxLjkgMS40IDMgMy42IDMgNS42IDAgMy4xLTIuNSA1LjUtNS41IDUuNXoiLz48Y2lyY2xlIGN4PSIxMCIgY3k9IjEwIiByPSIyIi8+PC9zdmc+';

	/**
	 * Add admin menu - top-level with icon.
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'Site Pilot AI', 'site-pilot-ai' ),
			__( 'Site Pilot AI', 'site-pilot-ai' ),
			'activate_plugins',
			self::PAGE_SLUG,
			array( $this, 'render_admin_page' ),
			self::MENU_ICON,
			80
		);

		add_submenu_page(
			self::PAGE_SLUG,
			__( 'Integrations', 'site-pilot-ai' ),
			__( 'Integrations', 'site-pilot-ai' ),
			'activate_plugins',
			Spai_Integrations_Admin::PAGE_SLUG,
			array( new Spai_Integrations_Admin(), 'render' )
		);

		add_submenu_page(
			self::PAGE_SLUG,
			__( 'MCP Tools', 'site-pilot-ai' ),
			__( 'MCP Tools', 'site-pilot-ai' ),
			'activate_plugins',
			Spai_Tools_Admin::PAGE_SLUG,
			array( new Spai_Tools_Admin(), 'render' )
		);

		add_submenu_page(
			self::PAGE_SLUG,
			__( 'Activity Log', 'site-pilot-ai' ),
			__( 'Activity Log', 'site-pilot-ai' ),
			'activate_plugins',
			self::ACTIVITY_LOG_PAGE_SLUG,
			array( $this, 'render_activity_log_page' )
		);

	}

	/**
	 * Enqueue admin styles.
	 *
	 * @param string $hook Current admin page.
	 */
	public function enqueue_styles( $hook ) {
		$allowed_hooks = array(
			'toplevel_page_' . self::PAGE_SLUG,
			self::PAGE_SLUG . '_page_' . self::ACTIVITY_LOG_PAGE_SLUG,
			self::PAGE_SLUG . '_page_' . Spai_Integrations_Admin::PAGE_SLUG,
			self::PAGE_SLUG . '_page_' . Spai_Tools_Admin::PAGE_SLUG,
		);

		if ( ! in_array( $hook, $allowed_hooks, true ) ) {
			return;
		}

		wp_enqueue_style(
			'spai-admin',
			SPAI_PLUGIN_URL . 'admin/css/spai-admin.css',
			array(),
			SPAI_VERSION
		);
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook Current admin page.
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'toplevel_page_' . self::PAGE_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'spai-admin',
			SPAI_PLUGIN_URL . 'admin/js/spai-admin.js',
			array( 'jquery' ),
			SPAI_VERSION,
			true
		);

		wp_localize_script(
			'spai-admin',
			'spaiAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'spai_admin_nonce' ),
				'restUrl' => rest_url( 'site-pilot-ai/v1/' ),
				'siteUrl' => site_url(),
				'strings' => array(
					'copied'      => __( 'Copied!', 'site-pilot-ai' ),
					'copyFailed'  => __( 'Copy failed', 'site-pilot-ai' ),
					'confirm'     => __( 'Are you sure you want to regenerate the API key? The old key will stop working immediately.', 'site-pilot-ai' ),
					'testing'     => __( 'Testing...', 'site-pilot-ai' ),
					'connected'   => __( 'Connected!', 'site-pilot-ai' ),
					'testFailed'  => __( 'Connection failed', 'site-pilot-ai' ),
				),
			)
		);
	}

	/**
	 * Handle AJAX test connection.
	 */
	public function ajax_test_connection() {
		check_ajax_referer( 'spai_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		// Verify REST API is reachable by checking site info directly.
		// We don't use rest_do_request() because the permission_callback
		// requires an API key header which isn't present in admin AJAX.
		$rest_url = rest_url( 'site-pilot-ai/v1/' );

		// Check that the API key exists.
		$stored_key = get_option( 'spai_api_key' );
		if ( empty( $stored_key ) ) {
			wp_send_json_error( array(
				'message' => __( 'No API key configured. Please generate one on the Setup tab.', 'site-pilot-ai' ),
			) );
		}

		// Gather site info directly (same data the REST endpoint returns).
		global $wp_version;
		$site_name = get_bloginfo( 'name' );

		wp_send_json_success( array(
			'site_name'      => $site_name,
			'wp_version'     => $wp_version,
			'php_version'    => PHP_VERSION,
			'plugin_version' => SPAI_VERSION,
			'rest_url'       => $rest_url,
		) );
	}

	/**
	 * Handle AJAX dismiss welcome.
	 */
	public function ajax_dismiss_welcome() {
		check_ajax_referer( 'spai_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		delete_option( 'spai_first_activation' );
		delete_transient( 'spai_new_api_key' );
		wp_send_json_success();
	}

	/**
	 * Render admin page.
	 */
	public function render_admin_page() {
		// Check permissions
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'site-pilot-ai' ) );
		}

		// Handle regenerate action
		$new_key = null;
		if ( isset( $_POST['spai_regenerate_key'] ) ) {
			check_admin_referer( 'spai_regenerate_key', 'spai_nonce' );
			$new_key = $this->regenerate_api_key();
			add_settings_error(
				'spai_messages',
				'spai_key_regenerated',
				__( 'API key has been regenerated. Copy it now — it will not be shown again.', 'site-pilot-ai' ),
				'updated'
			);
		}

		$new_scoped_key = null;
		if ( isset( $_POST['spai_create_scoped_key'] ) ) {
			check_admin_referer( 'spai_manage_scoped_keys', 'spai_scoped_keys_nonce' );

			$label  = isset( $_POST['spai_scoped_key_label'] ) ? sanitize_text_field( wp_unslash( $_POST['spai_scoped_key_label'] ) ) : '';
			$scopes = isset( $_POST['spai_scoped_key_scopes'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['spai_scoped_key_scopes'] ) ) : array();
			if ( empty( $scopes ) ) {
				$scopes = array( 'read' );
			}

			$role            = isset( $_POST['spai_scoped_key_role'] ) ? sanitize_key( wp_unslash( $_POST['spai_scoped_key_role'] ) ) : 'admin';
			$tool_categories = isset( $_POST['spai_scoped_key_categories'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['spai_scoped_key_categories'] ) ) : array();

			$new_scoped_key = $this->create_scoped_api_key( $label, $scopes, $role, $tool_categories );

			$roles       = self::get_role_definitions();
			$role_label  = isset( $roles[ $role ] ) ? $roles[ $role ]['label'] : $role;
			add_settings_error(
				'spai_messages',
				'spai_scoped_key_created',
				sprintf(
					/* translators: %s: role label */
					__( 'API key created (role: %s). Copy it now — it will not be shown again.', 'site-pilot-ai' ),
					$role_label
				),
				'updated'
			);
		}

		if ( isset( $_POST['spai_revoke_scoped_key'] ) ) {
			check_admin_referer( 'spai_manage_scoped_keys', 'spai_scoped_keys_nonce' );

			$key_id = isset( $_POST['spai_scoped_key_id'] ) ? sanitize_key( wp_unslash( $_POST['spai_scoped_key_id'] ) ) : '';
			if ( '' !== $key_id ) {
				$revoked = $this->revoke_scoped_api_key( $key_id );
				if ( $revoked ) {
					add_settings_error(
						'spai_messages',
						'spai_scoped_key_revoked',
						__( 'Scoped API key revoked.', 'site-pilot-ai' ),
						'updated'
					);
				} else {
					add_settings_error(
						'spai_messages',
						'spai_scoped_key_revoke_failed',
						__( 'Unable to revoke key (it may already be revoked).', 'site-pilot-ai' ),
						'error'
					);
				}
			}
		}

		// Check for first-activation key
		if ( ! $new_key ) {
			$first_key = get_transient( 'spai_new_api_key' );
			if ( $first_key ) {
				$new_key = $first_key;
			}
		}

		$scoped_keys = $this->list_scoped_api_keys( true );

		include SPAI_PLUGIN_DIR . 'admin/partials/spai-admin-display.php';
	}

	/**
	 * Render activity log page.
	 */
	public function render_activity_log_page() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'site-pilot-ai' ) );
		}

		$page = new Spai_Activity_Log_Page();
		$page->render();
	}

	/**
	 * Get recent API activity rows.
	 *
	 * @param int $limit Limit.
	 * @return array
	 */
	public function get_recent_activity_rows( $limit = 10 ) {
		global $wpdb;
		$table = $wpdb->prefix . 'spai_activity_log';

		$limit = max( 1, min( 50, absint( $limit ) ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from $wpdb->prefix.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, action, endpoint, method, status_code, created_at
				 FROM {$table}
				 ORDER BY created_at DESC
				 LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Display admin notices.
	 */
	public function admin_notices() {
		settings_errors( 'spai_messages' );
	}

	/**
	 * Add plugin action links.
	 *
	 * @param array $links Existing links.
	 * @return array Modified links.
	 */
	public function add_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'admin.php?page=' . self::PAGE_SLUG ),
			__( 'Settings', 'site-pilot-ai' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Add network admin menu page for multisite.
	 */
	public function add_network_admin_menu() {
		add_menu_page(
			__( 'Site Pilot AI Network', 'site-pilot-ai' ),
			__( 'Site Pilot AI', 'site-pilot-ai' ),
			'manage_network_plugins',
			'site-pilot-ai-network',
			array( $this, 'render_network_admin_page' ),
			self::MENU_ICON,
			80
		);
	}

	/**
	 * Handle "Setup All Sites" POST action from network admin page.
	 */
	private function handle_network_setup_all() {
		if ( ! isset( $_POST['spai_network_setup_all'] ) ) {
			return;
		}

		check_admin_referer( 'spai_network_setup_all', 'spai_network_nonce' );

		if ( ! current_user_can( 'manage_network_plugins' ) ) {
			return;
		}

		require_once SPAI_PLUGIN_DIR . 'includes/class-spai-activator.php';

		$sites = get_sites( array( 'fields' => 'ids' ) );
		$count = 0;

		foreach ( $sites as $blog_id ) {
			switch_to_blog( $blog_id );
			Spai_Activator::activate();
			$count++;
			restore_current_blog();
		}

		add_settings_error(
			'spai_network_messages',
			'spai_network_setup_done',
			sprintf(
				/* translators: %d: number of sites */
				__( 'Site Pilot AI activated on %d site(s).', 'site-pilot-ai' ),
				$count
			),
			'updated'
		);
	}

	/**
	 * Render the network admin page.
	 */
	public function render_network_admin_page() {
		if ( ! current_user_can( 'manage_network_plugins' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'site-pilot-ai' ) );
		}

		$this->handle_network_setup_all();

		$sites      = get_sites( array( 'number' => 500 ) );
		$site_data  = array();

		foreach ( $sites as $site ) {
			switch_to_blog( $site->blog_id );

			$version        = get_option( 'spai_version', '' );
			$has_api_key    = ! empty( get_option( 'spai_api_key' ) );
			$scoped_keys    = get_option( 'spai_api_keys', array() );
			$active_keys    = 0;
			if ( is_array( $scoped_keys ) ) {
				foreach ( $scoped_keys as $key ) {
					if ( empty( $key['revoked_at'] ) ) {
						$active_keys++;
					}
				}
			}

			$tool_count = 0;
			if ( class_exists( 'Spai_MCP_Tool_Registry' ) ) {
				$registry   = new Spai_MCP_Tool_Registry();
				$tool_count = count( $registry->get_all_tools() );
			}

			$site_data[] = array(
				'blog_id'     => $site->blog_id,
				'blogname'    => get_option( 'blogname', $site->domain . $site->path ),
				'siteurl'     => get_option( 'siteurl' ),
				'version'     => $version,
				'has_api_key' => $has_api_key,
				'active_keys' => $active_keys,
				'tool_count'  => $tool_count,
			);

			restore_current_blog();
		}

		settings_errors( 'spai_network_messages' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Site Pilot AI — Network Overview', 'site-pilot-ai' ); ?></h1>

			<form method="post">
				<?php wp_nonce_field( 'spai_network_setup_all', 'spai_network_nonce' ); ?>
				<p>
					<input type="submit" name="spai_network_setup_all" class="button button-primary"
						value="<?php esc_attr_e( 'Setup All Sites', 'site-pilot-ai' ); ?>"
						onclick="return confirm('<?php echo esc_js( __( 'Run activation (tables, options, bot user) on every site in the network?', 'site-pilot-ai' ) ); ?>');" />
					<span class="description"><?php esc_html_e( 'Runs activation on every site to ensure tables, options, and the bot user are provisioned.', 'site-pilot-ai' ); ?></span>
				</p>
			</form>

			<table class="widefat striped" style="margin-top:20px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'ID', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'Site', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'URL', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'Version', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'API Key', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'Active Keys', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'Tools', 'site-pilot-ai' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $site_data as $s ) : ?>
					<tr>
						<td><?php echo esc_html( $s['blog_id'] ); ?></td>
						<td><?php echo esc_html( $s['blogname'] ); ?></td>
						<td><a href="<?php echo esc_url( $s['siteurl'] ); ?>" target="_blank"><?php echo esc_html( $s['siteurl'] ); ?></a></td>
						<td>
							<?php if ( $s['version'] ) : ?>
								<?php echo esc_html( $s['version'] ); ?>
							<?php else : ?>
								<span style="color:#b32d2e;"><?php esc_html_e( 'Not activated', 'site-pilot-ai' ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( $s['has_api_key'] ) : ?>
								<span style="color:#00a32a;">&#10003;</span>
							<?php else : ?>
								<span style="color:#b32d2e;">&#10007;</span>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $s['active_keys'] ); ?></td>
						<td><?php echo esc_html( $s['tool_count'] ); ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Get capabilities for display.
	 *
	 * @return array Capabilities.
	 */
	public function get_capabilities_display() {
		$core = new Spai_Core();
		$capabilities = $core->get_capabilities();

		$display = array();

		// Elementor
		$display['elementor'] = array(
			'label'  => __( 'Elementor', 'site-pilot-ai' ),
			'active' => $capabilities['elementor'],
			'pro'    => $capabilities['elementor_pro'],
		);

		// SEO
		$seo_active = $capabilities['yoast'] || $capabilities['rankmath'] || $capabilities['aioseo'] || $capabilities['seopress'];
		$seo_name = '';
		if ( $capabilities['yoast'] ) {
			$seo_name = 'Yoast SEO';
		} elseif ( $capabilities['rankmath'] ) {
			$seo_name = 'RankMath';
		} elseif ( $capabilities['aioseo'] ) {
			$seo_name = 'All in One SEO';
		} elseif ( $capabilities['seopress'] ) {
			$seo_name = 'SEOPress';
		}
		$display['seo'] = array(
			'label'  => __( 'SEO Plugin', 'site-pilot-ai' ),
			'active' => $seo_active,
			'name'   => $seo_name,
		);

		// Forms
		$forms_active = $capabilities['cf7'] || $capabilities['wpforms'] || $capabilities['gravityforms'] || $capabilities['ninjaforms'];
		$forms = array();
		if ( $capabilities['cf7'] ) {
			$forms[] = 'CF7';
		}
		if ( $capabilities['wpforms'] ) {
			$forms[] = 'WPForms';
		}
		if ( $capabilities['gravityforms'] ) {
			$forms[] = 'Gravity Forms';
		}
		if ( $capabilities['ninjaforms'] ) {
			$forms[] = 'Ninja Forms';
		}
		$display['forms'] = array(
			'label'  => __( 'Form Plugins', 'site-pilot-ai' ),
			'active' => $forms_active,
			'names'  => $forms,
		);

		// WooCommerce
		$display['woocommerce'] = array(
			'label'  => __( 'WooCommerce', 'site-pilot-ai' ),
			'active' => $capabilities['woocommerce'],
		);

		return $display;
	}
}
