<?php
/**
 * Settings page template.
 *
 * Variables available from render_settings_page():
 *   $site_profile         — array   structured site profile
 *   $site_context_preview — string  generated canonical site context
 *   $llms_url             — string  public llms.txt URL
 *   $llms_preview         — string  llms.txt content preview
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap spai-admin">
	<h1 class="spai-header">
		<span class="spai-logo">
			<span class="dashicons dashicons-admin-generic"></span>
		</span>
		<?php esc_html_e( 'Settings', 'site-pilot-ai' ); ?>
		<span class="spai-version">v<?php echo esc_html( SPAI_VERSION ); ?></span>
	</h1>

	<?php settings_errors( 'spai_messages' ); ?>

	<div class="spai-tab-content">

		<div class="spai-card">
			<h2>
				<span class="dashicons dashicons-awards"></span>
				<?php esc_html_e( 'About', 'site-pilot-ai' ); ?>
			</h2>
			<p class="description">
				<?php esc_html_e( 'All features are included for free. mumcp connects your WordPress site to AI assistants via the Model Context Protocol (MCP).', 'site-pilot-ai' ); ?>
			</p>
			<p style="margin-top:10px;">
				<a href="https://mumega.com/" target="_blank" class="button">
					<span class="dashicons dashicons-external" style="margin-top:4px;"></span>
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
		 * Action for Pro add-on to render additional settings cards.
		 */
		do_action( 'spai_admin_settings_cards' );
		?>

	</div><!-- .spai-tab-content -->
</div><!-- .wrap -->
