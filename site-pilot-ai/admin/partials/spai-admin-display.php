<?php
/**
 * Admin page template
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$stored_key_hash = get_option( 'spai_api_key', '' );
$admin           = new Spai_Admin();
$capabilities    = $admin->get_capabilities_display();
$is_pro          = true;
$is_first        = get_option( 'spai_first_activation', false );
$rest_base       = rest_url( 'site-pilot-ai/v1/' );
$mcp_url         = rest_url( 'site-pilot-ai/v1/mcp' );
$site_name       = get_bloginfo( 'name' );
$site_slug       = sanitize_title( $site_name );
$library_inventory = $admin->get_library_inventory();
$library_filters   = $admin->get_library_filters();
$library_filter_options = $admin->get_library_filter_options( $library_inventory );
$library_inventory = $admin->filter_library_inventory( $library_inventory, $library_filters );
$site_context_preview = $admin->get_site_context_preview();
$llms_url             = $admin->get_llms_url();
$llms_preview         = $admin->get_llms_preview();
$update_channel       = $admin->get_update_channel_status();
$onboarding_status    = $admin->get_onboarding_status();
// Non-Latin site names (Persian, Arabic, CJK) produce URL-encoded slugs — fall back to hostname.
if ( empty( $site_slug ) || false !== strpos( $site_slug, '%' ) ) {
	$site_slug = preg_replace( '/^www\./', '', wp_parse_url( home_url(), PHP_URL_HOST ) );
	$site_slug = str_replace( '.', '-', $site_slug );
}

// Current tab — admin page, read-only navigation parameter.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'setup';

// Determine key display
if ( isset( $new_key ) && $new_key ) {
	$display_key = $new_key;
	$is_hidden   = false;
} elseif ( ! empty( $stored_key_hash ) ) {
	$display_key = 'spai_******************** (Hidden)';
	$is_hidden   = true;
} else {
	$display_key = '';
	$is_hidden   = false;
}
?>

<div class="wrap spai-admin">
	<h1 class="spai-header">
		<span class="spai-logo">
			<span class="dashicons dashicons-airplane"></span>
		</span>
		<?php esc_html_e( 'Mumega Site Pilot AI', 'site-pilot-ai' ); ?>
		<span class="spai-version">v<?php echo esc_html( SPAI_VERSION ); ?></span>
	</h1>

	<?php if ( $is_first && isset( $new_key ) && $new_key ) : ?>
	<!-- First-time welcome banner -->
	<div class="spai-welcome-banner" id="spai-welcome">
		<div class="spai-welcome-icon">
			<span class="dashicons dashicons-yes-alt"></span>
		</div>
		<div class="spai-welcome-content">
			<h2><?php esc_html_e( 'Site Pilot AI is ready!', 'site-pilot-ai' ); ?></h2>
			<p><?php esc_html_e( 'Your API key has been generated. Copy it now and use it to connect Claude Desktop, Claude Code, or ChatGPT to your WordPress site.', 'site-pilot-ai' ); ?></p>
			<div class="spai-api-key-wrapper spai-api-key-wrapper--highlight">
				<input
					type="text"
					id="spai-welcome-key"
					class="spai-api-key-input"
					value="<?php echo esc_attr( $new_key ); ?>"
					readonly
				/>
				<button type="button" class="button button-primary spai-copy-btn" data-copy="<?php echo esc_attr( $new_key ); ?>">
					<span class="dashicons dashicons-clipboard"></span>
					<?php esc_html_e( 'Copy Key', 'site-pilot-ai' ); ?>
				</button>
			</div>
			<p class="spai-welcome-warning">
				<strong><?php esc_html_e( 'Save this key now!', 'site-pilot-ai' ); ?></strong>
				<?php esc_html_e( 'It will not be shown again after you leave this page. You can always regenerate a new key.', 'site-pilot-ai' ); ?>
			</p>
			<button type="button" class="button spai-dismiss-welcome" id="spai-dismiss-welcome">
				<?php esc_html_e( 'Got it, I\'ve saved my key', 'site-pilot-ai' ); ?>
			</button>
		</div>
	</div>
	<?php endif; ?>

	<div class="spai-license-banner spai-license-active">
		<div class="spai-license-content">
			<span class="dashicons dashicons-yes-alt"></span>
			<strong><?php esc_html_e( 'All features included', 'site-pilot-ai' ); ?></strong>
			&mdash;
			<a href="https://mumega.com/" target="_blank"><?php esc_html_e( 'Powered by Mumega', 'site-pilot-ai' ); ?></a>
		</div>
	</div>

	<!-- Tab Navigation -->
	<nav class="nav-tab-wrapper spai-tabs">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=site-pilot-ai&tab=setup' ) ); ?>"
		   class="nav-tab <?php echo 'setup' === $current_tab ? 'nav-tab-active' : ''; ?>">
			<span class="dashicons dashicons-admin-tools"></span>
			<?php esc_html_e( 'Setup', 'site-pilot-ai' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=site-pilot-ai&tab=connect' ) ); ?>"
		   class="nav-tab <?php echo 'connect' === $current_tab ? 'nav-tab-active' : ''; ?>">
			<span class="dashicons dashicons-cloud"></span>
			<?php esc_html_e( 'Connect AI', 'site-pilot-ai' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=site-pilot-ai&tab=settings' ) ); ?>"
		   class="nav-tab <?php echo 'settings' === $current_tab ? 'nav-tab-active' : ''; ?>">
			<span class="dashicons dashicons-admin-generic"></span>
			<?php esc_html_e( 'Settings', 'site-pilot-ai' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=site-pilot-ai&tab=library' ) ); ?>"
		   class="nav-tab <?php echo 'library' === $current_tab ? 'nav-tab-active' : ''; ?>">
			<span class="dashicons dashicons-screenoptions"></span>
			<?php esc_html_e( 'Library', 'site-pilot-ai' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=site-pilot-ai&tab=advanced' ) ); ?>"
		   class="nav-tab <?php echo 'advanced' === $current_tab ? 'nav-tab-active' : ''; ?>">
			<span class="dashicons dashicons-editor-code"></span>
			<?php esc_html_e( 'Advanced', 'site-pilot-ai' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=site-pilot-ai&tab=changelog' ) ); ?>"
		   class="nav-tab <?php echo 'changelog' === $current_tab ? 'nav-tab-active' : ''; ?>">
			<span class="dashicons dashicons-list-view"></span>
			<?php esc_html_e( 'Changelog', 'site-pilot-ai' ); ?>
		</a>
	</nav>

	<!-- ======================== SETUP TAB ======================== -->
	<?php if ( 'setup' === $current_tab ) : ?>

	<div class="spai-tab-content">
		<div class="spai-card">
			<h2><?php esc_html_e( 'Operator Onboarding', 'site-pilot-ai' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'This checklist shows what is already in place and what the operator should do next to turn the site into a reusable AI production system.', 'site-pilot-ai' ); ?>
			</p>

			<div class="spai-library-summary">
				<div class="spai-library-stat">
					<span class="spai-library-stat__value"><?php echo esc_html( $onboarding_status['completed'] . '/' . $onboarding_status['total'] ); ?></span>
					<span class="spai-library-stat__label"><?php esc_html_e( 'Core Setup Complete', 'site-pilot-ai' ); ?></span>
				</div>
				<div class="spai-library-stat">
					<span class="spai-library-stat__value"><?php echo esc_html( $onboarding_status['next_step'] ? $onboarding_status['next_step']['title'] : __( 'Ready', 'site-pilot-ai' ) ); ?></span>
					<span class="spai-library-stat__label"><?php esc_html_e( 'Next Best Step', 'site-pilot-ai' ); ?></span>
				</div>
			</div>

			<div class="spai-onboarding-checklist">
				<?php foreach ( $onboarding_status['steps'] as $step ) : ?>
					<div class="spai-onboarding-step">
						<div class="spai-onboarding-step__status <?php echo ! empty( $step['done'] ) ? 'is-done' : 'is-open'; ?>">
							<?php echo ! empty( $step['done'] ) ? esc_html__( 'Done', 'site-pilot-ai' ) : esc_html__( 'Next', 'site-pilot-ai' ); ?>
						</div>
						<div class="spai-onboarding-step__body">
							<strong><?php echo esc_html( $step['title'] ); ?></strong>
							<div class="spai-design-reference__meta"><?php echo esc_html( $step['description'] ); ?></div>
						</div>
						<div class="spai-onboarding-step__action">
							<a class="button button-small" href="<?php echo esc_url( $step['url'] ); ?>"><?php echo esc_html( $step['cta'] ); ?></a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="spai-card">
			<h2><?php esc_html_e( 'Updates & Recovery', 'site-pilot-ai' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Site Pilot AI can detect new versions automatically, but plugin installation still depends on your host allowing WordPress to replace plugin files. When shared hosting blocks that step, use the manual recovery path below.', 'site-pilot-ai' ); ?>
			</p>

			<div class="spai-library-summary">
				<div class="spai-library-stat">
					<span class="spai-library-stat__value"><?php echo esc_html( $update_channel['current_version'] ); ?></span>
					<span class="spai-library-stat__label"><?php esc_html_e( 'Installed', 'site-pilot-ai' ); ?></span>
				</div>
				<div class="spai-library-stat">
					<span class="spai-library-stat__value"><?php echo esc_html( $update_channel['remote_version'] ? $update_channel['remote_version'] : 'n/a' ); ?></span>
					<span class="spai-library-stat__label"><?php esc_html_e( 'Remote', 'site-pilot-ai' ); ?></span>
				</div>
				<div class="spai-library-stat">
					<span class="spai-library-stat__value"><?php echo esc_html( $update_channel['update_available'] ? __( 'Yes', 'site-pilot-ai' ) : __( 'No', 'site-pilot-ai' ) ); ?></span>
					<span class="spai-library-stat__label"><?php esc_html_e( 'Update Available', 'site-pilot-ai' ); ?></span>
				</div>
			</div>

			<div class="spai-update-panel">
				<div class="spai-update-panel__row">
					<strong><?php esc_html_e( 'Manifest URL', 'site-pilot-ai' ); ?></strong>
					<code><?php echo esc_html( $update_channel['manifest_url'] ); ?></code>
				</div>
				<div class="spai-update-panel__row">
					<strong><?php esc_html_e( 'Package URL', 'site-pilot-ai' ); ?></strong>
					<code><?php echo esc_html( $update_channel['download_url'] ); ?></code>
				</div>
				<div class="spai-update-panel__row">
					<strong><?php esc_html_e( 'Update Source', 'site-pilot-ai' ); ?></strong>
					<span><?php echo esc_html( $update_channel['source'] ); ?></span>
				</div>
				<?php if ( ! empty( $update_channel['option_version'] ) ) : ?>
					<div class="spai-update-panel__row">
						<strong><?php esc_html_e( 'Site Override Version', 'site-pilot-ai' ); ?></strong>
						<span><?php echo esc_html( $update_channel['option_version'] ); ?></span>
					</div>
				<?php endif; ?>
				<?php if ( ! empty( $update_channel['warning'] ) ) : ?>
					<div class="spai-update-panel__row spai-update-panel__row--warning">
						<strong><?php esc_html_e( 'Warning', 'site-pilot-ai' ); ?></strong>
						<span><?php echo esc_html( $update_channel['warning'] ); ?></span>
					</div>
				<?php endif; ?>
			</div>

			<p>
				<a class="button button-primary" href="<?php echo esc_url( $update_channel['download_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Download Latest ZIP', 'site-pilot-ai' ); ?></a>
			</p>

			<h3><?php esc_html_e( 'Manual Recovery Path', 'site-pilot-ai' ); ?></h3>
			<ol>
				<?php foreach ( $update_channel['manual_steps'] as $step ) : ?>
					<li><?php echo esc_html( $step ); ?></li>
				<?php endforeach; ?>
			</ol>
		</div>

		<div class="spai-card">
			<h2><?php esc_html_e( 'Recent Activity', 'site-pilot-ai' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Latest API activity captured by Site Pilot AI. Use Activity Log for full history and details.', 'site-pilot-ai' ); ?>
			</p>

			<?php
			$recent = $admin->get_recent_activity_rows( 10 );
			$activity_url = admin_url( 'admin.php?page=' . Spai_Admin::ACTIVITY_LOG_PAGE_SLUG );
			?>

			<?php if ( empty( $recent ) ) : ?>
				<p><em><?php esc_html_e( 'No activity yet.', 'site-pilot-ai' ); ?></em></p>
			<?php else : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th style="width:160px;"><?php esc_html_e( 'When', 'site-pilot-ai' ); ?></th>
							<th style="width:140px;"><?php esc_html_e( 'Action', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'Endpoint', 'site-pilot-ai' ); ?></th>
							<th style="width:70px;"><?php esc_html_e( 'Method', 'site-pilot-ai' ); ?></th>
							<th style="width:70px;"><?php esc_html_e( 'Status', 'site-pilot-ai' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $recent as $row ) : ?>
						<tr>
							<td><?php echo esc_html( (string) $row['created_at'] ); ?></td>
							<td><?php echo esc_html( (string) $row['action'] ); ?></td>
							<td><code><?php echo esc_html( (string) $row['endpoint'] ); ?></code></td>
							<td><?php echo esc_html( (string) $row['method'] ); ?></td>
							<td><?php echo esc_html( (string) $row['status_code'] ); ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<p style="margin-top:10px;">
					<a class="button" href="<?php echo esc_url( $activity_url ); ?>"><?php esc_html_e( 'Open Activity Log', 'site-pilot-ai' ); ?></a>
				</p>
			<?php endif; ?>
		</div>

		<!-- API Key Card -->
		<div class="spai-card">
			<h2><?php esc_html_e( 'API Key', 'site-pilot-ai' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'This key authenticates AI assistants (Claude, ChatGPT) when they connect to your site.', 'site-pilot-ai' ); ?>
			</p>

			<div class="spai-api-key-wrapper">
				<input
					type="text"
					id="spai-api-key"
					class="spai-api-key-input"
					value="<?php echo esc_attr( $display_key ); ?>"
					readonly
				/>
				<?php if ( ! $is_hidden ) : ?>
				<button type="button" class="button spai-copy-btn" data-copy="<?php echo esc_attr( $display_key ); ?>">
					<span class="dashicons dashicons-clipboard"></span>
					<?php esc_html_e( 'Copy', 'site-pilot-ai' ); ?>
				</button>
				<?php endif; ?>
			</div>

			<?php if ( $is_hidden ) : ?>
			<p class="description">
				<?php esc_html_e( 'Your API key is stored securely (hashed). To see it, regenerate a new one below.', 'site-pilot-ai' ); ?>
			</p>
			<?php endif; ?>

			<form method="post" class="spai-regenerate-form">
				<?php wp_nonce_field( 'spai_regenerate_key', 'spai_nonce' ); ?>
				<button type="submit" name="spai_regenerate_key" class="button spai-regenerate-btn">
					<span class="dashicons dashicons-update"></span>
					<?php esc_html_e( 'Regenerate Key', 'site-pilot-ai' ); ?>
				</button>
				<span class="description">
					<?php esc_html_e( 'The old key will stop working immediately.', 'site-pilot-ai' ); ?>
				</span>
			</form>

			<h3><?php esc_html_e( 'API Keys', 'site-pilot-ai' ); ?></h3>
			<p class="description">
				<?php esc_html_e( 'Create role-based keys to control which tools AI assistants can access. Each role limits the MCP tools visible to the AI.', 'site-pilot-ai' ); ?>
			</p>

			<?php if ( ! empty( $new_scoped_key['key'] ) ) : ?>
			<div class="spai-api-key-wrapper spai-api-key-wrapper--highlight">
				<input
					type="text"
					class="spai-api-key-input"
					value="<?php echo esc_attr( $new_scoped_key['key'] ); ?>"
					readonly
				/>
				<button type="button" class="button button-primary spai-copy-btn" data-copy="<?php echo esc_attr( $new_scoped_key['key'] ); ?>">
					<span class="dashicons dashicons-clipboard"></span>
					<?php esc_html_e( 'Copy Key', 'site-pilot-ai' ); ?>
				</button>
			</div>
			<?php endif; ?>

			<?php
			$role_definitions = Spai_Admin::get_role_definitions();
			$all_cat_labels   = Spai_Admin::get_all_tool_category_labels();
			?>

			<form method="post" class="spai-regenerate-form">
				<?php wp_nonce_field( 'spai_manage_scoped_keys', 'spai_scoped_keys_nonce' ); ?>
				<p>
					<label for="spai_scoped_key_label"><strong><?php esc_html_e( 'Label', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="text" id="spai_scoped_key_label" name="spai_scoped_key_label" class="regular-text" placeholder="<?php esc_attr_e( 'Example: Content Writer Bot', 'site-pilot-ai' ); ?>" />
				</p>
				<p>
					<label for="spai_scoped_key_role"><strong><?php esc_html_e( 'Role', 'site-pilot-ai' ); ?></strong></label><br />
					<select id="spai_scoped_key_role" name="spai_scoped_key_role" style="min-width:200px;">
						<?php foreach ( $role_definitions as $role_slug => $role_def ) : ?>
						<option value="<?php echo esc_attr( $role_slug ); ?>"
							data-categories="<?php echo esc_attr( wp_json_encode( $role_def['categories'] ) ); ?>">
							<?php echo esc_html( $role_def['label'] ); ?> &mdash; <?php echo esc_html( $role_def['description'] ); ?>
						</option>
						<?php endforeach; ?>
					</select>
				</p>
				<div id="spai-custom-categories" style="display:none;margin-bottom:12px;">
					<strong><?php esc_html_e( 'Tool Categories', 'site-pilot-ai' ); ?></strong><br />
					<p class="description" style="margin-bottom:6px;">
						<?php esc_html_e( 'Select which tool categories this key can access.', 'site-pilot-ai' ); ?>
					</p>
					<?php foreach ( $all_cat_labels as $cat_slug => $cat_label ) : ?>
					<label style="display:inline-block;min-width:120px;margin:2px 0;">
						<input type="checkbox" name="spai_scoped_key_categories[]" value="<?php echo esc_attr( $cat_slug ); ?>" class="spai-category-checkbox" />
						<?php echo esc_html( $cat_label ); ?>
					</label>
					<?php endforeach; ?>
				</div>
				<div id="spai-role-preview" style="margin-bottom:12px;padding:8px 12px;background:#f0f0f1;border-radius:4px;display:none;">
					<strong><?php esc_html_e( 'Access:', 'site-pilot-ai' ); ?></strong>
					<span id="spai-role-preview-categories"></span>
				</div>
				<p>
					<strong><?php esc_html_e( 'Scopes', 'site-pilot-ai' ); ?></strong><br />
					<label><input type="checkbox" name="spai_scoped_key_scopes[]" value="read" checked /> <?php esc_html_e( 'Read', 'site-pilot-ai' ); ?></label>
					<label style="margin-left:12px;"><input type="checkbox" name="spai_scoped_key_scopes[]" value="write" /> <?php esc_html_e( 'Write', 'site-pilot-ai' ); ?></label>
					<label style="margin-left:12px;"><input type="checkbox" name="spai_scoped_key_scopes[]" value="admin" /> <?php esc_html_e( 'Admin', 'site-pilot-ai' ); ?></label>
				</p>
				<button type="submit" name="spai_create_scoped_key" class="button button-primary">
					<?php esc_html_e( 'Create API Key', 'site-pilot-ai' ); ?>
				</button>
			</form>

			<script>
			(function() {
				var roleSelect = document.getElementById('spai_scoped_key_role');
				var customDiv  = document.getElementById('spai-custom-categories');
				var previewDiv = document.getElementById('spai-role-preview');
				var previewCat = document.getElementById('spai-role-preview-categories');
				var checkboxes = document.querySelectorAll('.spai-category-checkbox');
				var catLabels  = <?php echo wp_json_encode( $all_cat_labels ); ?>;

				function updateRoleUI() {
					var sel  = roleSelect.options[roleSelect.selectedIndex];
					var role = sel.value;
					var cats = JSON.parse(sel.getAttribute('data-categories') || '[]');

					if (role === 'custom') {
						customDiv.style.display = 'block';
						previewDiv.style.display = 'none';
					} else if (role === 'admin') {
						customDiv.style.display = 'none';
						previewDiv.style.display = 'block';
						previewCat.textContent = '<?php echo esc_js( __( 'All categories (unrestricted)', 'site-pilot-ai' ) ); ?>';
					} else {
						customDiv.style.display = 'none';
						previewDiv.style.display = 'block';
						var labels = cats.map(function(c) { return catLabels[c] || c; });
						previewCat.textContent = labels.join(', ');
					}

					// Auto-check matching categories for preset roles.
					if (role !== 'custom') {
						checkboxes.forEach(function(cb) {
							cb.checked = cats.indexOf(cb.value) !== -1;
						});
					}
				}

				roleSelect.addEventListener('change', updateRoleUI);
				updateRoleUI();
			})();
			</script>

			<?php
			// Compute role badge colors.
			$role_colors = array(
				'admin'    => '#d63638',
				'author'   => '#2271b1',
				'designer' => '#8c5fc7',
				'editor'   => '#00a32a',
				'custom'   => '#996800',
			);
			?>

			<?php if ( ! empty( $scoped_keys ) ) : ?>
			<table class="widefat striped" style="margin-top:12px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Label', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'Role', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'Categories', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'Created', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'Last Used', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'Status', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'Action', 'site-pilot-ai' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $scoped_keys as $key ) :
						$key_role  = isset( $key['role'] ) ? $key['role'] : 'admin';
						$role_def  = isset( $role_definitions[ $key_role ] ) ? $role_definitions[ $key_role ] : $role_definitions['admin'];
						$badge_bg  = isset( $role_colors[ $key_role ] ) ? $role_colors[ $key_role ] : '#50575e';

						// Resolve display categories.
						$display_cats = array();
						if ( 'admin' === $key_role ) {
							$display_cats = array( __( 'All', 'site-pilot-ai' ) );
						} elseif ( 'custom' === $key_role && ! empty( $key['tool_categories'] ) ) {
							foreach ( $key['tool_categories'] as $cat ) {
								$display_cats[] = isset( $all_cat_labels[ $cat ] ) ? $all_cat_labels[ $cat ] : $cat;
							}
						} elseif ( ! empty( $role_def['categories'] ) ) {
							foreach ( $role_def['categories'] as $cat ) {
								$display_cats[] = isset( $all_cat_labels[ $cat ] ) ? $all_cat_labels[ $cat ] : $cat;
							}
						}
					?>
					<tr<?php echo ! empty( $key['revoked_at'] ) ? ' style="opacity:0.5;"' : ''; ?>>
						<td><strong><?php echo esc_html( $key['label'] ); ?></strong></td>
						<td>
							<span style="display:inline-block;background:<?php echo esc_attr( $badge_bg ); ?>;color:#fff;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600;">
								<?php echo esc_html( $role_def['label'] ); ?>
							</span>
						</td>
						<td><?php echo esc_html( implode( ', ', $display_cats ) ); ?></td>
						<td><?php echo ! empty( $key['created_at'] ) ? esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $key['created_at'] ) ) ) : '&mdash;'; ?></td>
						<td><?php echo ! empty( $key['last_used_at'] ) ? esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $key['last_used_at'] ) ) ) : '&mdash;'; ?></td>
						<td>
							<?php if ( ! empty( $key['revoked_at'] ) ) : ?>
								<span class="spai-status spai-status-inactive"><?php esc_html_e( 'Revoked', 'site-pilot-ai' ); ?></span>
							<?php else : ?>
								<span class="spai-status spai-status-active"><?php esc_html_e( 'Active', 'site-pilot-ai' ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( empty( $key['revoked_at'] ) ) : ?>
							<form method="post" style="display:inline;">
								<?php wp_nonce_field( 'spai_manage_scoped_keys', 'spai_scoped_keys_nonce' ); ?>
								<input type="hidden" name="spai_scoped_key_id" value="<?php echo esc_attr( $key['id'] ); ?>" />
								<button type="submit" name="spai_revoke_scoped_key" class="button button-link-delete" onclick="return confirm('<?php echo esc_js( __( 'Revoke this key?', 'site-pilot-ai' ) ); ?>');">
									<?php esc_html_e( 'Revoke', 'site-pilot-ai' ); ?>
								</button>
							</form>
							<?php else : ?>
								&mdash;
							<?php endif; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>
		</div>

		<!-- Test Connection Card -->
		<div class="spai-card">
			<h2><?php esc_html_e( 'Connection Status', 'site-pilot-ai' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Verify that your REST API is working correctly.', 'site-pilot-ai' ); ?>
			</p>

			<div class="spai-test-connection">
				<button type="button" class="button button-primary" id="spai-test-btn">
					<span class="dashicons dashicons-yes-alt"></span>
					<?php esc_html_e( 'Test Connection', 'site-pilot-ai' ); ?>
				</button>
				<div class="spai-test-result" id="spai-test-result" style="display:none;">
					<div class="spai-test-success" style="display:none;">
						<span class="dashicons dashicons-yes"></span>
						<span class="spai-test-message"></span>
					</div>
					<div class="spai-test-error" style="display:none;">
						<span class="dashicons dashicons-no"></span>
						<span class="spai-test-message"></span>
					</div>
					<div class="spai-test-details" style="display:none;"></div>
				</div>
			</div>
		</div>

		<!-- Detected Capabilities Card -->
		<div class="spai-card">
			<h2><?php esc_html_e( 'Detected Capabilities', 'site-pilot-ai' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Plugins detected on your site that Site Pilot AI can work with.', 'site-pilot-ai' ); ?>
			</p>
			<table class="widefat spai-capabilities-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Feature', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'Status', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'Details', 'site-pilot-ai' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $capabilities as $key => $cap ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $cap['label'] ); ?></strong></td>
						<td>
							<?php if ( $cap['active'] ) : ?>
								<span class="spai-status spai-status-active"><?php esc_html_e( 'Active', 'site-pilot-ai' ); ?></span>
							<?php else : ?>
								<span class="spai-status spai-status-inactive"><?php esc_html_e( 'Not Detected', 'site-pilot-ai' ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<?php
							if ( isset( $cap['pro'] ) && $cap['pro'] ) {
								echo '<span class="spai-badge spai-badge-pro">Pro</span> ';
							}
							if ( isset( $cap['name'] ) && $cap['name'] ) {
								echo esc_html( $cap['name'] );
							}
							if ( isset( $cap['names'] ) && ! empty( $cap['names'] ) ) {
								echo esc_html( implode( ', ', $cap['names'] ) );
							}
							?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- ======================== CONNECT AI TAB ======================== -->
	<?php elseif ( 'connect' === $current_tab ) : ?>

	<div class="spai-tab-content">
		<!-- Claude Desktop — Custom Connector (Recommended) -->
		<div class="spai-card">
			<h2>
				<span class="dashicons dashicons-cloud"></span>
				<?php esc_html_e( 'Claude Desktop — Custom Connector', 'site-pilot-ai' ); ?>
				<span class="spai-badge" style="background:#28a745;color:#fff;margin-left:8px;"><?php esc_html_e( 'Recommended', 'site-pilot-ai' ); ?></span>
			</h2>
			<p class="description">
				<?php esc_html_e( 'The fastest way to connect. No config files, no npm — just paste a URL.', 'site-pilot-ai' ); ?>
			</p>

			<div class="spai-setup-steps">
				<div class="spai-step">
					<span class="spai-step-number">1</span>
					<div class="spai-step-content">
						<h3><?php esc_html_e( 'Open Connectors', 'site-pilot-ai' ); ?></h3>
						<p><?php esc_html_e( 'In Claude Desktop, go to Settings → Connectors → Add custom connector', 'site-pilot-ai' ); ?></p>
					</div>
				</div>

				<div class="spai-step">
					<span class="spai-step-number">2</span>
					<div class="spai-step-content">
						<h3><?php esc_html_e( 'Fill in the fields', 'site-pilot-ai' ); ?></h3>
						<table class="spai-connector-fields">
							<tr>
								<td><strong><?php esc_html_e( 'Name', 'site-pilot-ai' ); ?></strong></td>
								<td>
									<div class="spai-code-wrapper spai-code-inline">
										<code id="spai-connector-name">sitepilotai-<?php echo esc_html( $site_slug ); ?></code>
										<button type="button" class="button spai-copy-code-btn spai-copy-code-btn--inline" data-target="spai-connector-name">
											<span class="dashicons dashicons-clipboard"></span>
										</button>
									</div>
								</td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e( 'Remote MCP server URL', 'site-pilot-ai' ); ?></strong></td>
								<td>
									<div class="spai-code-wrapper spai-code-inline">
										<code id="spai-connector-url"><?php echo esc_url( add_query_arg( 'api_key', ( $is_hidden ? 'YOUR_API_KEY' : $display_key ), $mcp_url ) ); ?></code>
										<button type="button" class="button spai-copy-code-btn spai-copy-code-btn--inline" data-target="spai-connector-url">
											<span class="dashicons dashicons-clipboard"></span>
										</button>
									</div>
								</td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e( 'OAuth fields', 'site-pilot-ai' ); ?></strong></td>
								<td><em><?php esc_html_e( 'Leave empty', 'site-pilot-ai' ); ?></em></td>
							</tr>
						</table>
						<?php if ( $is_hidden ) : ?>
						<p class="description" style="margin-top:10px;">
							<?php esc_html_e( 'Replace YOUR_API_KEY with your actual API key from the Setup tab.', 'site-pilot-ai' ); ?>
						</p>
						<?php endif; ?>
					</div>
				</div>

				<div class="spai-step">
					<span class="spai-step-number">3</span>
					<div class="spai-step-content">
						<h3><?php esc_html_e( 'Done!', 'site-pilot-ai' ); ?></h3>
						<p><?php esc_html_e( 'Claude Desktop will connect immediately. You should see your WordPress tools in the conversation.', 'site-pilot-ai' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<!-- Claude Desktop — JSON Config -->
		<div class="spai-card">
			<h2>
				<span class="dashicons dashicons-cloud"></span>
				<?php esc_html_e( 'Claude Desktop — JSON Config', 'site-pilot-ai' ); ?>
			</h2>
			<p class="description">
				<?php esc_html_e( 'Alternative method using the config file. Uses header-based auth (more secure for shared machines).', 'site-pilot-ai' ); ?>
			</p>

			<div class="spai-setup-steps">
				<div class="spai-step">
					<span class="spai-step-number">1</span>
					<div class="spai-step-content">
						<h3><?php esc_html_e( 'Open Claude Desktop Settings', 'site-pilot-ai' ); ?></h3>
						<p><?php esc_html_e( 'Go to Claude Desktop → Settings → Developer → Edit Config', 'site-pilot-ai' ); ?></p>
					</div>
				</div>

				<div class="spai-step">
					<span class="spai-step-number">2</span>
					<div class="spai-step-content">
						<h3><?php esc_html_e( 'Add this configuration', 'site-pilot-ai' ); ?></h3>
						<p><?php esc_html_e( 'Paste this into your claude_desktop_config.json file:', 'site-pilot-ai' ); ?></p>
						<div class="spai-code-wrapper">
							<pre class="spai-code-block" id="spai-claude-config">{
  "mcpServers": {
    "sitepilotai-<?php echo esc_html( $site_slug ); ?>": {
      "url": "<?php echo esc_url( $mcp_url ); ?>",
      "headers": {
        "X-API-Key": "<?php echo $is_hidden ? 'YOUR_API_KEY_HERE' : esc_attr( $display_key ); ?>"
      }
    }
  }
}</pre>
							<button type="button" class="button spai-copy-code-btn" data-target="spai-claude-config">
								<span class="dashicons dashicons-clipboard"></span>
								<?php esc_html_e( 'Copy', 'site-pilot-ai' ); ?>
							</button>
						</div>
					</div>
				</div>

				<div class="spai-step">
					<span class="spai-step-number">3</span>
					<div class="spai-step-content">
						<h3><?php esc_html_e( 'Restart Claude Desktop', 'site-pilot-ai' ); ?></h3>
						<p><?php esc_html_e( 'After saving the config, restart Claude Desktop. You should see the WordPress tools appear.', 'site-pilot-ai' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<!-- Claude Code (CLI) -->
		<div class="spai-card">
			<h2>
				<span class="dashicons dashicons-terminal"></span>
				<?php esc_html_e( 'Claude Code (CLI)', 'site-pilot-ai' ); ?>
			</h2>
			<p class="description">
				<?php esc_html_e( 'For developers using Claude Code in the terminal. Connects directly via Streamable HTTP — no proxy needed.', 'site-pilot-ai' ); ?>
			</p>

			<div class="spai-setup-steps">
				<div class="spai-step">
					<span class="spai-step-number">1</span>
					<div class="spai-step-content">
						<h3><?php esc_html_e( 'Add to your project settings', 'site-pilot-ai' ); ?></h3>
						<p><?php esc_html_e( 'Add to .mcp.json in your project root or ~/.claude.json for global access:', 'site-pilot-ai' ); ?></p>
						<div class="spai-code-wrapper">
							<pre class="spai-code-block" id="spai-claude-code-config">{
  "mcpServers": {
    "sitepilotai-<?php echo esc_html( $site_slug ); ?>": {
      "url": "<?php echo esc_url( $mcp_url ); ?>",
      "headers": {
        "X-API-Key": "<?php echo $is_hidden ? 'YOUR_API_KEY_HERE' : esc_attr( $display_key ); ?>"
      }
    }
  }
}</pre>
							<button type="button" class="button spai-copy-code-btn" data-target="spai-claude-code-config">
								<span class="dashicons dashicons-clipboard"></span>
								<?php esc_html_e( 'Copy', 'site-pilot-ai' ); ?>
							</button>
						</div>
					</div>
				</div>

				<div class="spai-step">
					<span class="spai-step-number">2</span>
					<div class="spai-step-content">
						<h3><?php esc_html_e( 'Or use the npm package', 'site-pilot-ai' ); ?></h3>
						<p><?php esc_html_e( 'The site-pilot-ai npm package provides a stdio proxy if your setup requires it:', 'site-pilot-ai' ); ?></p>
						<div class="spai-code-wrapper">
							<pre class="spai-code-block" id="spai-npm-config">{
  "mcpServers": {
    "sitepilotai-<?php echo esc_html( $site_slug ); ?>": {
      "command": "npx",
      "args": ["-y", "site-pilot-ai"],
      "env": {
        "WP_URL": "<?php echo esc_url( home_url() ); ?>",
        "WP_API_KEY": "<?php echo $is_hidden ? 'YOUR_API_KEY_HERE' : esc_attr( $display_key ); ?>",
        "WP_SITE_NAME": "<?php echo esc_attr( $site_slug ); ?>"
      }
    }
  }
}</pre>
							<button type="button" class="button spai-copy-code-btn" data-target="spai-npm-config">
								<span class="dashicons dashicons-clipboard"></span>
								<?php esc_html_e( 'Copy', 'site-pilot-ai' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- ChatGPT -->
		<div class="spai-card">
			<h2>
				<span class="dashicons dashicons-format-chat"></span>
				<?php esc_html_e( 'ChatGPT', 'site-pilot-ai' ); ?>
			</h2>
			<p class="description">
				<?php esc_html_e( 'Use ChatGPT to manage your site via a custom GPT with Actions:', 'site-pilot-ai' ); ?>
			</p>

			<div class="spai-setup-steps">
				<div class="spai-step">
					<span class="spai-step-number">1</span>
					<div class="spai-step-content">
						<h3><?php esc_html_e( 'Create a custom GPT', 'site-pilot-ai' ); ?></h3>
						<p><?php esc_html_e( 'Go to ChatGPT → Explore GPTs → Create and add an Action.', 'site-pilot-ai' ); ?></p>
					</div>
				</div>

				<div class="spai-step">
					<span class="spai-step-number">2</span>
					<div class="spai-step-content">
						<h3><?php esc_html_e( 'Configure authentication', 'site-pilot-ai' ); ?></h3>
						<p><?php esc_html_e( 'Set Authentication to "API Key", Header Name to "X-API-Key", and paste your API key.', 'site-pilot-ai' ); ?></p>
					</div>
				</div>

				<div class="spai-step">
					<span class="spai-step-number">3</span>
					<div class="spai-step-content">
						<h3><?php esc_html_e( 'Import the OpenAPI spec', 'site-pilot-ai' ); ?></h3>
						<p><?php esc_html_e( 'Use the API base URL as your server URL in the OpenAPI schema:', 'site-pilot-ai' ); ?></p>
						<div class="spai-code-wrapper">
							<pre class="spai-code-block" id="spai-rest-url"><?php echo esc_url( $rest_base ); ?></pre>
							<button type="button" class="button spai-copy-code-btn" data-target="spai-rest-url">
								<span class="dashicons dashicons-clipboard"></span>
								<?php esc_html_e( 'Copy', 'site-pilot-ai' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- MCP Endpoint Info -->
		<div class="spai-card">
			<h2>
				<span class="dashicons dashicons-rest-api"></span>
				<?php esc_html_e( 'MCP Endpoint', 'site-pilot-ai' ); ?>
			</h2>
			<p class="description">
				<?php esc_html_e( 'Your site exposes a native MCP (Model Context Protocol) endpoint. Any MCP-compatible AI client can connect:', 'site-pilot-ai' ); ?>
			</p>
			<div class="spai-code-wrapper">
				<pre class="spai-code-block" id="spai-mcp-url"><?php echo esc_url( $mcp_url ); ?></pre>
				<button type="button" class="button spai-copy-code-btn" data-target="spai-mcp-url">
					<span class="dashicons dashicons-clipboard"></span>
					<?php esc_html_e( 'Copy', 'site-pilot-ai' ); ?>
				</button>
			</div>
			<table class="spai-mcp-info-table" style="margin-top:15px;">
				<tr>
					<td><strong><?php esc_html_e( 'Protocol', 'site-pilot-ai' ); ?></strong></td>
					<td><?php esc_html_e( 'JSON-RPC 2.0 over Streamable HTTP (POST + GET)', 'site-pilot-ai' ); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Auth methods', 'site-pilot-ai' ); ?></strong></td>
					<td><code>X-API-Key</code> <?php esc_html_e( 'header', 'site-pilot-ai' ); ?> · <code>Authorization: Bearer</code> <?php esc_html_e( 'header', 'site-pilot-ai' ); ?> · <code>?api_key=</code> <?php esc_html_e( 'query param', 'site-pilot-ai' ); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Methods', 'site-pilot-ai' ); ?></strong></td>
					<td><code>initialize</code>, <code>tools/list</code>, <code>tools/call</code>, <code>resources/list</code>, <code>resources/read</code></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Server name', 'site-pilot-ai' ); ?></strong></td>
					<td><code>site-pilot-ai:<?php echo esc_html( $site_name ); ?></code></td>
				</tr>
			</table>
		</div>
	</div>

	<!-- ======================== SETTINGS TAB ======================== -->
	<?php elseif ( 'settings' === $current_tab ) : ?>

	<div class="spai-tab-content">
		<div class="spai-card">
			<h2>
				<span class="dashicons dashicons-awards"></span>
				<?php esc_html_e( 'About', 'site-pilot-ai' ); ?>
			</h2>
			<p class="description">
				<?php esc_html_e( 'All features are included for free. Mumega Site Pilot AI connects your WordPress site to AI assistants via the Model Context Protocol (MCP).', 'site-pilot-ai' ); ?>
			</p>
			<p style="margin-top: 10px;">
				<a href="https://mumega.com/" target="_blank" class="button">
					<span class="dashicons dashicons-external" style="margin-top: 4px;"></span>
					<?php esc_html_e( 'Visit Mumega', 'site-pilot-ai' ); ?>
				</a>
			</p>
		</div>

		<div class="spai-card">
			<h2><?php esc_html_e( 'General Settings', 'site-pilot-ai' ); ?></h2>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'spai_settings_group' );
				do_settings_sections( 'spai_settings' );
				submit_button();
				?>
			</form>
		</div>

		<div class="spai-card">
			<h2><?php esc_html_e( 'Rate Limiting', 'site-pilot-ai' ); ?></h2>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'spai_rate_limit_group' );
				do_settings_sections( 'spai_rate_limit_settings' );
				submit_button( __( 'Save Rate Limits', 'site-pilot-ai' ) );
				?>
			</form>
		</div>

		<div class="spai-card">
			<h2><?php esc_html_e( 'AI Site Context', 'site-pilot-ai' ); ?></h2>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'spai_site_context_group' );
				do_settings_sections( 'spai_site_context_settings' );
				submit_button( __( 'Save Site Context', 'site-pilot-ai' ) );
				?>
			</form>
		</div>

		<div class="spai-card">
			<h2><?php esc_html_e( 'Guided Site Character', 'site-pilot-ai' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Use this builder if you want a structured way to define the site character. Saving this form regenerates the canonical site context used by MCP and published in /llms.txt.', 'site-pilot-ai' ); ?>
			</p>

			<form method="post" class="spai-site-profile-form">
				<?php wp_nonce_field( 'spai_site_profile_actions', 'spai_site_profile_nonce' ); ?>
				<div class="spai-site-profile-grid">
					<p>
						<label for="spai_site_profile_brand_name"><strong><?php esc_html_e( 'Brand Name', 'site-pilot-ai' ); ?></strong></label><br />
						<input type="text" id="spai_site_profile_brand_name" name="spai_site_profile[brand_name]" class="regular-text" value="<?php echo esc_attr( isset( $site_profile['brand_name'] ) ? $site_profile['brand_name'] : '' ); ?>" />
					</p>
					<p>
						<label for="spai_site_profile_target_audience"><strong><?php esc_html_e( 'Target Audience', 'site-pilot-ai' ); ?></strong></label><br />
						<textarea id="spai_site_profile_target_audience" name="spai_site_profile[target_audience]" rows="4" class="large-text"><?php echo esc_textarea( isset( $site_profile['target_audience'] ) ? $site_profile['target_audience'] : '' ); ?></textarea>
					</p>
					<p class="spai-site-profile-grid__full">
						<label for="spai_site_profile_brand_summary"><strong><?php esc_html_e( 'Brand Summary', 'site-pilot-ai' ); ?></strong></label><br />
						<textarea id="spai_site_profile_brand_summary" name="spai_site_profile[brand_summary]" rows="4" class="large-text"><?php echo esc_textarea( isset( $site_profile['brand_summary'] ) ? $site_profile['brand_summary'] : '' ); ?></textarea>
					</p>
					<p>
						<label for="spai_site_profile_brand_voice"><strong><?php esc_html_e( 'Brand Voice', 'site-pilot-ai' ); ?></strong></label><br />
						<textarea id="spai_site_profile_brand_voice" name="spai_site_profile[brand_voice]" rows="5" class="large-text"><?php echo esc_textarea( isset( $site_profile['brand_voice'] ) ? $site_profile['brand_voice'] : '' ); ?></textarea>
					</p>
					<p>
						<label for="spai_site_profile_visual_style"><strong><?php esc_html_e( 'Visual Style', 'site-pilot-ai' ); ?></strong></label><br />
						<textarea id="spai_site_profile_visual_style" name="spai_site_profile[visual_style]" rows="5" class="large-text"><?php echo esc_textarea( isset( $site_profile['visual_style'] ) ? $site_profile['visual_style'] : '' ); ?></textarea>
					</p>
					<p>
						<label for="spai_site_profile_primary_colors"><strong><?php esc_html_e( 'Colors', 'site-pilot-ai' ); ?></strong></label><br />
						<textarea id="spai_site_profile_primary_colors" name="spai_site_profile[primary_colors]" rows="5" class="large-text"><?php echo esc_textarea( isset( $site_profile['primary_colors'] ) ? $site_profile['primary_colors'] : '' ); ?></textarea>
					</p>
					<p>
						<label for="spai_site_profile_typography"><strong><?php esc_html_e( 'Typography', 'site-pilot-ai' ); ?></strong></label><br />
						<textarea id="spai_site_profile_typography" name="spai_site_profile[typography]" rows="5" class="large-text"><?php echo esc_textarea( isset( $site_profile['typography'] ) ? $site_profile['typography'] : '' ); ?></textarea>
					</p>
					<p>
						<label for="spai_site_profile_header_rules"><strong><?php esc_html_e( 'Header Rules', 'site-pilot-ai' ); ?></strong></label><br />
						<textarea id="spai_site_profile_header_rules" name="spai_site_profile[header_rules]" rows="5" class="large-text"><?php echo esc_textarea( isset( $site_profile['header_rules'] ) ? $site_profile['header_rules'] : '' ); ?></textarea>
					</p>
					<p>
						<label for="spai_site_profile_footer_rules"><strong><?php esc_html_e( 'Footer Rules', 'site-pilot-ai' ); ?></strong></label><br />
						<textarea id="spai_site_profile_footer_rules" name="spai_site_profile[footer_rules]" rows="5" class="large-text"><?php echo esc_textarea( isset( $site_profile['footer_rules'] ) ? $site_profile['footer_rules'] : '' ); ?></textarea>
					</p>
					<p>
						<label for="spai_site_profile_core_page_patterns"><strong><?php esc_html_e( 'Core Page Patterns', 'site-pilot-ai' ); ?></strong></label><br />
						<textarea id="spai_site_profile_core_page_patterns" name="spai_site_profile[core_page_patterns]" rows="6" class="large-text"><?php echo esc_textarea( isset( $site_profile['core_page_patterns'] ) ? $site_profile['core_page_patterns'] : '' ); ?></textarea>
					</p>
					<p>
						<label for="spai_site_profile_reusable_sections"><strong><?php esc_html_e( 'Reusable Sections', 'site-pilot-ai' ); ?></strong></label><br />
						<textarea id="spai_site_profile_reusable_sections" name="spai_site_profile[reusable_sections]" rows="6" class="large-text"><?php echo esc_textarea( isset( $site_profile['reusable_sections'] ) ? $site_profile['reusable_sections'] : '' ); ?></textarea>
					</p>
					<p>
						<label for="spai_site_profile_conversion_goals"><strong><?php esc_html_e( 'Conversion Goals', 'site-pilot-ai' ); ?></strong></label><br />
						<textarea id="spai_site_profile_conversion_goals" name="spai_site_profile[conversion_goals]" rows="5" class="large-text"><?php echo esc_textarea( isset( $site_profile['conversion_goals'] ) ? $site_profile['conversion_goals'] : '' ); ?></textarea>
					</p>
					<p>
						<label for="spai_site_profile_claims_to_avoid"><strong><?php esc_html_e( 'Claims To Avoid', 'site-pilot-ai' ); ?></strong></label><br />
						<textarea id="spai_site_profile_claims_to_avoid" name="spai_site_profile[claims_to_avoid]" rows="5" class="large-text"><?php echo esc_textarea( isset( $site_profile['claims_to_avoid'] ) ? $site_profile['claims_to_avoid'] : '' ); ?></textarea>
					</p>
					<p class="spai-site-profile-grid__full">
						<label for="spai_site_profile_ai_instructions"><strong><?php esc_html_e( 'Instructions For AI Systems', 'site-pilot-ai' ); ?></strong></label><br />
						<textarea id="spai_site_profile_ai_instructions" name="spai_site_profile[ai_instructions]" rows="6" class="large-text"><?php echo esc_textarea( isset( $site_profile['ai_instructions'] ) ? $site_profile['ai_instructions'] : '' ); ?></textarea>
					</p>
				</div>
				<p>
					<button type="submit" name="spai_save_site_profile" class="button button-primary"><?php esc_html_e( 'Save Guided Character', 'site-pilot-ai' ); ?></button>
				</p>
			</form>
		</div>

		<div class="spai-card">
			<h2><?php esc_html_e( 'Live AI Preview', 'site-pilot-ai' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'This is what your connected AI tools and public AI crawlers will see. The site context feeds MCP. The llms.txt preview is the public-facing summary exposed on your site.', 'site-pilot-ai' ); ?>
			</p>

			<div class="spai-ai-preview-grid">
				<div class="spai-ai-preview-panel">
					<h3><?php esc_html_e( 'Canonical Site Context', 'site-pilot-ai' ); ?></h3>
					<?php if ( '' !== trim( $site_context_preview ) ) : ?>
						<div class="spai-code-wrapper">
							<pre class="spai-code-block" id="spai-site-context-preview"><?php echo esc_html( $site_context_preview ); ?></pre>
							<button type="button" class="button spai-copy-code-btn" data-target="spai-site-context-preview">
								<span class="dashicons dashicons-clipboard"></span>
								<?php esc_html_e( 'Copy', 'site-pilot-ai' ); ?>
							</button>
						</div>
					<?php else : ?>
						<p><em><?php esc_html_e( 'No site context generated yet.', 'site-pilot-ai' ); ?></em></p>
					<?php endif; ?>
				</div>

				<div class="spai-ai-preview-panel">
					<h3><?php esc_html_e( 'Public llms.txt', 'site-pilot-ai' ); ?></h3>
					<p class="description">
						<strong><?php esc_html_e( 'URL:', 'site-pilot-ai' ); ?></strong>
						<a href="<?php echo esc_url( $llms_url ); ?>" target="_blank"><?php echo esc_html( $llms_url ); ?></a>
					</p>
					<?php if ( '' !== trim( $llms_preview ) ) : ?>
						<div class="spai-code-wrapper">
							<pre class="spai-code-block" id="spai-llms-preview"><?php echo esc_html( $llms_preview ); ?></pre>
							<button type="button" class="button spai-copy-code-btn" data-target="spai-llms-preview">
								<span class="dashicons dashicons-clipboard"></span>
								<?php esc_html_e( 'Copy', 'site-pilot-ai' ); ?>
							</button>
						</div>
					<?php else : ?>
						<p><em><?php esc_html_e( 'llms.txt preview is not available.', 'site-pilot-ai' ); ?></em></p>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php
		/**
		 * Action for Pro add-on to render additional settings tabs.
		 */
		do_action( 'spai_admin_settings_cards' );
		?>
	</div>

	<!-- ======================== LIBRARY TAB ======================== -->
	<?php elseif ( 'library' === $current_tab ) : ?>

	<div class="spai-tab-content">
		<?php
		$unused_reference_count = count(
			array_filter(
				$library_inventory['design_references'],
				static function ( $item ) {
					return empty( $item['page_count'] ) && empty( $item['linked_part_count'] ) && empty( $item['linked_archetype_count'] );
				}
			)
		);
		$unlinked_part_count = count(
			array_filter(
				$library_inventory['parts'],
				static function ( $item ) {
					return empty( $item['reference_count'] );
				}
			)
		);
		$unlinked_archetype_count = count(
			array_filter(
				$library_inventory['page_archetypes'],
				static function ( $item ) {
					return empty( $item['reference_count'] );
				}
			)
		);
		?>
		<div class="spai-card">
			<h2>
				<span class="dashicons dashicons-screenoptions"></span>
				<?php esc_html_e( 'Structured Design Library', 'site-pilot-ai' ); ?>
			</h2>
			<p class="description">
				<?php esc_html_e( 'This is the reusable system your models should build against. SPAI stores reusable page structures and sections as Elementor templates, then adds archetype and part metadata on top so they stay editable in Elementor and reusable in SPAI.', 'site-pilot-ai' ); ?>
			</p>

			<div class="spai-library-summary">
				<div class="spai-library-stat">
					<span class="spai-library-stat__value"><?php echo esc_html( count( $library_inventory['page_archetypes'] ) ); ?></span>
					<span class="spai-library-stat__label"><?php esc_html_e( 'Page Archetypes', 'site-pilot-ai' ); ?></span>
				</div>
				<div class="spai-library-stat">
					<span class="spai-library-stat__value"><?php echo esc_html( count( $library_inventory['product_archetypes'] ) ); ?></span>
					<span class="spai-library-stat__label"><?php esc_html_e( 'Product Archetypes', 'site-pilot-ai' ); ?></span>
				</div>
				<div class="spai-library-stat">
					<span class="spai-library-stat__value"><?php echo esc_html( count( $library_inventory['parts'] ) ); ?></span>
					<span class="spai-library-stat__label"><?php esc_html_e( 'Reusable Parts', 'site-pilot-ai' ); ?></span>
				</div>
				<div class="spai-library-stat">
					<span class="spai-library-stat__value"><?php echo esc_html( count( $library_inventory['design_references'] ) ); ?></span>
					<span class="spai-library-stat__label"><?php esc_html_e( 'Design References', 'site-pilot-ai' ); ?></span>
				</div>
			</div>
		</div>

		<div class="spai-card">
			<h2><?php esc_html_e( 'Library Health', 'site-pilot-ai' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Use this to spot references that have not produced assets yet, and library items that are not connected back to a source design.', 'site-pilot-ai' ); ?>
			</p>
			<div class="spai-library-summary">
				<div class="spai-library-stat spai-library-stat--warning">
					<span class="spai-library-stat__value"><?php echo esc_html( $unused_reference_count ); ?></span>
					<span class="spai-library-stat__label"><?php esc_html_e( 'Unused References', 'site-pilot-ai' ); ?></span>
				</div>
				<div class="spai-library-stat">
					<span class="spai-library-stat__value"><?php echo esc_html( $unlinked_part_count ); ?></span>
					<span class="spai-library-stat__label"><?php esc_html_e( 'Parts Without Reference Links', 'site-pilot-ai' ); ?></span>
				</div>
				<div class="spai-library-stat">
					<span class="spai-library-stat__value"><?php echo esc_html( $unlinked_archetype_count ); ?></span>
					<span class="spai-library-stat__label"><?php esc_html_e( 'Archetypes Without Reference Links', 'site-pilot-ai' ); ?></span>
				</div>
			</div>
		</div>

		<div class="spai-card">
			<h2><?php esc_html_e( 'Find Assets', 'site-pilot-ai' ); ?></h2>
			<form method="get" class="spai-library-filters">
				<input type="hidden" name="page" value="site-pilot-ai" />
				<input type="hidden" name="tab" value="library" />
				<div class="spai-library-filters__grid">
					<p>
						<label for="library_search"><strong><?php esc_html_e( 'Search', 'site-pilot-ai' ); ?></strong></label><br />
						<input type="text" id="library_search" name="library_search" class="regular-text" value="<?php echo esc_attr( $library_filters['search'] ); ?>" placeholder="<?php esc_attr_e( 'hero, blog_post, premium, homepage', 'site-pilot-ai' ); ?>" />
					</p>
					<p>
						<label for="library_asset_type"><strong><?php esc_html_e( 'Asset Type', 'site-pilot-ai' ); ?></strong></label><br />
						<select id="library_asset_type" name="library_asset_type">
							<option value="all" <?php selected( 'all', $library_filters['asset_type'] ); ?>><?php esc_html_e( 'All', 'site-pilot-ai' ); ?></option>
							<option value="archetypes" <?php selected( 'archetypes', $library_filters['asset_type'] ); ?>><?php esc_html_e( 'Page Archetypes', 'site-pilot-ai' ); ?></option>
							<option value="products" <?php selected( 'products', $library_filters['asset_type'] ); ?>><?php esc_html_e( 'Product Archetypes', 'site-pilot-ai' ); ?></option>
							<option value="parts" <?php selected( 'parts', $library_filters['asset_type'] ); ?>><?php esc_html_e( 'Reusable Parts', 'site-pilot-ai' ); ?></option>
							<option value="references" <?php selected( 'references', $library_filters['asset_type'] ); ?>><?php esc_html_e( 'Design References', 'site-pilot-ai' ); ?></option>
						</select>
					</p>
					<p>
						<label for="library_class"><strong><?php esc_html_e( 'Class / Kind', 'site-pilot-ai' ); ?></strong></label><br />
						<select id="library_class" name="library_class">
							<option value=""><?php esc_html_e( 'All', 'site-pilot-ai' ); ?></option>
							<?php foreach ( $library_filter_options['classes'] as $class_option ) : ?>
								<option value="<?php echo esc_attr( $class_option ); ?>" <?php selected( $class_option, $library_filters['class'] ); ?>><?php echo esc_html( $class_option ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<p>
						<label for="library_style"><strong><?php esc_html_e( 'Style', 'site-pilot-ai' ); ?></strong></label><br />
						<select id="library_style" name="library_style">
							<option value=""><?php esc_html_e( 'All', 'site-pilot-ai' ); ?></option>
							<?php foreach ( $library_filter_options['styles'] as $style_option ) : ?>
								<option value="<?php echo esc_attr( $style_option ); ?>" <?php selected( $style_option, $library_filters['style'] ); ?>><?php echo esc_html( $style_option ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
				</div>
				<p class="spai-library-filters__actions">
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Apply Filters', 'site-pilot-ai' ); ?></button>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=site-pilot-ai&tab=library' ) ); ?>"><?php esc_html_e( 'Reset', 'site-pilot-ai' ); ?></a>
				</p>
			</form>
		</div>

		<div class="spai-card">
			<h2><?php esc_html_e( 'Operating Sequence', 'site-pilot-ai' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'This is the operator loop Site Pilot AI is designed around. New models and humans should follow this path instead of building pages from scratch every time.', 'site-pilot-ai' ); ?>
			</p>
			<div class="spai-workflow-sequence">
				<div class="spai-workflow-step">
					<span class="spai-workflow-step__number">1</span>
					<strong><?php esc_html_e( 'Define Character', 'site-pilot-ai' ); ?></strong>
					<div class="spai-design-reference__meta"><?php esc_html_e( 'Set the site voice, audience, and structure rules first.', 'site-pilot-ai' ); ?></div>
				</div>
				<div class="spai-workflow-step">
					<span class="spai-workflow-step__number">2</span>
					<strong><?php esc_html_e( 'Store References', 'site-pilot-ai' ); ?></strong>
					<div class="spai-design-reference__meta"><?php esc_html_e( 'Turn screenshots, mockups, and approved designs into reusable references.', 'site-pilot-ai' ); ?></div>
				</div>
				<div class="spai-workflow-step">
					<span class="spai-workflow-step__number">3</span>
					<strong><?php esc_html_e( 'Reuse Archetypes', 'site-pilot-ai' ); ?></strong>
					<div class="spai-design-reference__meta"><?php esc_html_e( 'Start from saved page or product structures before inventing anything new.', 'site-pilot-ai' ); ?></div>
				</div>
				<div class="spai-workflow-step">
					<span class="spai-workflow-step__number">4</span>
					<strong><?php esc_html_e( 'Build Drafts', 'site-pilot-ai' ); ?></strong>
					<div class="spai-design-reference__meta"><?php esc_html_e( 'Create draft pages and products, then review instead of publishing blindly.', 'site-pilot-ai' ); ?></div>
				</div>
				<div class="spai-workflow-step">
					<span class="spai-workflow-step__number">5</span>
					<strong><?php esc_html_e( 'Save Reusable Parts', 'site-pilot-ai' ); ?></strong>
					<div class="spai-design-reference__meta"><?php esc_html_e( 'Good sections should compound into the library for the next build.', 'site-pilot-ai' ); ?></div>
				</div>
			</div>
		</div>

		<div class="spai-card">
			<h2><?php esc_html_e( 'Design References', 'site-pilot-ai' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Store screenshots, mockups, and design inspiration here before turning them into archetypes or reusable Elementor parts. This gives models a visual source of truth to work from.', 'site-pilot-ai' ); ?>
			</p>
			<?php if ( empty( $library_inventory['design_references'] ) ) : ?>
				<p><em><?php esc_html_e( 'No design references saved yet.', 'site-pilot-ai' ); ?></em></p>
			<?php else : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Reference', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'ID', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'Intent / Class', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'Style', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'Source', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'Tags / Reuse Notes', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'Updated', 'site-pilot-ai' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $library_inventory['design_references'] as $item ) : ?>
						<tr>
							<td>
								<div class="spai-design-reference">
									<?php if ( ! empty( $item['image_url'] ) ) : ?>
										<img class="spai-design-reference__thumb" src="<?php echo esc_url( $item['image_url'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" />
									<?php endif; ?>
									<div class="spai-design-reference__body">
										<strong><?php echo esc_html( $item['title'] ? $item['title'] : __( 'Untitled Reference', 'site-pilot-ai' ) ); ?></strong>
										<?php if ( ! empty( $item['analysis_summary'] ) ) : ?>
											<div class="spai-design-reference__meta"><?php echo esc_html( $item['analysis_summary'] ); ?></div>
										<?php elseif ( ! empty( $item['notes'] ) ) : ?>
											<div class="spai-design-reference__meta"><?php echo esc_html( $item['notes'] ); ?></div>
										<?php endif; ?>
										<div class="spai-design-reference__meta">
											<?php
											echo esc_html(
												sprintf(
													/* translators: %d: page count */
													_n( 'Used on %d page', 'Used on %d pages', (int) $item['page_count'], 'site-pilot-ai' ),
													(int) $item['page_count']
												)
											);
											?>
										</div>
										<?php if ( ! empty( $item['linked_pages'] ) ) : ?>
											<div class="spai-design-reference__meta">
												<strong><?php esc_html_e( 'Pages:', 'site-pilot-ai' ); ?></strong>
												<?php foreach ( $item['linked_pages'] as $page_link ) : ?>
													<a href="<?php echo esc_url( $page_link['url'] ); ?>"><?php echo esc_html( $page_link['title'] ? $page_link['title'] : '#' . $page_link['id'] ); ?></a><?php echo end( $item['linked_pages'] ) === $page_link ? '' : ', '; ?>
												<?php endforeach; ?>
											</div>
										<?php endif; ?>
										<div class="spai-row-actions spai-row-actions--stack">
											<form method="post" class="spai-inline-action spai-inline-action--grid">
												<?php wp_nonce_field( 'spai_library_actions', 'spai_library_nonce' ); ?>
												<input type="hidden" name="spai_action_design_reference_id" value="<?php echo esc_attr( $item['id'] ); ?>" />
												<input type="text" name="spai_design_reference_page_title" class="regular-text" placeholder="<?php esc_attr_e( 'Draft page title', 'site-pilot-ai' ); ?>" />
												<button type="submit" name="spai_create_page_from_design_reference" class="button button-small"><?php esc_html_e( 'Create Draft Page', 'site-pilot-ai' ); ?></button>
											</form>
										</div>
									</div>
								</div>
							</td>
							<td><code><?php echo esc_html( $item['id'] ); ?></code></td>
							<td>
								<div><code><?php echo esc_html( $item['page_intent'] ? $item['page_intent'] : 'general' ); ?></code></div>
								<?php if ( ! empty( $item['archetype_class'] ) ) : ?>
									<div class="spai-design-reference__meta"><?php echo esc_html( $item['archetype_class'] ); ?></div>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( $item['style'] ? $item['style'] : 'default' ); ?></td>
							<td>
								<span class="spai-origin-badge"><?php echo esc_html( $item['source_type'] ? $item['source_type'] : 'manual' ); ?></span>
								<?php if ( ! empty( $item['media_id'] ) ) : ?>
									<div class="spai-design-reference__meta"><?php echo esc_html( 'Media #' . $item['media_id'] ); ?></div>
								<?php endif; ?>
							</td>
							<td>
								<?php if ( ! empty( $item['tags'] ) ) : ?>
									<div class="spai-tag-list">
										<?php foreach ( $item['tags'] as $tag ) : ?>
											<span class="spai-tag"><?php echo esc_html( $tag ); ?></span>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
								<?php if ( ! empty( $item['must_keep'] ) ) : ?>
									<div class="spai-design-reference__meta"><strong><?php esc_html_e( 'Keep:', 'site-pilot-ai' ); ?></strong> <?php echo esc_html( implode( ', ', $item['must_keep'] ) ); ?></div>
								<?php endif; ?>
								<?php if ( ! empty( $item['avoid'] ) ) : ?>
									<div class="spai-design-reference__meta"><strong><?php esc_html_e( 'Avoid:', 'site-pilot-ai' ); ?></strong> <?php echo esc_html( implode( ', ', $item['avoid'] ) ); ?></div>
								<?php endif; ?>
								<?php if ( ! empty( $item['linked_part_count'] ) || ! empty( $item['linked_archetype_count'] ) ) : ?>
									<div class="spai-design-reference__meta">
										<strong><?php esc_html_e( 'Linked:', 'site-pilot-ai' ); ?></strong>
										<?php
										echo esc_html(
											sprintf(
												/* translators: 1: part count 2: archetype count */
												__( '%1$d parts, %2$d archetypes', 'site-pilot-ai' ),
												(int) $item['linked_part_count'],
												(int) $item['linked_archetype_count']
											)
										);
										?>
									</div>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( $item['updated_at'] ? $item['updated_at'] : '' ); ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<div class="spai-card">
			<h2><?php esc_html_e( 'Add Design Reference', 'site-pilot-ai' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Upload a screenshot, paste a design URL, or point at existing media. Save the intent and reuse rules now so future models can turn it into archetypes and reusable parts.', 'site-pilot-ai' ); ?>
			</p>

			<form method="post" enctype="multipart/form-data" class="spai-library-form">
				<?php wp_nonce_field( 'spai_library_actions', 'spai_library_nonce' ); ?>
				<p>
					<label for="spai_design_reference_title"><strong><?php esc_html_e( 'Title', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="text" id="spai_design_reference_title" name="spai_design_reference_title" class="regular-text" placeholder="<?php esc_attr_e( 'Homepage Hero Inspiration / SaaS', 'site-pilot-ai' ); ?>" required />
				</p>
				<p>
					<label for="spai_design_reference_file"><strong><?php esc_html_e( 'Upload Image', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="file" id="spai_design_reference_file" name="spai_design_reference_file" accept="image/*" />
				</p>
				<p>
					<label for="spai_design_reference_url"><strong><?php esc_html_e( 'Image URL', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="url" id="spai_design_reference_url" name="spai_design_reference_url" class="large-text" placeholder="<?php esc_attr_e( 'https://example.com/reference.png', 'site-pilot-ai' ); ?>" />
				</p>
				<p>
					<label for="spai_design_reference_media_id"><strong><?php esc_html_e( 'Existing Media ID', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="number" min="1" id="spai_design_reference_media_id" name="spai_design_reference_media_id" class="small-text" />
				</p>
				<p>
					<label for="spai_design_reference_intent"><strong><?php esc_html_e( 'Page Intent', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="text" id="spai_design_reference_intent" name="spai_design_reference_intent" class="regular-text" placeholder="<?php esc_attr_e( 'landing_page, blog_post, product_page', 'site-pilot-ai' ); ?>" />
				</p>
				<p>
					<label for="spai_design_reference_class"><strong><?php esc_html_e( 'Archetype Class', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="text" id="spai_design_reference_class" name="spai_design_reference_class" class="regular-text" placeholder="<?php esc_attr_e( 'saas_landing, editorial_blog, digital_product', 'site-pilot-ai' ); ?>" />
				</p>
				<p>
					<label for="spai_design_reference_style"><strong><?php esc_html_e( 'Style', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="text" id="spai_design_reference_style" name="spai_design_reference_style" class="regular-text" placeholder="<?php esc_attr_e( 'showcase, editorial, premium', 'site-pilot-ai' ); ?>" />
				</p>
				<p>
					<label for="spai_design_reference_tags"><strong><?php esc_html_e( 'Tags', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="text" id="spai_design_reference_tags" name="spai_design_reference_tags" class="regular-text" placeholder="<?php esc_attr_e( 'hero, pricing, b2b', 'site-pilot-ai' ); ?>" />
				</p>
				<p>
					<label for="spai_design_reference_notes"><strong><?php esc_html_e( 'Notes', 'site-pilot-ai' ); ?></strong></label><br />
					<textarea id="spai_design_reference_notes" name="spai_design_reference_notes" rows="4" class="large-text" placeholder="<?php esc_attr_e( 'Why this design matters and where it should be used.', 'site-pilot-ai' ); ?>"></textarea>
				</p>
				<p>
					<label for="spai_design_reference_summary"><strong><?php esc_html_e( 'Analysis Summary', 'site-pilot-ai' ); ?></strong></label><br />
					<textarea id="spai_design_reference_summary" name="spai_design_reference_summary" rows="4" class="large-text" placeholder="<?php esc_attr_e( 'Short structural summary of the design.', 'site-pilot-ai' ); ?>"></textarea>
				</p>
				<p>
					<label for="spai_design_reference_must_keep"><strong><?php esc_html_e( 'Must Keep', 'site-pilot-ai' ); ?></strong></label><br />
					<textarea id="spai_design_reference_must_keep" name="spai_design_reference_must_keep" rows="4" class="large-text" placeholder="<?php esc_attr_e( "One item per line:\nstrong headline\nleft-aligned proof strip", 'site-pilot-ai' ); ?>"></textarea>
				</p>
				<p>
					<label for="spai_design_reference_avoid"><strong><?php esc_html_e( 'Avoid', 'site-pilot-ai' ); ?></strong></label><br />
					<textarea id="spai_design_reference_avoid" name="spai_design_reference_avoid" rows="4" class="large-text" placeholder="<?php esc_attr_e( "One item per line:\ncarousel\ndense paragraph blocks", 'site-pilot-ai' ); ?>"></textarea>
				</p>
				<p>
					<label for="spai_design_reference_outline"><strong><?php esc_html_e( 'Section Outline', 'site-pilot-ai' ); ?></strong></label><br />
					<textarea id="spai_design_reference_outline" name="spai_design_reference_outline" rows="5" class="large-text" placeholder="<?php esc_attr_e( "One section per line:\nhero\nfeature grid\ntestimonials\ncta", 'site-pilot-ai' ); ?>"></textarea>
				</p>
				<p>
					<button type="submit" name="spai_create_design_reference" class="button button-primary"><?php esc_html_e( 'Save Design Reference', 'site-pilot-ai' ); ?></button>
				</p>
			</form>
		</div>

		<div class="spai-card">
			<h2><?php esc_html_e( 'Page Archetypes', 'site-pilot-ai' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Page archetypes are Elementor templates marked as canonical structures for blog posts, landing pages, service pages, and other repeatable layouts. Models should start from one of these before generating a page from scratch.', 'site-pilot-ai' ); ?>
			</p>
			<?php if ( empty( $library_inventory['page_archetypes'] ) ) : ?>
				<p><em><?php esc_html_e( 'No page archetypes saved yet.', 'site-pilot-ai' ); ?></em></p>
			<?php else : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Template', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'ID', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'Class', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'Style', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'Type', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'Modified', 'site-pilot-ai' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $library_inventory['page_archetypes'] as $item ) : ?>
						<tr>
							<td>
								<strong><?php echo esc_html( $item['title'] ); ?></strong>
								<?php if ( ! empty( $item['edit_url'] ) ) : ?>
									<div class="spai-row-actions"><a href="<?php echo esc_url( $item['edit_url'] ); ?>"><?php esc_html_e( 'Open in Elementor', 'site-pilot-ai' ); ?></a></div>
								<?php endif; ?>
								<div class="spai-design-reference__meta"><span class="spai-origin-badge"><?php echo esc_html( $item['provenance_label'] ); ?></span></div>
								<div class="spai-design-reference__meta">
									<?php
									echo esc_html(
										sprintf(
											/* translators: %d: reference count */
											_n( 'Linked from %d design reference', 'Linked from %d design references', (int) $item['reference_count'], 'site-pilot-ai' ),
											(int) $item['reference_count']
										)
									);
									?>
								</div>
								<?php if ( ! empty( $item['linked_references'] ) ) : ?>
									<div class="spai-design-reference__meta">
										<strong><?php esc_html_e( 'References:', 'site-pilot-ai' ); ?></strong>
										<?php foreach ( $item['linked_references'] as $reference_link ) : ?>
											<a href="<?php echo esc_url( $reference_link['url'] ); ?>"><?php echo esc_html( $reference_link['title'] ? $reference_link['title'] : $reference_link['id'] ); ?></a><?php echo end( $item['linked_references'] ) === $reference_link ? '' : ', '; ?>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
								<div class="spai-row-actions spai-row-actions--stack">
									<form method="post" class="spai-inline-action">
										<?php wp_nonce_field( 'spai_library_actions', 'spai_library_nonce' ); ?>
										<input type="hidden" name="spai_action_archetype_id" value="<?php echo esc_attr( $item['id'] ); ?>" />
										<button type="submit" name="spai_create_page_from_archetype" class="button button-small"><?php esc_html_e( 'Create Draft Page', 'site-pilot-ai' ); ?></button>
									</form>
									<form method="post" class="spai-inline-action">
										<?php wp_nonce_field( 'spai_library_actions', 'spai_library_nonce' ); ?>
										<input type="hidden" name="spai_action_archetype_id" value="<?php echo esc_attr( $item['id'] ); ?>" />
										<button type="submit" name="spai_demote_archetype" class="button button-small"><?php esc_html_e( 'Remove Archetype Tag', 'site-pilot-ai' ); ?></button>
									</form>
								</div>
							</td>
							<td><code><?php echo esc_html( $item['id'] ); ?></code></td>
							<td><code><?php echo esc_html( $item['archetype_class'] ? $item['archetype_class'] : 'default' ); ?></code></td>
							<td><?php echo esc_html( $item['archetype_style'] ? $item['archetype_style'] : 'default' ); ?></td>
							<td><?php echo esc_html( $item['type'] ? $item['type'] : 'page' ); ?></td>
							<td><?php echo esc_html( $item['modified'] ? $item['modified'] : ''); ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<div class="spai-card">
			<h2><?php esc_html_e( 'Product Archetypes', 'site-pilot-ai' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Use product archetypes to standardize WooCommerce product pages and field structure. This is where simple products, variable products, and other catalog patterns should live.', 'site-pilot-ai' ); ?>
			</p>
			<?php if ( empty( $library_inventory['product_archetypes'] ) ) : ?>
				<p><em><?php esc_html_e( 'No product archetypes saved yet.', 'site-pilot-ai' ); ?></em></p>
			<?php else : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Name', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'ID', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'Class', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'Style', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'Product Type', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'Status Default', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'Updated', 'site-pilot-ai' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $library_inventory['product_archetypes'] as $item ) : ?>
						<tr>
							<td>
								<strong><?php echo esc_html( $item['name'] ); ?></strong>
								<div class="spai-row-actions spai-row-actions--stack">
									<form method="post" class="spai-inline-action spai-inline-action--grid">
										<?php wp_nonce_field( 'spai_library_actions', 'spai_library_nonce' ); ?>
										<input type="hidden" name="spai_action_product_archetype_id" value="<?php echo esc_attr( $item['id'] ); ?>" />
										<input type="text" name="spai_product_name" class="regular-text" placeholder="<?php esc_attr_e( 'Draft product name', 'site-pilot-ai' ); ?>" />
										<button type="submit" name="spai_create_product_from_archetype" class="button button-small"><?php esc_html_e( 'Create Draft Product', 'site-pilot-ai' ); ?></button>
									</form>
									<form method="post" class="spai-inline-action">
										<?php wp_nonce_field( 'spai_library_actions', 'spai_library_nonce' ); ?>
										<input type="hidden" name="spai_action_product_archetype_id" value="<?php echo esc_attr( $item['id'] ); ?>" />
										<button type="submit" name="spai_delete_product_archetype" class="button button-small"><?php esc_html_e( 'Remove Archetype', 'site-pilot-ai' ); ?></button>
									</form>
								</div>
							</td>
							<td><code><?php echo esc_html( $item['id'] ); ?></code></td>
							<td><code><?php echo esc_html( $item['archetype_class'] ? $item['archetype_class'] : 'default' ); ?></code></td>
							<td><?php echo esc_html( $item['archetype_style'] ? $item['archetype_style'] : 'default' ); ?></td>
							<td><?php echo esc_html( $item['product_type'] ? $item['product_type'] : 'simple' ); ?></td>
							<td><?php echo esc_html( $item['status'] ? $item['status'] : 'draft' ); ?></td>
							<td><?php echo esc_html( $item['updated_at'] ? $item['updated_at'] : '' ); ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<div class="spai-card">
			<h2><?php esc_html_e( 'Create Product Archetype', 'site-pilot-ai' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Define a canonical WooCommerce product pattern once, then let models and humans generate consistent draft products from it.', 'site-pilot-ai' ); ?>
			</p>

			<form method="post" class="spai-library-form">
				<?php wp_nonce_field( 'spai_library_actions', 'spai_library_nonce' ); ?>
				<p>
					<label for="spai_product_archetype_name"><strong><?php esc_html_e( 'Archetype Name', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="text" id="spai_product_archetype_name" name="spai_product_archetype_name" class="regular-text" placeholder="<?php esc_attr_e( 'Digital Course / Premium / Default', 'site-pilot-ai' ); ?>" required />
				</p>
				<p>
					<label for="spai_product_archetype_class"><strong><?php esc_html_e( 'Archetype Class', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="text" id="spai_product_archetype_class" name="spai_product_archetype_class" class="regular-text" placeholder="<?php esc_attr_e( 'simple_product, digital_product, variable_product', 'site-pilot-ai' ); ?>" />
				</p>
				<p>
					<label for="spai_product_archetype_style"><strong><?php esc_html_e( 'Style', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="text" id="spai_product_archetype_style" name="spai_product_archetype_style" class="regular-text" placeholder="<?php esc_attr_e( 'premium, minimal, editorial', 'site-pilot-ai' ); ?>" />
				</p>
				<p>
					<label for="spai_product_type"><strong><?php esc_html_e( 'Product Type', 'site-pilot-ai' ); ?></strong></label><br />
					<select id="spai_product_type" name="spai_product_type">
						<option value="simple"><?php esc_html_e( 'Simple', 'site-pilot-ai' ); ?></option>
						<option value="variable"><?php esc_html_e( 'Variable', 'site-pilot-ai' ); ?></option>
						<option value="grouped"><?php esc_html_e( 'Grouped', 'site-pilot-ai' ); ?></option>
						<option value="external"><?php esc_html_e( 'External', 'site-pilot-ai' ); ?></option>
					</select>
				</p>
				<p>
					<label for="spai_product_status"><strong><?php esc_html_e( 'Default Status', 'site-pilot-ai' ); ?></strong></label><br />
					<select id="spai_product_status" name="spai_product_status">
						<option value="draft"><?php esc_html_e( 'Draft', 'site-pilot-ai' ); ?></option>
						<option value="publish"><?php esc_html_e( 'Publish', 'site-pilot-ai' ); ?></option>
						<option value="pending"><?php esc_html_e( 'Pending', 'site-pilot-ai' ); ?></option>
						<option value="private"><?php esc_html_e( 'Private', 'site-pilot-ai' ); ?></option>
					</select>
				</p>
				<p>
					<label for="spai_product_regular_price"><strong><?php esc_html_e( 'Regular Price', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="text" id="spai_product_regular_price" name="spai_product_regular_price" class="regular-text" placeholder="<?php esc_attr_e( '99.00', 'site-pilot-ai' ); ?>" />
				</p>
				<p>
					<label for="spai_product_sale_price"><strong><?php esc_html_e( 'Sale Price', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="text" id="spai_product_sale_price" name="spai_product_sale_price" class="regular-text" placeholder="<?php esc_attr_e( '79.00', 'site-pilot-ai' ); ?>" />
				</p>
				<p>
					<label for="spai_product_stock_status"><strong><?php esc_html_e( 'Stock Status', 'site-pilot-ai' ); ?></strong></label><br />
					<select id="spai_product_stock_status" name="spai_product_stock_status">
						<option value="instock"><?php esc_html_e( 'In stock', 'site-pilot-ai' ); ?></option>
						<option value="outofstock"><?php esc_html_e( 'Out of stock', 'site-pilot-ai' ); ?></option>
						<option value="onbackorder"><?php esc_html_e( 'On backorder', 'site-pilot-ai' ); ?></option>
					</select>
				</p>
				<p>
					<label><input type="checkbox" name="spai_product_virtual" value="1" /> <?php esc_html_e( 'Virtual product', 'site-pilot-ai' ); ?></label>
					<label style="margin-left:12px;"><input type="checkbox" name="spai_product_downloadable" value="1" /> <?php esc_html_e( 'Downloadable product', 'site-pilot-ai' ); ?></label>
				</p>
				<p>
					<label for="spai_product_categories"><strong><?php esc_html_e( 'Default Categories', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="text" id="spai_product_categories" name="spai_product_categories" class="regular-text" placeholder="<?php esc_attr_e( 'Courses, Membership', 'site-pilot-ai' ); ?>" />
				</p>
				<p>
					<label for="spai_product_tags"><strong><?php esc_html_e( 'Default Tags', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="text" id="spai_product_tags" name="spai_product_tags" class="regular-text" placeholder="<?php esc_attr_e( 'featured, evergreen', 'site-pilot-ai' ); ?>" />
				</p>
				<p>
					<label for="spai_product_short_description"><strong><?php esc_html_e( 'Short Description', 'site-pilot-ai' ); ?></strong></label><br />
					<textarea id="spai_product_short_description" name="spai_product_short_description" rows="4" class="large-text" placeholder="<?php esc_attr_e( 'Concise merchandising copy for the product summary.', 'site-pilot-ai' ); ?>"></textarea>
				</p>
				<p>
					<label for="spai_product_description"><strong><?php esc_html_e( 'Full Description', 'site-pilot-ai' ); ?></strong></label><br />
					<textarea id="spai_product_description" name="spai_product_description" rows="6" class="large-text" placeholder="<?php esc_attr_e( 'Long-form product description or structure starter.', 'site-pilot-ai' ); ?>"></textarea>
				</p>
				<p>
					<label for="spai_product_archetype_brief"><strong><?php esc_html_e( 'Archetype Override Brief', 'site-pilot-ai' ); ?></strong></label><br />
					<textarea id="spai_product_archetype_brief" name="spai_product_archetype_brief" rows="5" class="large-text" placeholder="<?php esc_attr_e( 'Specific guidance for this product class.', 'site-pilot-ai' ); ?>"></textarea>
				</p>
				<p>
					<button type="submit" name="spai_create_product_archetype" class="button button-primary"><?php esc_html_e( 'Save Product Archetype', 'site-pilot-ai' ); ?></button>
				</p>
			</form>
		</div>

		<div class="spai-card">
			<h2><?php esc_html_e( 'Reusable Elementor Parts', 'site-pilot-ai' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Reusable parts are Elementor templates marked as reusable sections. Every strong hero, feature grid, FAQ block, testimonial strip, and CTA should be saved here so future models can reuse it instead of reinventing it.', 'site-pilot-ai' ); ?>
			</p>
			<?php if ( empty( $library_inventory['parts'] ) ) : ?>
				<p><em><?php esc_html_e( 'No reusable parts saved yet.', 'site-pilot-ai' ); ?></em></p>
			<?php else : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Part', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'ID', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'Kind', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'Style', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'Tags', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'Source', 'site-pilot-ai' ); ?></th>
							<th><?php esc_html_e( 'Modified', 'site-pilot-ai' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $library_inventory['parts'] as $item ) : ?>
						<tr>
							<td>
								<strong><?php echo esc_html( $item['title'] ); ?></strong>
								<?php if ( ! empty( $item['edit_url'] ) ) : ?>
									<div class="spai-row-actions"><a href="<?php echo esc_url( $item['edit_url'] ); ?>"><?php esc_html_e( 'Open in Elementor', 'site-pilot-ai' ); ?></a></div>
								<?php endif; ?>
								<div class="spai-design-reference__meta"><span class="spai-origin-badge"><?php echo esc_html( $item['provenance_label'] ); ?></span></div>
								<div class="spai-design-reference__meta">
									<?php
									echo esc_html(
										sprintf(
											/* translators: %d: reference count */
											_n( 'Linked from %d design reference', 'Linked from %d design references', (int) $item['reference_count'], 'site-pilot-ai' ),
											(int) $item['reference_count']
										)
									);
									?>
								</div>
								<?php if ( ! empty( $item['linked_references'] ) ) : ?>
									<div class="spai-design-reference__meta">
										<strong><?php esc_html_e( 'References:', 'site-pilot-ai' ); ?></strong>
										<?php foreach ( $item['linked_references'] as $reference_link ) : ?>
											<a href="<?php echo esc_url( $reference_link['url'] ); ?>"><?php echo esc_html( $reference_link['title'] ? $reference_link['title'] : $reference_link['id'] ); ?></a><?php echo end( $item['linked_references'] ) === $reference_link ? '' : ', '; ?>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
								<div class="spai-row-actions spai-row-actions--stack">
									<form method="post" class="spai-inline-action spai-inline-action--grid">
										<?php wp_nonce_field( 'spai_library_actions', 'spai_library_nonce' ); ?>
										<input type="hidden" name="spai_action_part_id" value="<?php echo esc_attr( $item['id'] ); ?>" />
										<input type="number" min="1" name="spai_target_page_id" class="small-text" placeholder="<?php esc_attr_e( 'Page ID', 'site-pilot-ai' ); ?>" required />
										<select name="spai_part_apply_mode">
											<option value="insert"><?php esc_html_e( 'Insert', 'site-pilot-ai' ); ?></option>
											<option value="replace"><?php esc_html_e( 'Replace', 'site-pilot-ai' ); ?></option>
										</select>
										<select name="spai_part_apply_position">
											<option value="end"><?php esc_html_e( 'End', 'site-pilot-ai' ); ?></option>
											<option value="start"><?php esc_html_e( 'Start', 'site-pilot-ai' ); ?></option>
										</select>
										<button type="submit" name="spai_apply_part_to_page" class="button button-small"><?php esc_html_e( 'Apply to Page', 'site-pilot-ai' ); ?></button>
									</form>
									<form method="post" class="spai-inline-action">
										<?php wp_nonce_field( 'spai_library_actions', 'spai_library_nonce' ); ?>
										<input type="hidden" name="spai_action_part_id" value="<?php echo esc_attr( $item['id'] ); ?>" />
										<button type="submit" name="spai_demote_part" class="button button-small"><?php esc_html_e( 'Remove Part Tag', 'site-pilot-ai' ); ?></button>
									</form>
								</div>
							</td>
							<td><code><?php echo esc_html( $item['id'] ); ?></code></td>
							<td><code><?php echo esc_html( $item['part_kind'] ? $item['part_kind'] : 'section' ); ?></code></td>
							<td><?php echo esc_html( $item['part_style'] ? $item['part_style'] : 'default' ); ?></td>
							<td>
								<?php if ( ! empty( $item['part_tags'] ) ) : ?>
									<div class="spai-tag-list">
										<?php foreach ( $item['part_tags'] as $tag ) : ?>
											<span class="spai-tag"><?php echo esc_html( $tag ); ?></span>
										<?php endforeach; ?>
									</div>
								<?php else : ?>
									&mdash;
								<?php endif; ?>
							</td>
							<td>
								<?php if ( ! empty( $item['source_page_id'] ) ) : ?>
									<?php echo esc_html( $item['source_page_title'] ? $item['source_page_title'] : '#' . $item['source_page_id'] ); ?>
								<?php else : ?>
									&mdash;
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( $item['modified'] ? $item['modified'] : '' ); ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<div class="spai-card">
			<h2><?php esc_html_e( 'Promote Existing Template', 'site-pilot-ai' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Use this when you already have an Elementor template and want SPAI to classify it as a canonical archetype or reusable part without duplicating the template.', 'site-pilot-ai' ); ?>
			</p>

			<div class="spai-library-actions">
				<form method="post" class="spai-library-form">
					<?php wp_nonce_field( 'spai_library_actions', 'spai_library_nonce' ); ?>
					<h3><?php esc_html_e( 'Promote to Page Archetype', 'site-pilot-ai' ); ?></h3>
					<p>
						<label for="spai_archetype_template_id"><strong><?php esc_html_e( 'Template ID', 'site-pilot-ai' ); ?></strong></label><br />
						<input type="number" min="1" id="spai_archetype_template_id" name="spai_archetype_template_id" class="small-text" required />
					</p>
					<p>
						<label for="spai_archetype_title"><strong><?php esc_html_e( 'Title Override', 'site-pilot-ai' ); ?></strong></label><br />
						<input type="text" id="spai_archetype_title" name="spai_archetype_title" class="regular-text" placeholder="<?php esc_attr_e( 'Optional', 'site-pilot-ai' ); ?>" />
					</p>
					<p>
						<label for="spai_archetype_scope"><strong><?php esc_html_e( 'Scope', 'site-pilot-ai' ); ?></strong></label><br />
						<select id="spai_archetype_scope" name="spai_archetype_scope">
							<option value="page"><?php esc_html_e( 'Page', 'site-pilot-ai' ); ?></option>
							<option value="product"><?php esc_html_e( 'Product', 'site-pilot-ai' ); ?></option>
						</select>
					</p>
					<p>
						<label for="spai_archetype_class"><strong><?php esc_html_e( 'Archetype Class', 'site-pilot-ai' ); ?></strong></label><br />
						<input type="text" id="spai_archetype_class" name="spai_archetype_class" class="regular-text" placeholder="<?php esc_attr_e( 'blog_post, landing_page, service_page', 'site-pilot-ai' ); ?>" />
					</p>
					<p>
						<label for="spai_archetype_style"><strong><?php esc_html_e( 'Style', 'site-pilot-ai' ); ?></strong></label><br />
						<input type="text" id="spai_archetype_style" name="spai_archetype_style" class="regular-text" placeholder="<?php esc_attr_e( 'editorial, minimal, bold', 'site-pilot-ai' ); ?>" />
					</p>
					<p>
						<label for="spai_archetype_brief"><strong><?php esc_html_e( 'Archetype Override Brief', 'site-pilot-ai' ); ?></strong></label><br />
						<textarea id="spai_archetype_brief" name="spai_archetype_brief" rows="5" class="large-text" placeholder="<?php esc_attr_e( 'Specific guidance for this page type.', 'site-pilot-ai' ); ?>"></textarea>
					</p>
					<p>
						<button type="submit" name="spai_promote_template_archetype" class="button button-primary"><?php esc_html_e( 'Save Archetype', 'site-pilot-ai' ); ?></button>
					</p>
				</form>

				<form method="post" class="spai-library-form">
					<?php wp_nonce_field( 'spai_library_actions', 'spai_library_nonce' ); ?>
					<h3><?php esc_html_e( 'Promote to Reusable Part', 'site-pilot-ai' ); ?></h3>
					<p>
						<label for="spai_part_template_id"><strong><?php esc_html_e( 'Template ID', 'site-pilot-ai' ); ?></strong></label><br />
						<input type="number" min="1" id="spai_part_template_id" name="spai_part_template_id" class="small-text" required />
					</p>
					<p>
						<label for="spai_part_title"><strong><?php esc_html_e( 'Title Override', 'site-pilot-ai' ); ?></strong></label><br />
						<input type="text" id="spai_part_title" name="spai_part_title" class="regular-text" placeholder="<?php esc_attr_e( 'Optional', 'site-pilot-ai' ); ?>" />
					</p>
					<p>
						<label for="spai_part_kind"><strong><?php esc_html_e( 'Part Kind', 'site-pilot-ai' ); ?></strong></label><br />
						<input type="text" id="spai_part_kind" name="spai_part_kind" class="regular-text" placeholder="<?php esc_attr_e( 'hero, faq, cta, testimonials', 'site-pilot-ai' ); ?>" />
					</p>
					<p>
						<label for="spai_part_style"><strong><?php esc_html_e( 'Style', 'site-pilot-ai' ); ?></strong></label><br />
						<input type="text" id="spai_part_style" name="spai_part_style" class="regular-text" placeholder="<?php esc_attr_e( 'clean, editorial, premium', 'site-pilot-ai' ); ?>" />
					</p>
					<p>
						<label for="spai_part_tags"><strong><?php esc_html_e( 'Tags', 'site-pilot-ai' ); ?></strong></label><br />
						<input type="text" id="spai_part_tags" name="spai_part_tags" class="regular-text" placeholder="<?php esc_attr_e( 'comma, separated, tags', 'site-pilot-ai' ); ?>" />
					</p>
					<p>
						<button type="submit" name="spai_promote_template_part" class="button button-primary"><?php esc_html_e( 'Save Reusable Part', 'site-pilot-ai' ); ?></button>
					</p>
				</form>
			</div>
		</div>

		<div class="spai-card">
			<h2><?php esc_html_e( 'Extract Live Section to Part', 'site-pilot-ai' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Use this when a live page contains a strong section you want to preserve for future pages. Enter the source page ID and the Elementor element ID for the section or container.', 'site-pilot-ai' ); ?>
			</p>

			<form method="post" class="spai-library-form">
				<?php wp_nonce_field( 'spai_library_actions', 'spai_library_nonce' ); ?>
				<p>
					<label for="spai_source_page_id"><strong><?php esc_html_e( 'Source Page ID', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="number" min="1" id="spai_source_page_id" name="spai_source_page_id" class="small-text" required />
				</p>
				<p>
					<label for="spai_source_element_id"><strong><?php esc_html_e( 'Elementor Element ID', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="text" id="spai_source_element_id" name="spai_source_element_id" class="regular-text" required />
				</p>
				<p>
					<label for="spai_extract_part_title"><strong><?php esc_html_e( 'Part Title', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="text" id="spai_extract_part_title" name="spai_extract_part_title" class="regular-text" placeholder="<?php esc_attr_e( 'Homepage Hero / Default', 'site-pilot-ai' ); ?>" />
				</p>
				<p>
					<label for="spai_extract_part_kind"><strong><?php esc_html_e( 'Part Kind', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="text" id="spai_extract_part_kind" name="spai_extract_part_kind" class="regular-text" placeholder="<?php esc_attr_e( 'hero, faq, cta, pricing', 'site-pilot-ai' ); ?>" />
				</p>
				<p>
					<label for="spai_extract_part_style"><strong><?php esc_html_e( 'Style', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="text" id="spai_extract_part_style" name="spai_extract_part_style" class="regular-text" placeholder="<?php esc_attr_e( 'bold, minimal, editorial', 'site-pilot-ai' ); ?>" />
				</p>
				<p>
					<label for="spai_extract_part_tags"><strong><?php esc_html_e( 'Tags', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="text" id="spai_extract_part_tags" name="spai_extract_part_tags" class="regular-text" placeholder="<?php esc_attr_e( 'homepage, saas, lead-gen', 'site-pilot-ai' ); ?>" />
				</p>
				<p>
					<button type="submit" name="spai_extract_section_part" class="button button-primary"><?php esc_html_e( 'Extract to Library', 'site-pilot-ai' ); ?></button>
				</p>
			</form>
		</div>
	</div>

	<!-- ======================== ADVANCED TAB ======================== -->
	<?php elseif ( 'advanced' === $current_tab ) : ?>

	<div class="spai-tab-content">
		<div class="spai-card">
			<h2><?php esc_html_e( 'REST API Reference', 'site-pilot-ai' ); ?></h2>

			<h3><?php esc_html_e( 'API Base URL', 'site-pilot-ai' ); ?></h3>
			<div class="spai-code-wrapper">
				<pre class="spai-code-block" id="spai-base-url"><?php echo esc_url( $rest_base ); ?></pre>
				<button type="button" class="button spai-copy-code-btn" data-target="spai-base-url">
					<span class="dashicons dashicons-clipboard"></span>
					<?php esc_html_e( 'Copy', 'site-pilot-ai' ); ?>
				</button>
			</div>

			<h3><?php esc_html_e( 'Test with curl', 'site-pilot-ai' ); ?></h3>
			<div class="spai-code-wrapper">
				<pre class="spai-code-block" id="spai-curl-test">curl -H "X-API-Key: <?php echo $is_hidden ? 'YOUR_API_KEY' : esc_attr( $display_key ); ?>" \
  "<?php echo esc_url( rest_url( 'site-pilot-ai/v1/site-info' ) ); ?>"</pre>
				<button type="button" class="button spai-copy-code-btn" data-target="spai-curl-test">
					<span class="dashicons dashicons-clipboard"></span>
					<?php esc_html_e( 'Copy', 'site-pilot-ai' ); ?>
				</button>
			</div>

			<h3><?php esc_html_e( 'Test MCP with curl', 'site-pilot-ai' ); ?></h3>
			<div class="spai-code-wrapper">
				<pre class="spai-code-block" id="spai-curl-mcp">curl -X POST "<?php echo esc_url( $mcp_url ); ?>" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: <?php echo $is_hidden ? 'YOUR_API_KEY' : esc_attr( $display_key ); ?>" \
  -d '{"jsonrpc":"2.0","method":"tools/list","id":1}'</pre>
				<button type="button" class="button spai-copy-code-btn" data-target="spai-curl-mcp">
					<span class="dashicons dashicons-clipboard"></span>
					<?php esc_html_e( 'Copy', 'site-pilot-ai' ); ?>
				</button>
			</div>
		</div>

		<div class="spai-card">
			<h2><?php esc_html_e( 'Available Endpoints', 'site-pilot-ai' ); ?></h2>
			<table class="widefat spai-endpoints-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Method', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'Endpoint', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'Description', 'site-pilot-ai' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr class="spai-endpoint-section"><td colspan="3"><strong><?php esc_html_e( 'Core', 'site-pilot-ai' ); ?></strong></td></tr>
					<tr><td>GET</td><td>/site-info</td><td><?php esc_html_e( 'Site information', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET</td><td>/analytics</td><td><?php esc_html_e( 'API analytics', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET</td><td>/plugins</td><td><?php esc_html_e( 'Detected plugins', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET/POST</td><td>/mcp</td><td><?php esc_html_e( 'MCP protocol endpoint (GET = server info, POST = JSON-RPC)', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>POST</td><td>/oauth/token</td><td><?php esc_html_e( 'OAuth client credentials token endpoint', 'site-pilot-ai' ); ?></td></tr>

					<tr class="spai-endpoint-section"><td colspan="3"><strong><?php esc_html_e( 'Content', 'site-pilot-ai' ); ?></strong></td></tr>
					<tr><td>GET/POST</td><td>/posts</td><td><?php esc_html_e( 'List/create posts', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET/PUT/DELETE</td><td>/posts/{id}</td><td><?php esc_html_e( 'Single post operations', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET/POST</td><td>/pages</td><td><?php esc_html_e( 'List/create pages', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET/PUT</td><td>/pages/{id}</td><td><?php esc_html_e( 'Single page operations', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET/POST</td><td>/media</td><td><?php esc_html_e( 'List/upload media', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>DELETE</td><td>/media/{id}</td><td><?php esc_html_e( 'Delete media attachment', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>POST</td><td>/media/from-url</td><td><?php esc_html_e( 'Upload from URL', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET</td><td>/drafts</td><td><?php esc_html_e( 'List drafts', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>DELETE</td><td>/drafts/delete-all</td><td><?php esc_html_e( 'Delete all drafts', 'site-pilot-ai' ); ?></td></tr>

					<tr class="spai-endpoint-section"><td colspan="3"><strong><?php esc_html_e( 'Elementor', 'site-pilot-ai' ); ?></strong></td></tr>
					<tr><td>GET</td><td>/elementor/status</td><td><?php esc_html_e( 'Elementor status', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET/POST</td><td>/elementor/{id}</td><td><?php esc_html_e( 'Get/set Elementor data', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET</td><td>/elementor/{id}/summary</td><td><?php esc_html_e( 'Lightweight structural summary', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>POST</td><td>/elementor/{id}/edit-section</td><td><?php esc_html_e( 'Surgical element editing', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>POST</td><td>/elementor/page</td><td><?php esc_html_e( 'Create Elementor page', 'site-pilot-ai' ); ?></td></tr>

					<?php if ( $is_pro ) : ?>
					<tr class="spai-endpoint-section"><td colspan="3"><strong><?php esc_html_e( 'Pro: SEO', 'site-pilot-ai' ); ?> <span class="spai-badge spai-badge-pro">PRO</span></strong></td></tr>
					<tr><td>GET/POST</td><td>/seo/{id}</td><td><?php esc_html_e( 'Get/set SEO metadata', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET</td><td>/seo/{id}/analyze</td><td><?php esc_html_e( 'SEO analysis', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET</td><td>/seo/status</td><td><?php esc_html_e( 'SEO plugin status', 'site-pilot-ai' ); ?></td></tr>

					<tr class="spai-endpoint-section"><td colspan="3"><strong><?php esc_html_e( 'Pro: Forms', 'site-pilot-ai' ); ?> <span class="spai-badge spai-badge-pro">PRO</span></strong></td></tr>
					<tr><td>GET</td><td>/forms</td><td><?php esc_html_e( 'List forms', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET</td><td>/forms/{plugin}/{id}</td><td><?php esc_html_e( 'Get form details', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET</td><td>/forms/status</td><td><?php esc_html_e( 'Forms plugin status', 'site-pilot-ai' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<?php
		/**
		 * Action for Pro add-on to render additional admin tab content.
		 */
		do_action( 'spai_admin_tab_content', 'advanced' );
		?>

		<div class="spai-card">
			<h2><?php esc_html_e( 'Resources', 'site-pilot-ai' ); ?></h2>
			<ul class="spai-resources-list">
				<li>
					<span class="dashicons dashicons-book"></span>
					<a href="https://sitepilotai.mumega.com/docs" target="_blank"><?php esc_html_e( 'Documentation & Source Code', 'site-pilot-ai' ); ?></a>
				</li>
				<li>
					<span class="dashicons dashicons-sos"></span>
					<a href="https://github.com/Digidinc/wp-ai-operator/issues" target="_blank"><?php esc_html_e( 'Report a Bug', 'site-pilot-ai' ); ?></a>
				</li>
				<li>
					<span class="dashicons dashicons-info"></span>
					<a href="https://modelcontextprotocol.io" target="_blank"><?php esc_html_e( 'About MCP (Model Context Protocol)', 'site-pilot-ai' ); ?></a>
				</li>
			</ul>
		</div>
	</div>

	<!-- ======================== CHANGELOG TAB ======================== -->
	<?php elseif ( 'changelog' === $current_tab ) : ?>

	<div class="spai-tab-content">
		<div class="spai-card">
			<h2>
				<span class="dashicons dashicons-list-view"></span>
				<?php esc_html_e( 'Changelog', 'site-pilot-ai' ); ?>
				<span class="spai-version" style="margin-left:8px;">
					<?php
					/* translators: %s: current plugin version */
					printf( esc_html__( 'Current: v%s', 'site-pilot-ai' ), esc_html( SPAI_VERSION ) );
					?>
				</span>
			</h2>

			<div class="spai-changelog">
				<?php
				$readme_path = SPAI_PLUGIN_DIR . 'readme.txt';
				$changelog_html = '';

				if ( file_exists( $readme_path ) ) {
					// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local file
					$readme_content = file_get_contents( $readme_path );
					$changelog_pos  = strpos( $readme_content, '== Changelog ==' );

					if ( false !== $changelog_pos ) {
						$changelog_text = substr( $readme_content, $changelog_pos + strlen( '== Changelog ==' ) );
						// Stop at the next == section
						$next_section = strpos( $changelog_text, "\n== " );
						if ( false !== $next_section ) {
							$changelog_text = substr( $changelog_text, 0, $next_section );
						}

						// Parse the changelog into version blocks
						$lines   = explode( "\n", trim( $changelog_text ) );
						$version = '';
						$items   = array();
						$first   = true;

						foreach ( $lines as $line ) {
							$line = trim( $line );
							if ( empty( $line ) ) {
								continue;
							}

							// Version header: = X.Y.Z =
							if ( preg_match( '/^= (.+?) =$/', $line, $m ) ) {
								// Output previous version block
								if ( $version && ! empty( $items ) ) {
									$is_current = version_compare( trim( $version, ' ' ), SPAI_VERSION, '==' );
									$open_attr  = $first ? ' open' : '';
									echo '<details class="spai-changelog-version"' . $open_attr . '>';
									echo '<summary>';
									echo '<strong>' . esc_html( 'v' . $version ) . '</strong>';
									if ( $is_current ) {
										echo ' <span class="spai-badge" style="background:#28a745;color:#fff;">' . esc_html__( 'Current', 'site-pilot-ai' ) . '</span>';
									}
									echo '</summary>';
									echo '<ul class="spai-changelog-items">';
									foreach ( $items as $item ) {
										echo '<li>' . esc_html( $item ) . '</li>';
									}
									echo '</ul>';
									echo '</details>';
									$first = false;
								}
								$version = $m[1];
								$items   = array();
							} elseif ( preg_match( '/^\* (.+)$/', $line, $m ) ) {
								$items[] = $m[1];
							}
						}

						// Output last version block
						if ( $version && ! empty( $items ) ) {
							$is_current = version_compare( trim( $version, ' ' ), SPAI_VERSION, '==' );
							echo '<details class="spai-changelog-version">';
							echo '<summary>';
							echo '<strong>' . esc_html( 'v' . $version ) . '</strong>';
							if ( $is_current ) {
								echo ' <span class="spai-badge" style="background:#28a745;color:#fff;">' . esc_html__( 'Current', 'site-pilot-ai' ) . '</span>';
							}
							echo '</summary>';
							echo '<ul class="spai-changelog-items">';
							foreach ( $items as $item ) {
								echo '<li>' . esc_html( $item ) . '</li>';
							}
							echo '</ul>';
							echo '</details>';
						}
					}
				}

				if ( empty( $changelog_text ) ) {
					echo '<p><em>' . esc_html__( 'Changelog not available.', 'site-pilot-ai' ) . '</em></p>';
				}
				?>
			</div>
		</div>
	</div>

	<?php endif; ?>

</div>
