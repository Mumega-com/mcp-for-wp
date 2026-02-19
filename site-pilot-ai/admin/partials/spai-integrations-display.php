<?php
/**
 * Integrations admin page template.
 *
 * @package SitePilotAI
 * @since   1.1.0
 *
 * @var array $providers Provider status data from Spai_Integration_Manager.
 * @var bool  $is_pro    Whether Pro license is active.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap spai-wrap">
	<h1><?php esc_html_e( 'AI Integrations', 'site-pilot-ai' ); ?></h1>
	<p class="description">
		<?php esc_html_e( 'Connect third-party AI services to unlock image generation, vision analysis, text-to-speech, and stock photos via MCP tools.', 'site-pilot-ai' ); ?>
	</p>

	<div class="spai-integrations-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(360px,1fr));gap:20px;margin-top:20px;">
		<?php foreach ( $providers as $slug => $provider ) : ?>
			<?php
			$is_pro_provider = 'free' !== $provider['tier'];
			$locked          = $is_pro_provider && ! $is_pro;
			?>
			<div class="spai-integration-card" style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;position:relative;">
				<?php if ( $is_pro_provider ) : ?>
					<span style="position:absolute;top:12px;right:12px;background:<?php echo $is_pro ? '#00a32a' : '#dba617'; ?>;color:#fff;font-size:11px;padding:2px 8px;border-radius:3px;font-weight:600;">
						<?php echo $is_pro ? 'PRO' : esc_html__( 'PRO REQUIRED', 'site-pilot-ai' ); ?>
					</span>
				<?php else : ?>
					<span style="position:absolute;top:12px;right:12px;background:#2271b1;color:#fff;font-size:11px;padding:2px 8px;border-radius:3px;font-weight:600;">
						<?php esc_html_e( 'FREE', 'site-pilot-ai' ); ?>
					</span>
				<?php endif; ?>

				<h3 style="margin:0 0 8px 0;font-size:16px;">
					<?php echo esc_html( $provider['name'] ); ?>
				</h3>

				<p style="margin:0 0 15px 0;">
					<a href="<?php echo esc_url( $provider['url'] ); ?>" target="_blank" rel="noopener">
						<?php esc_html_e( 'Get API Key', 'site-pilot-ai' ); ?> &rarr;
					</a>
				</p>

				<?php if ( $locked ) : ?>
					<p style="color:#666;font-style:italic;">
						<?php esc_html_e( 'Upgrade to Pro to use this integration.', 'site-pilot-ai' ); ?>
						<?php if ( function_exists( 'spai_license' ) ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=site-pilot-ai-pricing' ) ); ?>">
								<?php esc_html_e( 'Upgrade', 'site-pilot-ai' ); ?>
							</a>
						<?php endif; ?>
					</p>
				<?php else : ?>
					<div class="spai-integration-key-form" data-provider="<?php echo esc_attr( $slug ); ?>">
						<?php if ( $provider['configured'] ) : ?>
							<div class="spai-key-configured">
								<span style="display:inline-block;width:10px;height:10px;border-radius:50%;margin-right:6px;background:<?php echo 'ok' === $provider['test_status'] ? '#00a32a' : ( 'failed' === $provider['test_status'] ? '#d63638' : '#dba617' ); ?>;"></span>
								<code style="background:#f0f0f1;padding:2px 8px;border-radius:3px;">
									<?php
									esc_html_e( 'Key configured', 'site-pilot-ai' );
									if ( $provider['configured_at'] ) {
										echo ' &mdash; ' . esc_html( human_time_diff( strtotime( $provider['configured_at'] ) ) ) . ' ago';
									}
									?>
								</code>
							</div>
							<div style="margin-top:10px;display:flex;gap:8px;">
								<button type="button" class="button spai-test-integration" data-provider="<?php echo esc_attr( $slug ); ?>">
									<?php esc_html_e( 'Test Connection', 'site-pilot-ai' ); ?>
								</button>
								<button type="button" class="button spai-remove-integration" data-provider="<?php echo esc_attr( $slug ); ?>" style="color:#d63638;">
									<?php esc_html_e( 'Remove', 'site-pilot-ai' ); ?>
								</button>
							</div>
							<div style="margin-top:10px;">
								<input type="text" class="regular-text spai-integration-key-input" placeholder="<?php esc_attr_e( 'Paste new key to update...', 'site-pilot-ai' ); ?>" style="display:none;" />
								<button type="button" class="button spai-update-key-toggle" data-provider="<?php echo esc_attr( $slug ); ?>">
									<?php esc_html_e( 'Update Key', 'site-pilot-ai' ); ?>
								</button>
								<button type="button" class="button button-primary spai-save-integration" data-provider="<?php echo esc_attr( $slug ); ?>" style="display:none;">
									<?php esc_html_e( 'Save', 'site-pilot-ai' ); ?>
								</button>
							</div>
						<?php else : ?>
							<div style="display:flex;gap:8px;align-items:center;">
								<input type="text" class="regular-text spai-integration-key-input" placeholder="<?php esc_attr_e( 'Paste your API key...', 'site-pilot-ai' ); ?>" />
								<button type="button" class="button button-primary spai-save-integration" data-provider="<?php echo esc_attr( $slug ); ?>">
									<?php esc_html_e( 'Save', 'site-pilot-ai' ); ?>
								</button>
							</div>
						<?php endif; ?>
						<span class="spai-integration-status" style="display:block;margin-top:8px;font-size:13px;"></span>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>

	<div style="margin-top:30px;padding:15px;background:#f0f6fc;border:1px solid #c3c4c7;border-radius:4px;">
		<h3 style="margin:0 0 8px;"><?php esc_html_e( 'Available MCP Tools', 'site-pilot-ai' ); ?></h3>
		<p style="margin:0 0 10px;color:#50575e;">
			<?php esc_html_e( 'Once configured, these tools become available to AI assistants via MCP:', 'site-pilot-ai' ); ?>
		</p>
		<table class="widefat striped" style="max-width:700px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Tool', 'site-pilot-ai' ); ?></th>
					<th><?php esc_html_e( 'Provider', 'site-pilot-ai' ); ?></th>
					<th><?php esc_html_e( 'Tier', 'site-pilot-ai' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr><td><code>wp_search_stock_photos</code></td><td>Pexels</td><td>Free</td></tr>
				<tr><td><code>wp_download_stock_photo</code></td><td>Pexels</td><td>Free</td></tr>
				<tr><td><code>wp_generate_image</code></td><td>OpenAI / Gemini</td><td>Pro</td></tr>
				<tr><td><code>wp_generate_featured_image</code></td><td>OpenAI / Gemini</td><td>Pro</td></tr>
				<tr><td><code>wp_generate_alt_text</code></td><td>OpenAI / Gemini</td><td>Pro</td></tr>
				<tr><td><code>wp_describe_image</code></td><td>OpenAI / Gemini</td><td>Pro</td></tr>
				<tr><td><code>wp_generate_excerpt</code></td><td>OpenAI / Gemini</td><td>Pro</td></tr>
				<tr><td><code>wp_text_to_speech</code></td><td>ElevenLabs</td><td>Pro</td></tr>
			</tbody>
		</table>
	</div>
</div>

<script type="text/javascript">
jQuery(function($) {
	var nonce = typeof spaiIntegrations !== 'undefined' ? spaiIntegrations.nonce : '<?php echo esc_js( wp_create_nonce( 'spai_integrations_nonce' ) ); ?>';
	var ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';

	// Save key.
	$(document).on('click', '.spai-save-integration', function() {
		var $btn = $(this);
		var provider = $btn.data('provider');
		var $form = $btn.closest('.spai-integration-key-form');
		var key = $form.find('.spai-integration-key-input').val().trim();
		var $status = $form.find('.spai-integration-status');

		if (!key) {
			$status.text('Please enter an API key.').css('color', '#d63638');
			return;
		}

		$btn.prop('disabled', true).text('Saving...');
		$.post(ajaxUrl, {
			action: 'spai_save_integration_key',
			nonce: nonce,
			provider: provider,
			key: key
		}, function(response) {
			if (response.success) {
				$status.text('Saved! Reloading...').css('color', '#00a32a');
				location.reload();
			} else {
				$status.text(response.data.message || 'Save failed').css('color', '#d63638');
				$btn.prop('disabled', false).text('Save');
			}
		}).fail(function() {
			$status.text('Request failed').css('color', '#d63638');
			$btn.prop('disabled', false).text('Save');
		});
	});

	// Remove key.
	$(document).on('click', '.spai-remove-integration', function() {
		if (!confirm('Are you sure you want to remove this API key?')) return;

		var $btn = $(this);
		var provider = $btn.data('provider');
		var $form = $btn.closest('.spai-integration-key-form');
		var $status = $form.find('.spai-integration-status');

		$btn.prop('disabled', true).text('Removing...');
		$.post(ajaxUrl, {
			action: 'spai_remove_integration_key',
			nonce: nonce,
			provider: provider
		}, function(response) {
			if (response.success) {
				$status.text('Removed! Reloading...').css('color', '#00a32a');
				location.reload();
			} else {
				$status.text(response.data.message || 'Remove failed').css('color', '#d63638');
				$btn.prop('disabled', false).text('Remove');
			}
		});
	});

	// Test connection.
	$(document).on('click', '.spai-test-integration', function() {
		var $btn = $(this);
		var provider = $btn.data('provider');
		var $form = $btn.closest('.spai-integration-key-form');
		var $status = $form.find('.spai-integration-status');

		$btn.prop('disabled', true).text('Testing...');
		$.post(ajaxUrl, {
			action: 'spai_test_integration',
			nonce: nonce,
			provider: provider
		}, function(response) {
			if (response.success) {
				$status.text(response.data.message || 'Connected!').css('color', '#00a32a');
			} else {
				$status.text(response.data.message || 'Connection failed').css('color', '#d63638');
			}
			$btn.prop('disabled', false).text('Test Connection');
		}).fail(function() {
			$status.text('Request failed').css('color', '#d63638');
			$btn.prop('disabled', false).text('Test Connection');
		});
	});

	// Toggle update key input.
	$(document).on('click', '.spai-update-key-toggle', function() {
		var $form = $(this).closest('.spai-integration-key-form');
		$form.find('.spai-integration-key-input').toggle().focus();
		$form.find('.spai-save-integration').toggle();
	});
});
</script>
