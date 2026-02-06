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
$license         = function_exists( 'spai_license' ) ? spai_license() : null;
$is_paying       = $license ? $license->is_paying() : false;
$plan            = $license ? $license->get_plan() : 'free';
$is_pro          = $license ? $license->is_pro() : false;
$upgrade_url     = $license ? $license->get_upgrade_url() : 'https://sitepilot.ai/pricing/';
$is_first        = get_option( 'spai_first_activation', false );
$rest_base       = rest_url( 'site-pilot-ai/v1/' );
$mcp_url         = rest_url( 'site-pilot-ai/v1/mcp' );

// Current tab
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
		<?php esc_html_e( 'Site Pilot AI', 'site-pilot-ai' ); ?>
		<span class="spai-version">v<?php echo esc_html( SPAI_VERSION ); ?></span>
		<?php if ( $is_pro ) : ?>
		<span class="spai-version" style="margin-left:10px;background:#1d2327;color:#fff;padding:2px 8px;border-radius:999px;font-size:12px;">
			<?php echo esc_html( strtoupper( $plan ) ); ?>
		</span>
		<?php endif; ?>
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

	<?php if ( ! $is_pro ) : ?>
	<div class="spai-upgrade-banner">
		<div class="spai-upgrade-icon">
			<span class="dashicons dashicons-superhero-alt"></span>
		</div>
		<div class="spai-upgrade-content">
			<h2><?php esc_html_e( 'Upgrade to Pro', 'site-pilot-ai' ); ?></h2>
			<p class="spai-upgrade-tagline"><?php esc_html_e( 'Unlock SEO tools, form builders, advanced Elementor, and WooCommerce management', 'site-pilot-ai' ); ?></p>

			<div class="spai-upgrade-features">
				<div class="spai-feature-col">
					<div class="spai-feature-item">
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'SEO Tools (Yoast, RankMath)', 'site-pilot-ai' ); ?>
					</div>
					<div class="spai-feature-item">
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'Form Builders Support', 'site-pilot-ai' ); ?>
					</div>
				</div>
				<div class="spai-feature-col">
					<div class="spai-feature-item">
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'Advanced Elementor', 'site-pilot-ai' ); ?>
					</div>
					<div class="spai-feature-item">
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'WooCommerce Management', 'site-pilot-ai' ); ?>
					</div>
				</div>
			</div>

			<div class="spai-upgrade-cta">
				<a href="<?php echo esc_url( $upgrade_url ); ?>" class="spai-upgrade-button" target="_blank">
					<span class="dashicons dashicons-cart"></span>
					<?php esc_html_e( 'Get Pro', 'site-pilot-ai' ); ?>
				</a>
			</div>
		</div>
	</div>
	<?php else : ?>
	<div class="spai-license-banner spai-license-active">
		<div class="spai-license-content">
			<span class="dashicons dashicons-yes-alt"></span>
			<strong><?php printf( esc_html__( '%s Plan Active', 'site-pilot-ai' ), esc_html( ucfirst( $plan ) ) ); ?></strong>
			<?php if ( $license && $license->get_expiration() ) : ?>
				<span class="spai-license-expiry">
					<?php printf( esc_html__( 'Renews: %s', 'site-pilot-ai' ), esc_html( date_i18n( get_option( 'date_format' ), strtotime( $license->get_expiration() ) ) ) ); ?>
				</span>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>

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
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=site-pilot-ai&tab=advanced' ) ); ?>"
		   class="nav-tab <?php echo 'advanced' === $current_tab ? 'nav-tab-active' : ''; ?>">
			<span class="dashicons dashicons-editor-code"></span>
			<?php esc_html_e( 'Advanced', 'site-pilot-ai' ); ?>
		</a>
	</nav>

	<!-- ======================== SETUP TAB ======================== -->
	<?php if ( 'setup' === $current_tab ) : ?>

	<div class="spai-tab-content">
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

			<h3><?php esc_html_e( 'Scoped API Keys', 'site-pilot-ai' ); ?></h3>
			<p class="description">
				<?php esc_html_e( 'Create additional keys with limited scopes. New keys are shown once only.', 'site-pilot-ai' ); ?>
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

			<form method="post" class="spai-regenerate-form">
				<?php wp_nonce_field( 'spai_manage_scoped_keys', 'spai_scoped_keys_nonce' ); ?>
				<p>
					<label for="spai_scoped_key_label"><strong><?php esc_html_e( 'Label', 'site-pilot-ai' ); ?></strong></label><br />
					<input type="text" id="spai_scoped_key_label" name="spai_scoped_key_label" class="regular-text" placeholder="<?php esc_attr_e( 'Example: Read-only CI bot', 'site-pilot-ai' ); ?>" />
				</p>
				<p>
					<strong><?php esc_html_e( 'Scopes', 'site-pilot-ai' ); ?></strong><br />
					<label><input type="checkbox" name="spai_scoped_key_scopes[]" value="read" checked /> <?php esc_html_e( 'Read', 'site-pilot-ai' ); ?></label>
					<label style="margin-left:12px;"><input type="checkbox" name="spai_scoped_key_scopes[]" value="write" /> <?php esc_html_e( 'Write', 'site-pilot-ai' ); ?></label>
					<label style="margin-left:12px;"><input type="checkbox" name="spai_scoped_key_scopes[]" value="admin" /> <?php esc_html_e( 'Admin', 'site-pilot-ai' ); ?></label>
				</p>
				<button type="submit" name="spai_create_scoped_key" class="button">
					<?php esc_html_e( 'Create Scoped Key', 'site-pilot-ai' ); ?>
				</button>
			</form>

			<?php if ( ! empty( $scoped_keys ) ) : ?>
			<table class="widefat striped" style="margin-top:12px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Label', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'Scopes', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'Created', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'Last Used', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'Status', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'Action', 'site-pilot-ai' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $scoped_keys as $key ) : ?>
					<tr>
						<td><?php echo esc_html( $key['label'] ); ?></td>
						<td><?php echo esc_html( implode( ', ', array_map( 'strtoupper', (array) $key['scopes'] ) ) ); ?></td>
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
		<div class="spai-card">
			<h2>
				<span class="dashicons dashicons-cloud"></span>
				<?php esc_html_e( 'Connect Claude Desktop', 'site-pilot-ai' ); ?>
			</h2>
			<p class="description">
				<?php esc_html_e( 'Claude Desktop can manage your WordPress site directly using the MCP protocol. Add this to your Claude Desktop configuration:', 'site-pilot-ai' ); ?>
			</p>

			<div class="spai-setup-steps">
				<div class="spai-step">
					<span class="spai-step-number">1</span>
					<div class="spai-step-content">
						<h3><?php esc_html_e( 'Open Claude Desktop Settings', 'site-pilot-ai' ); ?></h3>
						<p><?php esc_html_e( 'Go to Claude Desktop > Settings > Developer > Edit Config', 'site-pilot-ai' ); ?></p>
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
    "wordpress": {
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

		<div class="spai-card">
			<h2>
				<span class="dashicons dashicons-terminal"></span>
				<?php esc_html_e( 'Connect Claude Code (CLI)', 'site-pilot-ai' ); ?>
			</h2>
			<p class="description">
				<?php esc_html_e( 'For developers using Claude Code in the terminal:', 'site-pilot-ai' ); ?>
			</p>

			<div class="spai-setup-steps">
				<div class="spai-step">
					<span class="spai-step-number">1</span>
					<div class="spai-step-content">
						<h3><?php esc_html_e( 'Install the npm package', 'site-pilot-ai' ); ?></h3>
						<div class="spai-code-wrapper">
							<pre class="spai-code-block" id="spai-npm-install">npm install -g site-pilot-ai</pre>
							<button type="button" class="button spai-copy-code-btn" data-target="spai-npm-install">
								<span class="dashicons dashicons-clipboard"></span>
								<?php esc_html_e( 'Copy', 'site-pilot-ai' ); ?>
							</button>
						</div>
					</div>
				</div>

				<div class="spai-step">
					<span class="spai-step-number">2</span>
					<div class="spai-step-content">
						<h3><?php esc_html_e( 'Or use the remote MCP URL directly', 'site-pilot-ai' ); ?></h3>
						<p><?php esc_html_e( 'Add to your .claude.json or project settings:', 'site-pilot-ai' ); ?></p>
						<div class="spai-code-wrapper">
							<pre class="spai-code-block" id="spai-claude-code-config">{
  "mcpServers": {
    "wordpress": {
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
			</div>
		</div>

		<div class="spai-card">
			<h2>
				<span class="dashicons dashicons-format-chat"></span>
				<?php esc_html_e( 'Connect ChatGPT', 'site-pilot-ai' ); ?>
			</h2>
			<p class="description">
				<?php esc_html_e( 'Use ChatGPT to manage your site via a custom GPT with Actions:', 'site-pilot-ai' ); ?>
			</p>

			<div class="spai-setup-steps">
				<div class="spai-step">
					<span class="spai-step-number">1</span>
					<div class="spai-step-content">
						<h3><?php esc_html_e( 'Create a custom GPT', 'site-pilot-ai' ); ?></h3>
						<p><?php esc_html_e( 'Go to ChatGPT > Explore GPTs > Create and add an Action.', 'site-pilot-ai' ); ?></p>
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

		<div class="spai-card">
			<h2>
				<span class="dashicons dashicons-rest-api"></span>
				<?php esc_html_e( 'MCP Endpoint', 'site-pilot-ai' ); ?>
			</h2>
			<p class="description">
				<?php esc_html_e( 'Your site exposes a native MCP (Model Context Protocol) endpoint. Any MCP-compatible AI client can connect to:', 'site-pilot-ai' ); ?>
			</p>
			<div class="spai-code-wrapper">
				<pre class="spai-code-block" id="spai-mcp-url"><?php echo esc_url( $mcp_url ); ?></pre>
				<button type="button" class="button spai-copy-code-btn" data-target="spai-mcp-url">
					<span class="dashicons dashicons-clipboard"></span>
					<?php esc_html_e( 'Copy', 'site-pilot-ai' ); ?>
				</button>
			</div>
			<p class="description" style="margin-top: 10px;">
				<?php
				printf(
					esc_html__( 'Protocol: JSON-RPC 2.0 over HTTP POST. Auth: %s header. Tools available: %d free%s.', 'site-pilot-ai' ),
					'<code>X-API-Key</code>',
					17,
					$is_pro ? ' + 13 Pro' : ''
				);
				?>
			</p>
		</div>
	</div>

	<!-- ======================== SETTINGS TAB ======================== -->
	<?php elseif ( 'settings' === $current_tab ) : ?>

	<div class="spai-tab-content">
		<!-- License / Upgrade Card -->
		<div class="spai-card">
			<h2>
				<span class="dashicons dashicons-awards"></span>
				<?php esc_html_e( 'License', 'site-pilot-ai' ); ?>
			</h2>

			<?php if ( $is_paying ) : ?>
				<div class="spai-license-info">
					<table class="spai-license-table">
						<tr>
							<td><strong><?php esc_html_e( 'Status:', 'site-pilot-ai' ); ?></strong></td>
							<td><span class="spai-status spai-status-active"><?php esc_html_e( 'Active', 'site-pilot-ai' ); ?></span></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Plan:', 'site-pilot-ai' ); ?></strong></td>
							<td><?php echo esc_html( ucfirst( $plan ) ); ?></td>
						</tr>
						<?php if ( $license && $license->get_license_key() ) : ?>
						<tr>
							<td><strong><?php esc_html_e( 'License Key:', 'site-pilot-ai' ); ?></strong></td>
							<td><code><?php echo esc_html( $license->get_license_key() ); ?></code></td>
						</tr>
						<?php endif; ?>
						<?php if ( $license && $license->get_expiration() ) : ?>
						<tr>
							<td><strong><?php esc_html_e( 'Expires:', 'site-pilot-ai' ); ?></strong></td>
							<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $license->get_expiration() ) ) ); ?></td>
						</tr>
						<?php endif; ?>
					</table>

					<?php if ( function_exists( 'spa_fs' ) ) : ?>
					<p style="margin-top: 15px;">
						<a href="<?php echo esc_url( spa_fs()->get_account_url() ); ?>" class="button">
							<span class="dashicons dashicons-admin-users" style="margin-top: 4px;"></span>
							<?php esc_html_e( 'Manage License', 'site-pilot-ai' ); ?>
						</a>
					</p>
					<?php endif; ?>
				</div>
			<?php else : ?>
				<p class="description">
					<?php esc_html_e( 'You are on the Free plan. Upgrade to Pro to unlock SEO tools, form builders, advanced Elementor templates, and WooCommerce management.', 'site-pilot-ai' ); ?>
				</p>

				<div class="spai-license-actions" style="margin-top: 15px;">
					<?php if ( function_exists( 'spa_fs' ) ) : ?>
						<a href="<?php echo esc_url( spa_fs()->get_upgrade_url() ); ?>" class="button button-primary" style="margin-right: 10px;">
							<span class="dashicons dashicons-cart" style="margin-top: 4px;"></span>
							<?php esc_html_e( 'Upgrade to Pro', 'site-pilot-ai' ); ?>
						</a>
						<a href="<?php echo esc_url( spa_fs()->get_account_url() ); ?>" class="button">
							<span class="dashicons dashicons-admin-network" style="margin-top: 4px;"></span>
							<?php esc_html_e( 'Enter License Key', 'site-pilot-ai' ); ?>
						</a>
					<?php else : ?>
						<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary" target="_blank">
							<span class="dashicons dashicons-cart" style="margin-top: 4px;"></span>
							<?php esc_html_e( 'Upgrade to Pro', 'site-pilot-ai' ); ?>
						</a>
					<?php endif; ?>
				</div>

				<div class="spai-pro-features-grid" style="margin-top: 20px;">
					<table class="widefat">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Feature', 'site-pilot-ai' ); ?></th>
								<th style="text-align:center;"><?php esc_html_e( 'Free', 'site-pilot-ai' ); ?></th>
								<th style="text-align:center;"><span class="spai-badge spai-badge-pro">PRO</span></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><?php esc_html_e( 'Posts, Pages & Media', 'site-pilot-ai' ); ?></td>
								<td style="text-align:center;"><span class="dashicons dashicons-yes" style="color:#28a745;"></span></td>
								<td style="text-align:center;"><span class="dashicons dashicons-yes" style="color:#28a745;"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Elementor (Basic)', 'site-pilot-ai' ); ?></td>
								<td style="text-align:center;"><span class="dashicons dashicons-yes" style="color:#28a745;"></span></td>
								<td style="text-align:center;"><span class="dashicons dashicons-yes" style="color:#28a745;"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'MCP Protocol (Claude)', 'site-pilot-ai' ); ?></td>
								<td style="text-align:center;"><span class="dashicons dashicons-yes" style="color:#28a745;"></span></td>
								<td style="text-align:center;"><span class="dashicons dashicons-yes" style="color:#28a745;"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'SEO Tools (Yoast, RankMath, AIOSEO)', 'site-pilot-ai' ); ?></td>
								<td style="text-align:center;"><span class="dashicons dashicons-no-alt" style="color:#ccc;"></span></td>
								<td style="text-align:center;"><span class="dashicons dashicons-yes" style="color:#28a745;"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Form Builders (CF7, WPForms, Gravity)', 'site-pilot-ai' ); ?></td>
								<td style="text-align:center;"><span class="dashicons dashicons-no-alt" style="color:#ccc;"></span></td>
								<td style="text-align:center;"><span class="dashicons dashicons-yes" style="color:#28a745;"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Elementor Pro Templates & Landing Pages', 'site-pilot-ai' ); ?></td>
								<td style="text-align:center;"><span class="dashicons dashicons-no-alt" style="color:#ccc;"></span></td>
								<td style="text-align:center;"><span class="dashicons dashicons-yes" style="color:#28a745;"></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'WooCommerce Management', 'site-pilot-ai' ); ?></td>
								<td style="text-align:center;"><span class="dashicons dashicons-no-alt" style="color:#ccc;"></span></td>
								<td style="text-align:center;"><span class="dashicons dashicons-yes" style="color:#28a745;"></span></td>
							</tr>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
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

		<?php
		/**
		 * Action for Pro add-on to render additional settings tabs.
		 */
		do_action( 'spai_admin_settings_cards' );
		?>
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
					<tr><td>POST</td><td>/mcp</td><td><?php esc_html_e( 'MCP protocol endpoint', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>POST</td><td>/oauth/token</td><td><?php esc_html_e( 'OAuth client credentials token endpoint', 'site-pilot-ai' ); ?></td></tr>

					<tr class="spai-endpoint-section"><td colspan="3"><strong><?php esc_html_e( 'Content', 'site-pilot-ai' ); ?></strong></td></tr>
					<tr><td>GET/POST</td><td>/posts</td><td><?php esc_html_e( 'List/create posts', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET/PUT/DELETE</td><td>/posts/{id}</td><td><?php esc_html_e( 'Single post operations', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET/POST</td><td>/pages</td><td><?php esc_html_e( 'List/create pages', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET/PUT</td><td>/pages/{id}</td><td><?php esc_html_e( 'Single page operations', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET/POST</td><td>/media</td><td><?php esc_html_e( 'List/upload media', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>POST</td><td>/media/from-url</td><td><?php esc_html_e( 'Upload from URL', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET</td><td>/drafts</td><td><?php esc_html_e( 'List drafts', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>DELETE</td><td>/drafts/delete-all</td><td><?php esc_html_e( 'Delete all drafts', 'site-pilot-ai' ); ?></td></tr>

					<tr class="spai-endpoint-section"><td colspan="3"><strong><?php esc_html_e( 'Elementor', 'site-pilot-ai' ); ?></strong></td></tr>
					<tr><td>GET</td><td>/elementor/status</td><td><?php esc_html_e( 'Elementor status', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET/POST</td><td>/elementor/{id}</td><td><?php esc_html_e( 'Get/set Elementor data', 'site-pilot-ai' ); ?></td></tr>
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
					<a href="https://github.com/Digidinc/site-pilot-ai" target="_blank"><?php esc_html_e( 'Documentation & Source Code', 'site-pilot-ai' ); ?></a>
				</li>
				<li>
					<span class="dashicons dashicons-sos"></span>
					<a href="https://github.com/Digidinc/site-pilot-ai/issues" target="_blank"><?php esc_html_e( 'Report a Bug', 'site-pilot-ai' ); ?></a>
				</li>
				<li>
					<span class="dashicons dashicons-info"></span>
					<a href="https://modelcontextprotocol.io" target="_blank"><?php esc_html_e( 'About MCP (Model Context Protocol)', 'site-pilot-ai' ); ?></a>
				</li>
			</ul>
		</div>
	</div>

	<?php endif; ?>

</div>
