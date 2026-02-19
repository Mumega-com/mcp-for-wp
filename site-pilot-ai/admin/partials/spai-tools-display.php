<?php
/**
 * MCP Tools admin page template.
 *
 * @package SitePilotAI
 * @since   1.1.2
 *
 * @var array $disabled_categories Currently disabled category slugs.
 * @var array $category_meta       Category display metadata.
 * @var array $tool_counts         Category slug => tool count.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap spai-wrap">
	<h1><?php esc_html_e( 'MCP Tools', 'site-pilot-ai' ); ?></h1>
	<p class="description">
		<?php esc_html_e( 'Enable or disable tool categories exposed to AI assistants via MCP. Disabled categories are hidden from tools/list to reduce context noise.', 'site-pilot-ai' ); ?>
	</p>

	<div class="spai-tools-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;margin-top:20px;">
		<?php foreach ( $category_meta as $slug => $meta ) :
			$is_disabled = in_array( $slug, $disabled_categories, true );
			$count       = isset( $tool_counts[ $slug ] ) ? (int) $tool_counts[ $slug ] : 0;
		?>
			<div class="spai-tool-card" data-category="<?php echo esc_attr( $slug ); ?>"
				style="background:#fff;border:1px solid <?php echo $is_disabled ? '#dcdcde' : '#c3c4c7'; ?>;border-radius:4px;padding:20px;position:relative;<?php echo $is_disabled ? 'opacity:0.6;' : ''; ?>transition:opacity 0.2s;">

				<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
					<div style="display:flex;align-items:center;gap:10px;">
						<span class="dashicons <?php echo esc_attr( $meta['icon'] ); ?>" style="font-size:24px;width:24px;height:24px;color:#2271b1;"></span>
						<h3 style="margin:0;font-size:15px;"><?php echo esc_html( $meta['name'] ); ?></h3>
					</div>
					<label class="spai-toggle" style="position:relative;display:inline-block;width:40px;height:22px;cursor:pointer;">
						<input type="checkbox" class="spai-category-toggle"
							data-category="<?php echo esc_attr( $slug ); ?>"
							<?php checked( ! $is_disabled ); ?>
							style="opacity:0;width:0;height:0;" />
						<span style="position:absolute;top:0;left:0;right:0;bottom:0;background:<?php echo $is_disabled ? '#ccc' : '#00a32a'; ?>;border-radius:22px;transition:background 0.3s;"></span>
						<span style="position:absolute;top:2px;left:<?php echo $is_disabled ? '2px' : '20px'; ?>;width:18px;height:18px;background:#fff;border-radius:50%;transition:left 0.3s;box-shadow:0 1px 3px rgba(0,0,0,0.2);"></span>
					</label>
				</div>

				<p style="margin:0 0 8px;color:#50575e;font-size:13px;">
					<?php echo esc_html( $meta['description'] ); ?>
				</p>

				<span style="font-size:12px;color:#787c82;">
					<?php
					printf(
						/* translators: %d: number of tools */
						esc_html( _n( '%d tool', '%d tools', $count, 'site-pilot-ai' ) ),
						$count
					);
					?>
				</span>

				<span class="spai-tool-status" style="display:none;margin-left:8px;font-size:12px;"></span>
			</div>
		<?php endforeach; ?>
	</div>
</div>

<script type="text/javascript">
jQuery(function($) {
	var ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
	var nonce = '<?php echo esc_js( wp_create_nonce( 'spai_tools_nonce' ) ); ?>';

	$(document).on('change', '.spai-category-toggle', function() {
		var $cb = $(this);
		var category = $cb.data('category');
		var enabled = $cb.is(':checked') ? '1' : '0';
		var $card = $cb.closest('.spai-tool-card');
		var $status = $card.find('.spai-tool-status');
		var $slider = $cb.siblings('span').first();
		var $knob = $cb.siblings('span').last();

		// Immediate visual feedback.
		if (enabled === '1') {
			$card.css('opacity', '1');
			$slider.css('background', '#00a32a');
			$knob.css('left', '20px');
		} else {
			$card.css('opacity', '0.6');
			$slider.css('background', '#ccc');
			$knob.css('left', '2px');
		}

		$status.text('Saving...').css('color', '#666').show();

		$.post(ajaxUrl, {
			action: 'spai_toggle_tool_category',
			nonce: nonce,
			category: category,
			enabled: enabled
		}, function(response) {
			if (response.success) {
				$status.text(enabled === '1' ? 'Enabled' : 'Disabled').css('color', '#00a32a');
				setTimeout(function() { $status.fadeOut(); }, 1500);
			} else {
				$status.text(response.data.message || 'Error').css('color', '#d63638');
				// Revert toggle.
				$cb.prop('checked', enabled !== '1');
				$card.css('opacity', enabled !== '1' ? '1' : '0.6');
			}
		}).fail(function() {
			$status.text('Request failed').css('color', '#d63638');
			$cb.prop('checked', enabled !== '1');
		});
	});
});
</script>
