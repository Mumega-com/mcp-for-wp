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
		<?php esc_html_e( 'Connect third-party AI services to unlock image generation, vision analysis, text-to-speech, screenshots, and stock photos via MCP tools.', 'site-pilot-ai' ); ?>
	</p>
	<p class="description" style="margin-top:4px;">
		<?php esc_html_e( 'AI assistants can also configure these integrations via the wp_configure_integration MCP tool.', 'site-pilot-ai' ); ?>
	</p>

	<div class="spai-integrations-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(360px,1fr));gap:20px;margin-top:20px;">
		<?php foreach ( $providers as $slug => $provider ) : ?>
			<?php
			$is_pro_provider  = 'free' !== $provider['tier'];
			$locked           = $is_pro_provider && ! $is_pro;
			$is_multi_field   = ! empty( $provider['fields'] );
			$has_description  = ! empty( $provider['description'] );
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

				<?php if ( $has_description ) : ?>
					<p style="margin:0 0 10px 0;color:#50575e;font-size:13px;">
						<?php echo esc_html( $provider['description'] ); ?>
					</p>
				<?php endif; ?>

				<p style="margin:0 0 15px 0;">
					<a href="<?php echo esc_url( $provider['url'] ); ?>" target="_blank" rel="noopener">
						<?php echo $is_multi_field ? esc_html__( 'Setup Guide', 'site-pilot-ai' ) : esc_html__( 'Get API Key', 'site-pilot-ai' ); ?> &rarr;
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
					<div class="spai-integration-key-form" data-provider="<?php echo esc_attr( $slug ); ?>" data-multi-field="<?php echo $is_multi_field ? '1' : '0'; ?>">
						<?php if ( $provider['configured'] ) : ?>
							<div class="spai-key-configured">
								<span style="display:inline-block;width:10px;height:10px;border-radius:50%;margin-right:6px;background:<?php echo 'ok' === $provider['test_status'] ? '#00a32a' : ( 'failed' === $provider['test_status'] ? '#d63638' : '#dba617' ); ?>;"></span>
								<code style="background:#f0f0f1;padding:2px 8px;border-radius:3px;">
									<?php
									esc_html_e( 'Configured', 'site-pilot-ai' );
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
								<?php if ( $is_multi_field ) : ?>
									<div class="spai-multi-field-inputs" style="display:none;">
										<?php foreach ( $provider['fields'] as $field_key => $field_info ) : ?>
											<div style="margin-bottom:8px;">
												<label style="display:block;font-size:12px;font-weight:600;margin-bottom:2px;"><?php echo esc_html( $field_info['label'] ); ?></label>
												<input type="<?php echo 'password' === $field_info['type'] ? 'password' : 'text'; ?>"
													class="regular-text spai-config-field"
													data-field="<?php echo esc_attr( $field_key ); ?>"
													placeholder="<?php echo esc_attr( isset( $field_info['placeholder'] ) ? $field_info['placeholder'] : '' ); ?>" />
											</div>
										<?php endforeach; ?>
									</div>
								<?php else : ?>
									<input type="text" class="regular-text spai-integration-key-input" placeholder="<?php esc_attr_e( 'Paste new key to update...', 'site-pilot-ai' ); ?>" style="display:none;" />
								<?php endif; ?>
								<button type="button" class="button spai-update-key-toggle" data-provider="<?php echo esc_attr( $slug ); ?>">
									<?php esc_html_e( 'Update', 'site-pilot-ai' ); ?>
								</button>
								<button type="button" class="button button-primary spai-save-integration" data-provider="<?php echo esc_attr( $slug ); ?>" style="display:none;">
									<?php esc_html_e( 'Save', 'site-pilot-ai' ); ?>
								</button>
							</div>
						<?php else : ?>
							<?php if ( $is_multi_field ) : ?>
								<div class="spai-multi-field-inputs">
									<?php foreach ( $provider['fields'] as $field_key => $field_info ) : ?>
										<div style="margin-bottom:8px;">
											<label style="display:block;font-size:12px;font-weight:600;margin-bottom:2px;"><?php echo esc_html( $field_info['label'] ); ?></label>
											<input type="<?php echo 'password' === $field_info['type'] ? 'password' : 'text'; ?>"
												class="regular-text spai-config-field"
												data-field="<?php echo esc_attr( $field_key ); ?>"
												placeholder="<?php echo esc_attr( isset( $field_info['placeholder'] ) ? $field_info['placeholder'] : '' ); ?>" />
										</div>
									<?php endforeach; ?>
								</div>
								<button type="button" class="button button-primary spai-save-integration" data-provider="<?php echo esc_attr( $slug ); ?>">
									<?php esc_html_e( 'Save', 'site-pilot-ai' ); ?>
								</button>
							<?php else : ?>
								<div style="display:flex;gap:8px;align-items:center;">
									<input type="text" class="regular-text spai-integration-key-input" placeholder="<?php esc_attr_e( 'Paste your API key...', 'site-pilot-ai' ); ?>" />
									<button type="button" class="button button-primary spai-save-integration" data-provider="<?php echo esc_attr( $slug ); ?>">
										<?php esc_html_e( 'Save', 'site-pilot-ai' ); ?>
									</button>
								</div>
							<?php endif; ?>
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
				<tr><td><code>wp_screenshot_url</code></td><td>Screenshot Worker</td><td>Free</td></tr>
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
		<h4 style="margin:15px 0 8px;"><?php esc_html_e( 'Integration Management Tools', 'site-pilot-ai' ); ?></h4>
		<p style="margin:0 0 10px;color:#50575e;">
			<?php esc_html_e( 'AI assistants can manage integrations directly via MCP:', 'site-pilot-ai' ); ?>
		</p>
		<table class="widefat striped" style="max-width:700px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Tool', 'site-pilot-ai' ); ?></th>
					<th><?php esc_html_e( 'Description', 'site-pilot-ai' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr><td><code>wp_integrations_status</code></td><td><?php esc_html_e( 'List all integrations and their configuration status', 'site-pilot-ai' ); ?></td></tr>
				<tr><td><code>wp_configure_integration</code></td><td><?php esc_html_e( 'Set up a provider (API key or URL+token)', 'site-pilot-ai' ); ?></td></tr>
				<tr><td><code>wp_test_integration</code></td><td><?php esc_html_e( 'Test a provider connection', 'site-pilot-ai' ); ?></td></tr>
				<tr><td><code>wp_remove_integration</code></td><td><?php esc_html_e( 'Remove a provider configuration', 'site-pilot-ai' ); ?></td></tr>
			</tbody>
		</table>
	</div>
</div>

<script type="text/javascript">
jQuery(function($) {
	var nonce = typeof spaiIntegrations !== 'undefined' ? spaiIntegrations.nonce : '<?php echo esc_js( wp_create_nonce( 'spai_integrations_nonce' ) ); ?>';
	var ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';

	// Save key (supports both single-key and multi-field providers).
	$(document).on('click', '.spai-save-integration', function() {
		var $btn = $(this);
		var provider = $btn.data('provider');
		var $form = $btn.closest('.spai-integration-key-form');
		var isMultiField = $form.data('multi-field') === 1 || $form.data('multi-field') === '1';
		var $status = $form.find('.spai-integration-status');
		var postData = {
			action: 'spai_save_integration_key',
			nonce: nonce,
			provider: provider
		};

		if (isMultiField) {
			var config = {};
			var hasValue = false;
			$form.find('.spai-config-field').each(function() {
				var val = $(this).val().trim();
				if (val) hasValue = true;
				config[$(this).data('field')] = val;
			});
			if (!hasValue) {
				$status.text('Please fill in at least one field.').css('color', '#d63638');
				return;
			}
			postData.config = config;
		} else {
			var key = $form.find('.spai-integration-key-input').val().trim();
			if (!key) {
				$status.text('Please enter an API key.').css('color', '#d63638');
				return;
			}
			postData.key = key;
		}

		$btn.prop('disabled', true).text('Saving...');
		$.post(ajaxUrl, postData, function(response) {
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
		if (!confirm('Are you sure you want to remove this configuration?')) return;

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

	// Toggle update key/config inputs.
	$(document).on('click', '.spai-update-key-toggle', function() {
		var $form = $(this).closest('.spai-integration-key-form');
		$form.find('.spai-multi-field-inputs, .spai-integration-key-input').toggle();
		$form.find('.spai-save-integration').toggle();
		$form.find('.spai-config-field:first, .spai-integration-key-input').focus();
	});
});
</script>
