<?php
/**
 * Library page template.
 *
 * Variables available from render_library_page():
 *   $library_inventory      — array  filtered library items
 *   $library_filters        — array  active filter values
 *   $library_filter_options — array  available filter options (classes, styles)
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

<div class="wrap spai-admin">
	<h1 class="spai-header">
		<span class="spai-logo">
			<span class="dashicons dashicons-screenoptions"></span>
		</span>
		<?php esc_html_e( 'Library', 'site-pilot-ai' ); ?>
		<span class="spai-version">v<?php echo esc_html( SPAI_VERSION ); ?></span>
	</h1>

	<?php settings_errors( 'spai_messages' ); ?>

	<div class="spai-tab-content">
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
				<input type="hidden" name="page" value="<?php echo esc_attr( Spai_Admin::LIBRARY_PAGE_SLUG ); ?>" />
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
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=' . Spai_Admin::LIBRARY_PAGE_SLUG ) ); ?>"><?php esc_html_e( 'Reset', 'site-pilot-ai' ); ?></a>
				</p>
			</form>
		</div>

		<div class="spai-card">
			<h2><?php esc_html_e( 'Operating Sequence', 'site-pilot-ai' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'This is the operator loop mumcp is designed around. New models and humans should follow this path instead of building pages from scratch every time.', 'site-pilot-ai' ); ?>
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
							<td><?php echo esc_html( $item['modified'] ? $item['modified'] : '' ); ?></td>
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

		<?php
		/**
		 * Action for Pro add-on to render additional library cards.
		 */
		do_action( 'spai_admin_library_cards' );
		?>

	</div><!-- .spai-tab-content -->
</div><!-- .wrap -->
