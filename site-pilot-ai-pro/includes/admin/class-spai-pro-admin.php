<?php
/**
 * Pro Admin
 *
 * @package SitePilotAI_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pro Admin class.
 *
 * Enhances the admin interface with Pro features.
 */
class Spai_Pro_Admin {

	/**
	 * Initialize admin features.
	 */
	public function __construct() {
		add_filter( 'spai_admin_tabs', array( $this, 'add_pro_tabs' ) );
		add_action( 'spai_admin_tab_content', array( $this, 'render_pro_tab_content' ) );
	}

	/**
	 * Add Pro tabs to admin page.
	 *
	 * @param array $tabs Existing tabs.
	 * @return array Modified tabs.
	 */
	public function add_pro_tabs( $tabs ) {
		$tabs['elementor-pro'] = __( 'Elementor Pro', 'site-pilot-ai-pro' );
		$tabs['seo']           = __( 'SEO', 'site-pilot-ai-pro' );
		$tabs['forms']         = __( 'Forms', 'site-pilot-ai-pro' );

		return $tabs;
	}

	/**
	 * Render Pro tab content.
	 *
	 * @param string $tab Current tab.
	 */
	public function render_pro_tab_content( $tab ) {
		switch ( $tab ) {
			case 'elementor-pro':
				$this->render_elementor_pro_tab();
				break;

			case 'seo':
				$this->render_seo_tab();
				break;

			case 'forms':
				$this->render_forms_tab();
				break;
		}
	}

