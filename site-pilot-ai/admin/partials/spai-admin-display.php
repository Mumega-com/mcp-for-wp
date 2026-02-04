<?php
/**
 * Admin page template
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$api_key = get_option( 'spai_api_key', '' );
$admin = new Spai_Admin();
$capabilities = $admin->get_capabilities_display();
$is_pro = class_exists( 'Spai_Pro_Loader' );
$license = function_exists( 'spai_license' ) ? spai_license() : null;
$is_paying = $license ? $license->is_paying() : false;
$plan = $license ? $license->get_plan() : 'free';
$upgrade_url = $license ? $license->get_upgrade_url() : 'https://sitepilot.ai/pricing/';
?>

<div class="wrap spai-admin">
	<h1><?php esc_html_e( 'Site Pilot AI', 'site-pilot-ai' ); ?></h1>

	<?php if ( ! $is_paying ) : ?>
	<div class="spai-upgrade-banner">
		<div class="spai-upgrade-content">
			<strong><?php esc_html_e( 'Unlock Pro Features', 'site-pilot-ai' ); ?></strong>
			<p><?php esc_html_e( 'Get full Elementor integration, SEO tools, forms, WooCommerce support, and more.', 'site-pilot-ai' ); ?></p>
			<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary">
				<?php esc_html_e( 'Upgrade to Pro - $49/year', 'site-pilot-ai' ); ?>
			</a>
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

	<div class="spai-card">
		<h2><?php esc_html_e( 'API Key', 'site-pilot-ai' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'Use this API key to authenticate requests from Claude or other AI assistants.', 'site-pilot-ai' ); ?>
		</p>

		<div class="spai-api-key-wrapper">
			<input
				type="text"
				id="spai-api-key"
				class="spai-api-key-input"
				value="<?php echo esc_attr( $api_key ); ?>"
				readonly
			/>
			<button type="button" class="button spai-copy-btn" data-copy="<?php echo esc_attr( $api_key ); ?>">
				<?php esc_html_e( 'Copy', 'site-pilot-ai' ); ?>
			</button>
		</div>

		<form method="post" class="spai-regenerate-form">
			<?php wp_nonce_field( 'spai_regenerate_key', 'spai_nonce' ); ?>
			<button type="submit" name="spai_regenerate_key" class="button spai-regenerate-btn">
				<?php esc_html_e( 'Regenerate API Key', 'site-pilot-ai' ); ?>
			</button>
			<span class="description">
				<?php esc_html_e( 'Warning: This will invalidate the current key immediately.', 'site-pilot-ai' ); ?>
			</span>
		</form>
	</div>

	<div class="spai-card">
		<h2><?php esc_html_e( 'Detected Capabilities', 'site-pilot-ai' ); ?></h2>
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
							echo '<span class="spai-badge spai-badge-pro">Pro</span>';
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

	<div class="spai-card">
		<h2><?php esc_html_e( 'Quick Start', 'site-pilot-ai' ); ?></h2>

		<h3><?php esc_html_e( 'API Base URL', 'site-pilot-ai' ); ?></h3>
		<code class="spai-code-block"><?php echo esc_url( rest_url( 'site-pilot-ai/v1/' ) ); ?></code>

		<h3><?php esc_html_e( 'Test Connection', 'site-pilot-ai' ); ?></h3>
		<pre class="spai-code-block">curl -H "X-API-Key: <?php echo esc_attr( $api_key ); ?>" \
  "<?php echo esc_url( rest_url( 'site-pilot-ai/v1/site-info' ) ); ?>"</pre>

		<h3><?php esc_html_e( 'Available Endpoints', 'site-pilot-ai' ); ?></h3>
		<details class="spai-endpoints-details">
			<summary><?php esc_html_e( 'Show all endpoints', 'site-pilot-ai' ); ?></summary>
			<table class="widefat spai-endpoints-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Method', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'Endpoint', 'site-pilot-ai' ); ?></th>
						<th><?php esc_html_e( 'Description', 'site-pilot-ai' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr><td>GET</td><td>/site-info</td><td><?php esc_html_e( 'Site information', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET</td><td>/analytics</td><td><?php esc_html_e( 'API analytics', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET</td><td>/plugins</td><td><?php esc_html_e( 'Detected plugins', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET/POST</td><td>/posts</td><td><?php esc_html_e( 'List/create posts', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET/PUT/DELETE</td><td>/posts/{id}</td><td><?php esc_html_e( 'Single post operations', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET/POST</td><td>/pages</td><td><?php esc_html_e( 'List/create pages', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET/PUT</td><td>/pages/{id}</td><td><?php esc_html_e( 'Single page operations', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET/POST</td><td>/media</td><td><?php esc_html_e( 'List/upload media', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>POST</td><td>/media/from-url</td><td><?php esc_html_e( 'Upload from URL', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET</td><td>/drafts</td><td><?php esc_html_e( 'List drafts', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>DELETE</td><td>/drafts/delete-all</td><td><?php esc_html_e( 'Delete all drafts', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET</td><td>/elementor/status</td><td><?php esc_html_e( 'Elementor status', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>GET/POST</td><td>/elementor/{id}</td><td><?php esc_html_e( 'Get/set Elementor data', 'site-pilot-ai' ); ?></td></tr>
					<tr><td>POST</td><td>/elementor/page</td><td><?php esc_html_e( 'Create Elementor page', 'site-pilot-ai' ); ?></td></tr>
				</tbody>
			</table>
		</details>
	</div>

	<div class="spai-card">
		<h2><?php esc_html_e( 'Settings', 'site-pilot-ai' ); ?></h2>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'spai_settings_group' );
			do_settings_sections( 'spai_settings' );
			submit_button();
			?>
		</form>
	</div>
</div>