	/**
	 * Render Elementor Pro tab.
	 */
	private function render_elementor_pro_tab() {
		$elementor_pro = new Spai_Elementor_Pro();
		?>
		<div class="spai-pro-section">
			<h2><?php esc_html_e( 'Elementor Pro Endpoints', 'site-pilot-ai-pro' ); ?></h2>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Method', 'site-pilot-ai-pro' ); ?></th>
						<th><?php esc_html_e( 'Endpoint', 'site-pilot-ai-pro' ); ?></th>
						<th><?php esc_html_e( 'Description', 'site-pilot-ai-pro' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>GET</code></td>
						<td><code>/elementor/templates</code></td>
						<td><?php esc_html_e( 'List all Elementor templates', 'site-pilot-ai-pro' ); ?></td>
					</tr>
					<tr>
						<td><code>POST</code></td>
						<td><code>/elementor/templates</code></td>
						<td><?php esc_html_e( 'Create a new template', 'site-pilot-ai-pro' ); ?></td>
					</tr>
					<tr>
						<td><code>GET</code></td>
						<td><code>/elementor/templates/{id}</code></td>
						<td><?php esc_html_e( 'Get template details', 'site-pilot-ai-pro' ); ?></td>
					</tr>
					<tr>
						<td><code>POST</code></td>
						<td><code>/elementor/templates/{id}/apply</code></td>
						<td><?php esc_html_e( 'Apply template to a page', 'site-pilot-ai-pro' ); ?></td>
					</tr>
					<tr>
						<td><code>POST</code></td>
						<td><code>/elementor/clone</code></td>
						<td><?php esc_html_e( 'Clone a page with Elementor data', 'site-pilot-ai-pro' ); ?></td>
					</tr>
					<tr>
						<td><code>POST</code></td>
						<td><code>/elementor/landing-page</code></td>
						<td><?php esc_html_e( 'Create a landing page', 'site-pilot-ai-pro' ); ?></td>
					</tr>
					<tr>
						<td><code>GET</code></td>
						<td><code>/elementor/widgets</code></td>
						<td><?php esc_html_e( 'List available widgets', 'site-pilot-ai-pro' ); ?></td>
					</tr>
					<tr>
						<td><code>GET</code></td>
						<td><code>/elementor/globals</code></td>
						<td><?php esc_html_e( 'Get global colors/fonts', 'site-pilot-ai-pro' ); ?></td>
					</tr>
				</tbody>
			</table>

			<h3><?php esc_html_e( 'Status', 'site-pilot-ai-pro' ); ?></h3>
			<ul>
				<li>
					<?php esc_html_e( 'Elementor:', 'site-pilot-ai-pro' ); ?>
					<?php echo $elementor_pro->is_elementor_active() ? '<span class="spai-status-active">' . esc_html__( 'Active', 'site-pilot-ai-pro' ) . '</span>' : '<span class="spai-status-inactive">' . esc_html__( 'Not Active', 'site-pilot-ai-pro' ) . '</span>'; ?>
				</li>
				<li>
					<?php esc_html_e( 'Elementor Pro:', 'site-pilot-ai-pro' ); ?>
					<?php echo $elementor_pro->is_elementor_pro_active() ? '<span class="spai-status-active">' . esc_html__( 'Active', 'site-pilot-ai-pro' ) . '</span>' : '<span class="spai-status-inactive">' . esc_html__( 'Not Active', 'site-pilot-ai-pro' ) . '</span>'; ?>
				</li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Render SEO tab.
	 */
	private function render_seo_tab() {
		$seo = new Spai_SEO();
		?>
		<div class="spai-pro-section">
			<h2><?php esc_html_e( 'SEO Endpoints', 'site-pilot-ai-pro' ); ?></h2>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Method', 'site-pilot-ai-pro' ); ?></th>
						<th><?php esc_html_e( 'Endpoint', 'site-pilot-ai-pro' ); ?></th>
						<th><?php esc_html_e( 'Description', 'site-pilot-ai-pro' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>GET</code></td>
						<td><code>/seo/status</code></td>
						<td><?php esc_html_e( 'Get SEO plugin status', 'site-pilot-ai-pro' ); ?></td>
					</tr>
					<tr>
						<td><code>GET</code></td>
						<td><code>/seo/{post_id}</code></td>
						<td><?php esc_html_e( 'Get SEO data for a post', 'site-pilot-ai-pro' ); ?></td>
					</tr>
					<tr>
						<td><code>PUT</code></td>
						<td><code>/seo/{post_id}</code></td>
						<td><?php esc_html_e( 'Update SEO data for a post', 'site-pilot-ai-pro' ); ?></td>
					</tr>
					<tr>
						<td><code>POST</code></td>
						<td><code>/seo/bulk</code></td>
						<td><?php esc_html_e( 'Bulk update SEO data', 'site-pilot-ai-pro' ); ?></td>
					</tr>
					<tr>
						<td><code>GET</code></td>
						<td><code>/seo/{post_id}/analyze</code></td>
						<td><?php esc_html_e( 'Analyze SEO for a post', 'site-pilot-ai-pro' ); ?></td>
					</tr>
				</tbody>
			</table>

			<h3><?php esc_html_e( 'Detected SEO Plugins', 'site-pilot-ai-pro' ); ?></h3>
			<ul>
				<li>
					<?php esc_html_e( 'Yoast SEO:', 'site-pilot-ai-pro' ); ?>
					<?php echo $seo->is_yoast_active() ? '<span class="spai-status-active">' . esc_html__( 'Active', 'site-pilot-ai-pro' ) . '</span>' : '<span class="spai-status-inactive">' . esc_html__( 'Not Active', 'site-pilot-ai-pro' ) . '</span>'; ?>
				</li>
				<li>
					<?php esc_html_e( 'RankMath:', 'site-pilot-ai-pro' ); ?>
					<?php echo $seo->is_rankmath_active() ? '<span class="spai-status-active">' . esc_html__( 'Active', 'site-pilot-ai-pro' ) . '</span>' : '<span class="spai-status-inactive">' . esc_html__( 'Not Active', 'site-pilot-ai-pro' ) . '</span>'; ?>
				</li>
				<li>
					<?php esc_html_e( 'AIOSEO:', 'site-pilot-ai-pro' ); ?>
					<?php echo $seo->is_aioseo_active() ? '<span class="spai-status-active">' . esc_html__( 'Active', 'site-pilot-ai-pro' ) . '</span>' : '<span class="spai-status-inactive">' . esc_html__( 'Not Active', 'site-pilot-ai-pro' ) . '</span>'; ?>
				</li>
				<li>
					<?php esc_html_e( 'SEOPress:', 'site-pilot-ai-pro' ); ?>
					<?php echo $seo->is_seopress_active() ? '<span class="spai-status-active">' . esc_html__( 'Active', 'site-pilot-ai-pro' ) . '</span>' : '<span class="spai-status-inactive">' . esc_html__( 'Not Active', 'site-pilot-ai-pro' ) . '</span>'; ?>
				</li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Render Forms tab.
	 */
	private function render_forms_tab() {
		$forms = new Spai_Forms();
		?>
		<div class="spai-pro-section">
			<h2><?php esc_html_e( 'Forms Endpoints', 'site-pilot-ai-pro' ); ?></h2>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Method', 'site-pilot-ai-pro' ); ?></th>
						<th><?php esc_html_e( 'Endpoint', 'site-pilot-ai-pro' ); ?></th>
						<th><?php esc_html_e( 'Description', 'site-pilot-ai-pro' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>GET</code></td>
						<td><code>/forms/status</code></td>
						<td><?php esc_html_e( 'Get forms plugin status', 'site-pilot-ai-pro' ); ?></td>
					</tr>
					<tr>
						<td><code>GET</code></td>
						<td><code>/forms</code></td>
						<td><?php esc_html_e( 'Get all forms from all plugins', 'site-pilot-ai-pro' ); ?></td>
					</tr>
					<tr>
						<td><code>GET</code></td>
						<td><code>/forms/{plugin}</code></td>
						<td><?php esc_html_e( 'Get forms by plugin (cf7, wpforms, gravityforms, ninjaforms)', 'site-pilot-ai-pro' ); ?></td>
					</tr>
					<tr>
						<td><code>GET</code></td>
						<td><code>/forms/{plugin}/{id}</code></td>
						<td><?php esc_html_e( 'Get single form details', 'site-pilot-ai-pro' ); ?></td>
					</tr>
					<tr>
						<td><code>GET</code></td>
						<td><code>/forms/{plugin}/{id}/entries</code></td>
						<td><?php esc_html_e( 'Get form submissions', 'site-pilot-ai-pro' ); ?></td>
					</tr>
				</tbody>
			</table>

			<h3><?php esc_html_e( 'Detected Forms Plugins', 'site-pilot-ai-pro' ); ?></h3>
			<ul>
				<li>
					<?php esc_html_e( 'Contact Form 7:', 'site-pilot-ai-pro' ); ?>
					<?php echo $forms->is_cf7_active() ? '<span class="spai-status-active">' . esc_html__( 'Active', 'site-pilot-ai-pro' ) . '</span>' : '<span class="spai-status-inactive">' . esc_html__( 'Not Active', 'site-pilot-ai-pro' ) . '</span>'; ?>
				</li>
				<li>
					<?php esc_html_e( 'WPForms:', 'site-pilot-ai-pro' ); ?>
					<?php echo $forms->is_wpforms_active() ? '<span class="spai-status-active">' . esc_html__( 'Active', 'site-pilot-ai-pro' ) . '</span>' : '<span class="spai-status-inactive">' . esc_html__( 'Not Active', 'site-pilot-ai-pro' ) . '</span>'; ?>
				</li>
				<li>
					<?php esc_html_e( 'Gravity Forms:', 'site-pilot-ai-pro' ); ?>
					<?php echo $forms->is_gravityforms_active() ? '<span class="spai-status-active">' . esc_html__( 'Active', 'site-pilot-ai-pro' ) . '</span>' : '<span class="spai-status-inactive">' . esc_html__( 'Not Active', 'site-pilot-ai-pro' ) . '</span>'; ?>
				</li>
				<li>
					<?php esc_html_e( 'Ninja Forms:', 'site-pilot-ai-pro' ); ?>
					<?php echo $forms->is_ninjaforms_active() ? '<span class="spai-status-active">' . esc_html__( 'Active', 'site-pilot-ai-pro' ) . '</span>' : '<span class="spai-status-inactive">' . esc_html__( 'Not Active', 'site-pilot-ai-pro' ) . '</span>'; ?>
				</li>
			</ul>
		</div>
		<?php
	}
}
