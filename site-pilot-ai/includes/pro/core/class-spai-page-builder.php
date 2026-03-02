<?php
/**
 * Page Builder — Semantic Section Blueprints
 *
 * Generates valid Elementor JSON from high-level section definitions.
 *
 * @package SitePilotAI_Pro
 * @since   1.1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build pages from semantic section blueprints.
 */
class Spai_Page_Builder {

	/**
	 * Supported section types.
	 *
	 * @var array
	 */
	private static $supported_types = array(
		'hero', 'features', 'cta', 'pricing', 'faq',
		'testimonials', 'text', 'gallery',
		'contact_form', 'map', 'countdown', 'stats', 'logo_grid', 'video',
	);

	/**
	 * Build a page from section definitions.
	 *
	 * @param string $title    Page title.
	 * @param array  $sections Array of section definitions.
	 * @param string $status   Post status (default: draft).
	 * @return array|WP_Error Created page with Elementor data.
	 */
	public function build( $title, $sections, $status = 'draft' ) {
		if ( empty( $title ) ) {
			return new WP_Error( 'missing_title', __( 'Page title is required.', 'site-pilot-ai' ) );
		}

		if ( empty( $sections ) || ! is_array( $sections ) ) {
			return new WP_Error( 'missing_sections', __( 'At least one section is required.', 'site-pilot-ai' ) );
		}

		// Detect layout mode.
		$use_containers = $this->use_containers();

		// Build Elementor elements from section definitions.
		$elements = array();
		$warnings = array();

		foreach ( $sections as $i => $section ) {
			$type = isset( $section['type'] ) ? $section['type'] : '';
			if ( ! in_array( $type, self::$supported_types, true ) ) {
				$warnings[] = sprintf( 'Section %d: unknown type "%s". Supported: %s', $i, $type, implode( ', ', self::$supported_types ) );
				continue;
			}

			$method = 'build_' . $type;
			$result = $this->$method( $section, $use_containers );
			if ( $result ) {
				// Some builders return arrays of elements (features, pricing, testimonials).
				if ( isset( $result['id'] ) ) {
					$elements[] = $result;
				} elseif ( is_array( $result ) ) {
					foreach ( $result as $el ) {
						$elements[] = $el;
					}
				}
			}
		}

		if ( empty( $elements ) ) {
			return new WP_Error( 'no_valid_sections', __( 'No valid sections to build.', 'site-pilot-ai' ) );
		}

		// Create the page.
		$page_id = wp_insert_post( array(
			'post_title'  => sanitize_text_field( $title ),
			'post_status' => in_array( $status, array( 'draft', 'publish', 'private' ), true ) ? $status : 'draft',
			'post_type'   => 'page',
		) );

		if ( is_wp_error( $page_id ) ) {
			return $page_id;
		}

		// Initialize Elementor document meta so the editor recognizes the page.
		update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
		update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );

		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			update_post_meta( $page_id, '_elementor_version', ELEMENTOR_VERSION );
		}
		if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			update_post_meta( $page_id, '_elementor_pro_version', ELEMENTOR_PRO_VERSION );
		}

		// Save via Elementor Document::save() API — this rebuilds post_content
		// and regenerates CSS so the page renders on the frontend immediately.
		$save_method = 'meta_direct';
		$elementor_ok = class_exists( '\Elementor\Plugin' ) && ! empty( \Elementor\Plugin::$instance->documents );

		if ( $elementor_ok ) {
			wp_cache_delete( $page_id, 'post_meta' );
			clean_post_cache( $page_id );
			$document = \Elementor\Plugin::$instance->documents->get( $page_id, false );
			if ( $document && method_exists( $document, 'save' ) ) {
				$save_result = $document->save( array( 'elements' => $elements ) );
				if ( ! is_wp_error( $save_result ) ) {
					$save_method = 'document_save';
				}
			}
		}

		// Fallback: direct meta write if Document::save() unavailable.
		if ( 'meta_direct' === $save_method ) {
			$json = wp_json_encode( $elements );
			update_post_meta( $page_id, '_elementor_data', wp_slash( $json ) );

			// Manual CSS regeneration for fallback path.
			if ( ! empty( \Elementor\Plugin::$instance->files_manager ) ) {
				\Elementor\Plugin::$instance->files_manager->clear_cache();
			}
			delete_post_meta( $page_id, '_elementor_css' );
			wp_cache_delete( $page_id, 'post_meta' );
			clean_post_cache( $page_id );
			if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
				$css_file = \Elementor\Core\Files\CSS\Post::create( $page_id );
				$css_file->update();
			}
		}

		$page = get_post( $page_id );

		return array(
			'id'             => $page_id,
			'title'          => $page->post_title,
			'status'         => $page->post_status,
			'link'           => get_permalink( $page_id ),
			'edit_url'       => admin_url( "post.php?post={$page_id}&action=elementor" ),
			'section_count'  => count( $elements ),
			'save_method'    => $save_method,
			'warnings'       => $warnings,
		);
	}

	/**
	 * Check if site uses container (flexbox) layout.
	 *
	 * @return bool
	 */
	private function use_containers() {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			return false;
		}
		$experiments = get_option( 'elementor_experiment-container', '' );
		return in_array( $experiments, array( 'active', 'default' ), true );
	}

	/**
	 * Generate a unique 8-char element ID.
	 *
	 * @return string
	 */
	private function id() {
		return substr( bin2hex( random_bytes( 4 ) ), 0, 8 );
	}

	// ---------------------------------------------------------------
	// Section Builders
	// ---------------------------------------------------------------

	/**
	 * Build a hero section.
	 *
	 * @param array $p              Section params.
	 * @param bool  $use_containers Use container layout.
	 * @return array Elementor element.
	 */
	private function build_hero( $p, $use_containers ) {
		$heading    = isset( $p['heading'] ) ? $p['heading'] : 'Welcome';
		$subheading = isset( $p['subheading'] ) ? $p['subheading'] : '';
		$cta_text   = isset( $p['cta_text'] ) ? $p['cta_text'] : '';
		$cta_url    = isset( $p['cta_url'] ) ? $p['cta_url'] : '#';
		$background = isset( $p['background'] ) ? $p['background'] : '';
		$image_url  = isset( $p['image_url'] ) ? $p['image_url'] : '';

		$widgets = array();

		// Heading.
		$widgets[] = $this->widget( 'heading', array(
			'title'       => $heading,
			'header_size' => 'h1',
			'align'       => 'center',
			'title_color' => '#FFFFFF',
			'typography_typography' => 'custom',
			'typography_font_size'  => array( 'size' => 48, 'unit' => 'px' ),
		) );

		// Subheading.
		if ( $subheading ) {
			$widgets[] = $this->widget( 'heading', array(
				'title'       => $subheading,
				'header_size' => 'h3',
				'align'       => 'center',
				'title_color' => '#E0E0E0',
				'typography_typography' => 'custom',
				'typography_font_size'  => array( 'size' => 20, 'unit' => 'px' ),
			) );
		}

		// CTA button.
		if ( $cta_text ) {
			$widgets[] = $this->widget( 'button', array(
				'text'  => $cta_text,
				'link'  => array( 'url' => $cta_url, 'is_external' => false ),
				'align' => 'center',
				'button_type' => 'default',
				'size'  => 'lg',
			) );
		}

		// Section settings.
		$settings = array(
			'background_background' => 'classic',
			'background_color'      => '#1a1a2e',
			'padding'               => array( 'top' => '100', 'bottom' => '100', 'left' => '20', 'right' => '20', 'unit' => 'px' ),
		);

		if ( 'gradient' === $background ) {
			$settings['background_background']    = 'gradient';
			$settings['background_color']         = '#1a1a2e';
			$settings['background_color_b']       = '#16213e';
			$settings['background_gradient_type']  = 'linear';
			$settings['background_gradient_angle'] = array( 'size' => 135, 'unit' => 'deg' );
		} elseif ( $image_url ) {
			$settings['background_image'] = array( 'url' => $image_url );
			$settings['background_overlay_background'] = 'classic';
			$settings['background_overlay_color']      = 'rgba(0,0,0,0.5)';
		} elseif ( $background && '#' === substr( $background, 0, 1 ) ) {
			$settings['background_color'] = $background;
		}

		return $this->wrap_section( $widgets, $settings, $use_containers );
	}

	/**
	 * Build a features section.
	 *
	 * @param array $p              Section params.
	 * @param bool  $use_containers Use container layout.
	 * @return array Elementor element.
	 */
	private function build_features( $p, $use_containers ) {
		$heading = isset( $p['heading'] ) ? $p['heading'] : '';
		$columns = isset( $p['columns'] ) ? min( max( (int) $p['columns'], 2 ), 4 ) : 3;
		$items   = isset( $p['items'] ) && is_array( $p['items'] ) ? $p['items'] : array();

		$all_widgets = array();

		// Optional heading above the grid.
		if ( $heading ) {
			$all_widgets[] = $this->wrap_section(
				array( $this->widget( 'heading', array(
					'title'       => $heading,
					'header_size' => 'h2',
					'align'       => 'center',
				) ) ),
				array( 'padding' => array( 'top' => '60', 'bottom' => '20', 'unit' => 'px' ) ),
				$use_containers
			);
		}

		// Build columns with icon-boxes.
		if ( $use_containers ) {
			$inner_containers = array();
			foreach ( $items as $item ) {
				$inner_containers[] = array(
					'id'       => $this->id(),
					'elType'   => 'container',
					'settings' => array( 'content_width' => 'full' ),
					'elements' => array( $this->widget( 'icon-box', array(
						'selected_icon' => array( 'value' => isset( $item['icon'] ) ? $item['icon'] : 'fas fa-star', 'library' => 'fa-solid' ),
						'title_text'    => isset( $item['title'] ) ? $item['title'] : '',
						'description_text' => isset( $item['desc'] ) ? $item['desc'] : ( isset( $item['description'] ) ? $item['description'] : '' ),
						'position'      => 'top',
						'title_typography_font_size' => array( 'size' => 18, 'unit' => 'px' ),
					) ) ),
				);
			}

			$all_widgets[] = array(
				'id'       => $this->id(),
				'elType'   => 'container',
				'settings' => array(
					'flex_direction'     => 'row',
					'flex_wrap'          => 'wrap',
					'flex_gap'           => array( 'size' => 20, 'unit' => 'px' ),
					'content_width'      => 'boxed',
					'padding'            => array( 'top' => '40', 'bottom' => '60', 'left' => '20', 'right' => '20', 'unit' => 'px' ),
				),
				'elements' => $inner_containers,
			);
		} else {
			// Classic section + columns.
			$col_size  = (int) floor( 100 / $columns );
			$structure = array( 2 => '20', 3 => '30', 4 => '40' );

			$column_elements = array();
			foreach ( $items as $item ) {
				$column_elements[] = array(
					'id'       => $this->id(),
					'elType'   => 'column',
					'settings' => array( '_column_size' => $col_size ),
					'elements' => array( $this->widget( 'icon-box', array(
						'selected_icon' => array( 'value' => isset( $item['icon'] ) ? $item['icon'] : 'fas fa-star', 'library' => 'fa-solid' ),
						'title_text'    => isset( $item['title'] ) ? $item['title'] : '',
						'description_text' => isset( $item['desc'] ) ? $item['desc'] : ( isset( $item['description'] ) ? $item['description'] : '' ),
						'position'      => 'top',
						'title_typography_font_size' => array( 'size' => 18, 'unit' => 'px' ),
					) ) ),
				);
			}

			$all_widgets[] = array(
				'id'       => $this->id(),
				'elType'   => 'section',
				'settings' => array(
					'structure' => isset( $structure[ $columns ] ) ? $structure[ $columns ] : '30',
					'padding'   => array( 'top' => '40', 'bottom' => '60', 'left' => '20', 'right' => '20', 'unit' => 'px' ),
				),
				'elements' => $column_elements,
			);
		}

		return $all_widgets;
	}

	/**
	 * Build a CTA section.
	 *
	 * @param array $p              Section params.
	 * @param bool  $use_containers Use container layout.
	 * @return array Elementor element.
	 */
	private function build_cta( $p, $use_containers ) {
		$heading     = isset( $p['heading'] ) ? $p['heading'] : 'Ready to Get Started?';
		$subheading  = isset( $p['subheading'] ) ? $p['subheading'] : '';
		$button_text = isset( $p['button_text'] ) ? $p['button_text'] : 'Get Started';
		$button_url  = isset( $p['button_url'] ) ? $p['button_url'] : '#';
		$background  = isset( $p['background'] ) ? $p['background'] : '#0073aa';

		$widgets = array();

		$widgets[] = $this->widget( 'heading', array(
			'title'       => $heading,
			'header_size' => 'h2',
			'align'       => 'center',
			'title_color' => '#FFFFFF',
		) );

		if ( $subheading ) {
			$widgets[] = $this->widget( 'text-editor', array(
				'editor'        => '<p style="text-align:center;color:#E0E0E0;">' . esc_html( $subheading ) . '</p>',
				'align'         => 'center',
			) );
		}

		$widgets[] = $this->widget( 'button', array(
			'text'  => $button_text,
			'link'  => array( 'url' => $button_url, 'is_external' => false ),
			'align' => 'center',
			'size'  => 'lg',
		) );

		return $this->wrap_section( $widgets, array(
			'background_background' => 'classic',
			'background_color'      => $background,
			'padding'               => array( 'top' => '80', 'bottom' => '80', 'left' => '20', 'right' => '20', 'unit' => 'px' ),
		), $use_containers );
	}

	/**
	 * Build a pricing section.
	 *
	 * @param array $p              Section params.
	 * @param bool  $use_containers Use container layout.
	 * @return array Elementor element.
	 */
	private function build_pricing( $p, $use_containers ) {
		$heading = isset( $p['heading'] ) ? $p['heading'] : 'Pricing';
		$plans   = isset( $p['plans'] ) && is_array( $p['plans'] ) ? $p['plans'] : ( isset( $p['items'] ) ? $p['items'] : array() );

		$elements = array();

		// Heading.
		$elements[] = $this->wrap_section(
			array( $this->widget( 'heading', array(
				'title'       => $heading,
				'header_size' => 'h2',
				'align'       => 'center',
			) ) ),
			array( 'padding' => array( 'top' => '60', 'bottom' => '20', 'unit' => 'px' ) ),
			$use_containers
		);

		// Build pricing cards as price-table widgets.
		$card_widgets = array();
		foreach ( $plans as $plan ) {
			$features_list = array();
			$features = isset( $plan['features'] ) ? $plan['features'] : array();
			foreach ( $features as $feature ) {
				$features_list[] = array(
					'item_text' => is_string( $feature ) ? $feature : ( $feature['text'] ?? '' ),
				);
			}

			$card_widgets[] = $this->widget( 'price-table', array(
				'heading'           => isset( $plan['title'] ) ? $plan['title'] : ( isset( $plan['name'] ) ? $plan['name'] : 'Plan' ),
				'sub_heading'       => isset( $plan['subtitle'] ) ? $plan['subtitle'] : '',
				'price'             => isset( $plan['price'] ) ? $plan['price'] : '0',
				'period'            => isset( $plan['period'] ) ? $plan['period'] : '/mo',
				'features_list'     => $features_list,
				'button_text'       => isset( $plan['button_text'] ) ? $plan['button_text'] : 'Choose Plan',
				'link'              => array( 'url' => isset( $plan['button_url'] ) ? $plan['button_url'] : '#' ),
			) );
		}

		if ( ! empty( $card_widgets ) ) {
			$columns = min( count( $card_widgets ), 4 );
			if ( $use_containers ) {
				$inner = array();
				foreach ( $card_widgets as $w ) {
					$inner[] = array(
						'id'       => $this->id(),
						'elType'   => 'container',
						'settings' => array( 'content_width' => 'full' ),
						'elements' => array( $w ),
					);
				}
				$elements[] = array(
					'id'       => $this->id(),
					'elType'   => 'container',
					'settings' => array(
						'flex_direction' => 'row',
						'flex_wrap'      => 'wrap',
						'flex_gap'       => array( 'size' => 20, 'unit' => 'px' ),
						'content_width'  => 'boxed',
						'padding'        => array( 'top' => '20', 'bottom' => '60', 'left' => '20', 'right' => '20', 'unit' => 'px' ),
					),
					'elements' => $inner,
				);
			} else {
				$col_size  = (int) floor( 100 / $columns );
				$structure = array( 2 => '20', 3 => '30', 4 => '40' );
				$cols = array();
				foreach ( $card_widgets as $w ) {
					$cols[] = array(
						'id'       => $this->id(),
						'elType'   => 'column',
						'settings' => array( '_column_size' => $col_size ),
						'elements' => array( $w ),
					);
				}
				$elements[] = array(
					'id'       => $this->id(),
					'elType'   => 'section',
					'settings' => array(
						'structure' => isset( $structure[ $columns ] ) ? $structure[ $columns ] : '30',
						'padding'   => array( 'top' => '20', 'bottom' => '60', 'left' => '20', 'right' => '20', 'unit' => 'px' ),
					),
					'elements' => $cols,
				);
			}
		}

		return $elements;
	}

	/**
	 * Build an FAQ section.
	 *
	 * @param array $p              Section params.
	 * @param bool  $use_containers Use container layout.
	 * @return array Elementor element.
	 */
	private function build_faq( $p, $use_containers ) {
		$heading = isset( $p['heading'] ) ? $p['heading'] : 'Frequently Asked Questions';
		$items   = isset( $p['items'] ) && is_array( $p['items'] ) ? $p['items'] : array();

		$tabs = array();
		foreach ( $items as $item ) {
			$tabs[] = array(
				'tab_title'   => isset( $item['question'] ) ? $item['question'] : ( isset( $item['q'] ) ? $item['q'] : '' ),
				'tab_content' => isset( $item['answer'] ) ? $item['answer'] : ( isset( $item['a'] ) ? $item['a'] : '' ),
			);
		}

		$widgets = array();

		$widgets[] = $this->widget( 'heading', array(
			'title'       => $heading,
			'header_size' => 'h2',
			'align'       => 'center',
		) );

		if ( ! empty( $tabs ) ) {
			$widgets[] = $this->widget( 'accordion', array(
				'tabs' => $tabs,
			) );
		}

		return $this->wrap_section( $widgets, array(
			'padding'       => array( 'top' => '60', 'bottom' => '60', 'left' => '20', 'right' => '20', 'unit' => 'px' ),
			'content_width' => array( 'size' => 800, 'unit' => 'px' ),
		), $use_containers );
	}

	/**
	 * Build a testimonials section.
	 *
	 * @param array $p              Section params.
	 * @param bool  $use_containers Use container layout.
	 * @return array Elementor element.
	 */
	private function build_testimonials( $p, $use_containers ) {
		$heading = isset( $p['heading'] ) ? $p['heading'] : 'What Our Clients Say';
		$items   = isset( $p['items'] ) && is_array( $p['items'] ) ? $p['items'] : array();

		$elements = array();

		// Heading.
		$elements[] = $this->wrap_section(
			array( $this->widget( 'heading', array(
				'title'       => $heading,
				'header_size' => 'h2',
				'align'       => 'center',
			) ) ),
			array( 'padding' => array( 'top' => '60', 'bottom' => '20', 'unit' => 'px' ) ),
			$use_containers
		);

		// Testimonial cards.
		$card_widgets = array();
		foreach ( $items as $item ) {
			$card_widgets[] = $this->widget( 'testimonial', array(
				'testimonial_content' => isset( $item['text'] ) ? $item['text'] : ( isset( $item['content'] ) ? $item['content'] : '' ),
				'testimonial_name'    => isset( $item['name'] ) ? $item['name'] : '',
				'testimonial_job'     => isset( $item['title'] ) ? $item['title'] : ( isset( $item['job'] ) ? $item['job'] : '' ),
				'testimonial_image'   => isset( $item['image'] ) ? array( 'url' => $item['image'] ) : array(),
			) );
		}

		if ( ! empty( $card_widgets ) ) {
			$columns = min( count( $card_widgets ), 3 );
			if ( $use_containers ) {
				$inner = array();
				foreach ( $card_widgets as $w ) {
					$inner[] = array(
						'id'       => $this->id(),
						'elType'   => 'container',
						'settings' => array( 'content_width' => 'full' ),
						'elements' => array( $w ),
					);
				}
				$elements[] = array(
					'id'       => $this->id(),
					'elType'   => 'container',
					'settings' => array(
						'flex_direction' => 'row',
						'flex_wrap'      => 'wrap',
						'flex_gap'       => array( 'size' => 20, 'unit' => 'px' ),
						'content_width'  => 'boxed',
						'padding'        => array( 'top' => '20', 'bottom' => '60', 'left' => '20', 'right' => '20', 'unit' => 'px' ),
					),
					'elements' => $inner,
				);
			} else {
				$col_size  = (int) floor( 100 / $columns );
				$structure = array( 2 => '20', 3 => '30' );
				$cols = array();
				foreach ( $card_widgets as $w ) {
					$cols[] = array(
						'id'       => $this->id(),
						'elType'   => 'column',
						'settings' => array( '_column_size' => $col_size ),
						'elements' => array( $w ),
					);
				}
				$elements[] = array(
					'id'       => $this->id(),
					'elType'   => 'section',
					'settings' => array(
						'structure' => isset( $structure[ $columns ] ) ? $structure[ $columns ] : '30',
						'padding'   => array( 'top' => '20', 'bottom' => '60', 'left' => '20', 'right' => '20', 'unit' => 'px' ),
					),
					'elements' => $cols,
				);
			}
		}

		return $elements;
	}

	/**
	 * Build a text section.
	 *
	 * @param array $p              Section params.
	 * @param bool  $use_containers Use container layout.
	 * @return array Elementor element.
	 */
	private function build_text( $p, $use_containers ) {
		$heading = isset( $p['heading'] ) ? $p['heading'] : '';
		$content = isset( $p['content'] ) ? $p['content'] : ( isset( $p['text'] ) ? $p['text'] : '' );

		$widgets = array();

		if ( $heading ) {
			$widgets[] = $this->widget( 'heading', array(
				'title'       => $heading,
				'header_size' => isset( $p['header_size'] ) ? $p['header_size'] : 'h2',
				'align'       => isset( $p['align'] ) ? $p['align'] : 'left',
			) );
		}

		if ( $content ) {
			$widgets[] = $this->widget( 'text-editor', array(
				'editor' => $content,
			) );
		}

		return $this->wrap_section( $widgets, array(
			'padding' => array( 'top' => '40', 'bottom' => '40', 'left' => '20', 'right' => '20', 'unit' => 'px' ),
		), $use_containers );
	}

	/**
	 * Build a gallery section.
	 *
	 * @param array $p              Section params.
	 * @param bool  $use_containers Use container layout.
	 * @return array Elementor element.
	 */
	private function build_gallery( $p, $use_containers ) {
		$heading = isset( $p['heading'] ) ? $p['heading'] : '';
		$images  = isset( $p['images'] ) && is_array( $p['images'] ) ? $p['images'] : array();
		$columns = isset( $p['columns'] ) ? min( max( (int) $p['columns'], 2 ), 6 ) : 3;

		$gallery_items = array();
		foreach ( $images as $image ) {
			if ( is_string( $image ) ) {
				$gallery_items[] = array( 'url' => $image );
			} elseif ( is_array( $image ) && isset( $image['url'] ) ) {
				$gallery_items[] = $image;
			}
		}

		$widgets = array();

		if ( $heading ) {
			$widgets[] = $this->widget( 'heading', array(
				'title'       => $heading,
				'header_size' => 'h2',
				'align'       => 'center',
			) );
		}

		$widgets[] = $this->widget( 'image-gallery', array(
			'wp_gallery'     => $gallery_items,
			'gallery_columns' => $columns,
		) );

		return $this->wrap_section( $widgets, array(
			'padding' => array( 'top' => '40', 'bottom' => '40', 'left' => '20', 'right' => '20', 'unit' => 'px' ),
		), $use_containers );
	}

	/**
	 * Build a contact form section.
	 *
	 * @param array $p              Section params: heading, form_id, form_plugin (wpforms|cf7|gravity).
	 * @param bool  $use_containers Use container layout.
	 * @return array Elementor element.
	 */
	private function build_contact_form( $p, $use_containers ) {
		$heading     = isset( $p['heading'] ) ? $p['heading'] : '';
		$form_id     = isset( $p['form_id'] ) ? (int) $p['form_id'] : 0;
		$form_plugin = isset( $p['form_plugin'] ) ? $p['form_plugin'] : 'wpforms';

		$widgets = array();

		if ( $heading ) {
			$widgets[] = $this->widget( 'heading', array(
				'title'       => $heading,
				'header_size' => 'h2',
				'align'       => 'center',
			) );
		}

		// Map plugin name to Elementor widget type.
		$widget_map = array(
			'wpforms'  => 'wpforms',
			'cf7'      => 'shortcode',
			'gravity'  => 'shortcode',
		);

		$widget_type = isset( $widget_map[ $form_plugin ] ) ? $widget_map[ $form_plugin ] : 'shortcode';

		if ( 'wpforms' === $form_plugin && $form_id ) {
			$widgets[] = $this->widget( $widget_type, array( 'form_id' => (string) $form_id ) );
		} elseif ( 'cf7' === $form_plugin && $form_id ) {
			$widgets[] = $this->widget( 'shortcode', array( 'shortcode' => '[contact-form-7 id="' . $form_id . '"]' ) );
		} elseif ( 'gravity' === $form_plugin && $form_id ) {
			$widgets[] = $this->widget( 'shortcode', array( 'shortcode' => '[gravityform id="' . $form_id . '" ajax="true"]' ) );
		} else {
			$widgets[] = $this->widget( 'text-editor', array(
				'editor' => '<p style="text-align:center;color:#999;">Form placeholder — set form_id and form_plugin to embed a real form.</p>',
			) );
		}

		return $this->wrap_section( $widgets, array(
			'padding' => array( 'top' => '60', 'bottom' => '60', 'left' => '20', 'right' => '20', 'unit' => 'px' ),
		), $use_containers );
	}

	/**
	 * Build a Google Maps section.
	 *
	 * @param array $p              Section params: heading, address, zoom, height.
	 * @param bool  $use_containers Use container layout.
	 * @return array Elementor element.
	 */
	private function build_map( $p, $use_containers ) {
		$heading = isset( $p['heading'] ) ? $p['heading'] : '';
		$address = isset( $p['address'] ) ? $p['address'] : 'New York, NY';
		$zoom    = isset( $p['zoom'] ) ? min( max( (int) $p['zoom'], 1 ), 20 ) : 14;
		$height  = isset( $p['height'] ) ? min( max( (int) $p['height'], 100 ), 800 ) : 400;

		$widgets = array();

		if ( $heading ) {
			$widgets[] = $this->widget( 'heading', array(
				'title'       => $heading,
				'header_size' => 'h2',
				'align'       => 'center',
			) );
		}

		$widgets[] = $this->widget( 'google_maps', array(
			'address' => $address,
			'zoom'    => array( 'size' => $zoom, 'unit' => 'px' ),
			'height'  => array( 'size' => $height, 'unit' => 'px' ),
		) );

		return $this->wrap_section( $widgets, array(
			'padding' => array( 'top' => '40', 'bottom' => '40', 'left' => '0', 'right' => '0', 'unit' => 'px' ),
		), $use_containers );
	}

	/**
	 * Build a countdown section.
	 *
	 * @param array $p              Section params: heading, subheading, due_date (Y-m-d H:i).
	 * @param bool  $use_containers Use container layout.
	 * @return array Elementor element.
	 */
	private function build_countdown( $p, $use_containers ) {
		$heading    = isset( $p['heading'] ) ? $p['heading'] : '';
		$subheading = isset( $p['subheading'] ) ? $p['subheading'] : '';
		$due_date   = isset( $p['due_date'] ) ? $p['due_date'] : gmdate( 'Y-m-d H:i', strtotime( '+30 days' ) );

		$widgets = array();

		if ( $heading ) {
			$widgets[] = $this->widget( 'heading', array(
				'title'       => $heading,
				'header_size' => 'h2',
				'align'       => 'center',
			) );
		}

		if ( $subheading ) {
			$widgets[] = $this->widget( 'text-editor', array(
				'editor' => '<p style="text-align:center;">' . esc_html( $subheading ) . '</p>',
			) );
		}

		$widgets[] = $this->widget( 'countdown', array(
			'countdown_type' => 'due_date',
			'due_date'       => $due_date,
		) );

		return $this->wrap_section( $widgets, array(
			'padding'              => array( 'top' => '60', 'bottom' => '60', 'left' => '20', 'right' => '20', 'unit' => 'px' ),
			'background_background' => 'classic',
			'background_color'     => '#1a1a2e',
			'_element_custom_width' => array( 'size' => '', 'unit' => '%' ),
		), $use_containers );
	}

	/**
	 * Build a stats / numbers section.
	 *
	 * @param array $p              Section params: heading, items[{number, suffix, title, duration}].
	 * @param bool  $use_containers Use container layout.
	 * @return array Elementor element.
	 */
	private function build_stats( $p, $use_containers ) {
		$heading = isset( $p['heading'] ) ? $p['heading'] : '';
		$items   = isset( $p['items'] ) && is_array( $p['items'] ) ? $p['items'] : array();
		$columns = count( $items ) > 4 ? 4 : max( count( $items ), 2 );

		$all_widgets = array();

		if ( $heading ) {
			$all_widgets[] = $this->wrap_section(
				array( $this->widget( 'heading', array(
					'title'       => $heading,
					'header_size' => 'h2',
					'align'       => 'center',
				) ) ),
				array( 'padding' => array( 'top' => '60', 'bottom' => '20', 'unit' => 'px' ) ),
				$use_containers
			);
		}

		if ( $use_containers ) {
			$inner_containers = array();
			foreach ( $items as $item ) {
				$inner_containers[] = array(
					'id'       => $this->id(),
					'elType'   => 'container',
					'settings' => array( 'content_width' => 'full' ),
					'elements' => array( $this->widget( 'counter', array(
						'starting_number' => 0,
						'ending_number'   => isset( $item['number'] ) ? (int) $item['number'] : 0,
						'suffix'          => isset( $item['suffix'] ) ? $item['suffix'] : '',
						'title'           => isset( $item['title'] ) ? $item['title'] : '',
						'duration'        => isset( $item['duration'] ) ? (int) $item['duration'] : 2000,
					) ) ),
				);
			}

			$all_widgets[] = array(
				'id'       => $this->id(),
				'elType'   => 'container',
				'settings' => array(
					'flex_direction' => 'row',
					'flex_wrap'      => 'wrap',
					'flex_gap'       => array( 'size' => 20, 'unit' => 'px' ),
					'content_width'  => 'boxed',
					'padding'        => array( 'top' => '40', 'bottom' => '60', 'left' => '20', 'right' => '20', 'unit' => 'px' ),
				),
				'elements' => $inner_containers,
			);
		} else {
			$col_size  = (int) floor( 100 / $columns );
			$structure = array( 2 => '20', 3 => '30', 4 => '40' );

			$column_elements = array();
			foreach ( $items as $item ) {
				$column_elements[] = array(
					'id'       => $this->id(),
					'elType'   => 'column',
					'settings' => array( '_column_size' => $col_size ),
					'elements' => array( $this->widget( 'counter', array(
						'starting_number' => 0,
						'ending_number'   => isset( $item['number'] ) ? (int) $item['number'] : 0,
						'suffix'          => isset( $item['suffix'] ) ? $item['suffix'] : '',
						'title'           => isset( $item['title'] ) ? $item['title'] : '',
						'duration'        => isset( $item['duration'] ) ? (int) $item['duration'] : 2000,
					) ) ),
				);
			}

			$all_widgets[] = array(
				'id'       => $this->id(),
				'elType'   => 'section',
				'settings' => array(
					'structure' => isset( $structure[ $columns ] ) ? $structure[ $columns ] : '30',
					'padding'   => array( 'top' => '40', 'bottom' => '60', 'left' => '20', 'right' => '20', 'unit' => 'px' ),
				),
				'elements' => $column_elements,
			);
		}

		return $all_widgets;
	}

	/**
	 * Build a logo grid section.
	 *
	 * @param array $p              Section params: heading, logos[{url, alt, link}], columns.
	 * @param bool  $use_containers Use container layout.
	 * @return array Elementor element(s).
	 */
	private function build_logo_grid( $p, $use_containers ) {
		$heading = isset( $p['heading'] ) ? $p['heading'] : '';
		$logos   = isset( $p['logos'] ) && is_array( $p['logos'] ) ? $p['logos'] : array();
		$columns = isset( $p['columns'] ) ? min( max( (int) $p['columns'], 2 ), 6 ) : 4;

		$all_widgets = array();

		if ( $heading ) {
			$all_widgets[] = $this->wrap_section(
				array( $this->widget( 'heading', array(
					'title'       => $heading,
					'header_size' => 'h2',
					'align'       => 'center',
				) ) ),
				array( 'padding' => array( 'top' => '60', 'bottom' => '20', 'unit' => 'px' ) ),
				$use_containers
			);
		}

		if ( $use_containers ) {
			$inner_containers = array();
			foreach ( $logos as $logo ) {
				$img_url = is_string( $logo ) ? $logo : ( isset( $logo['url'] ) ? $logo['url'] : '' );
				$alt     = is_array( $logo ) && isset( $logo['alt'] ) ? $logo['alt'] : '';
				$link    = is_array( $logo ) && isset( $logo['link'] ) ? $logo['link'] : '';

				$settings = array(
					'image'      => array( 'url' => $img_url ),
					'image_size' => 'medium',
					'align'      => 'center',
					'caption_source' => 'none',
				);
				if ( $alt ) {
					$settings['image']['alt'] = $alt;
				}
				if ( $link ) {
					$settings['link_to'] = 'custom';
					$settings['link']    = array( 'url' => $link, 'is_external' => true );
				}

				$inner_containers[] = array(
					'id'       => $this->id(),
					'elType'   => 'container',
					'settings' => array( 'content_width' => 'full' ),
					'elements' => array( $this->widget( 'image', $settings ) ),
				);
			}

			$all_widgets[] = array(
				'id'       => $this->id(),
				'elType'   => 'container',
				'settings' => array(
					'flex_direction' => 'row',
					'flex_wrap'      => 'wrap',
					'flex_gap'       => array( 'size' => 30, 'unit' => 'px' ),
					'content_width'  => 'boxed',
					'padding'        => array( 'top' => '40', 'bottom' => '60', 'left' => '20', 'right' => '20', 'unit' => 'px' ),
				),
				'elements' => $inner_containers,
			);
		} else {
			$col_size  = (int) floor( 100 / $columns );
			$structure = array( 2 => '20', 3 => '30', 4 => '40' );

			$column_elements = array();
			foreach ( $logos as $logo ) {
				$img_url = is_string( $logo ) ? $logo : ( isset( $logo['url'] ) ? $logo['url'] : '' );
				$alt     = is_array( $logo ) && isset( $logo['alt'] ) ? $logo['alt'] : '';
				$link    = is_array( $logo ) && isset( $logo['link'] ) ? $logo['link'] : '';

				$settings = array(
					'image'      => array( 'url' => $img_url ),
					'image_size' => 'medium',
					'align'      => 'center',
					'caption_source' => 'none',
				);
				if ( $alt ) {
					$settings['image']['alt'] = $alt;
				}
				if ( $link ) {
					$settings['link_to'] = 'custom';
					$settings['link']    = array( 'url' => $link, 'is_external' => true );
				}

				$column_elements[] = array(
					'id'       => $this->id(),
					'elType'   => 'column',
					'settings' => array( '_column_size' => $col_size ),
					'elements' => array( $this->widget( 'image', $settings ) ),
				);
			}

			$all_widgets[] = array(
				'id'       => $this->id(),
				'elType'   => 'section',
				'settings' => array(
					'structure' => isset( $structure[ $columns ] ) ? $structure[ $columns ] : '40',
					'padding'   => array( 'top' => '40', 'bottom' => '60', 'left' => '20', 'right' => '20', 'unit' => 'px' ),
				),
				'elements' => $column_elements,
			);
		}

		return $all_widgets;
	}

	/**
	 * Build a video section.
	 *
	 * @param array $p              Section params: heading, subheading, video_url.
	 * @param bool  $use_containers Use container layout.
	 * @return array Elementor element.
	 */
	private function build_video( $p, $use_containers ) {
		$heading    = isset( $p['heading'] ) ? $p['heading'] : '';
		$subheading = isset( $p['subheading'] ) ? $p['subheading'] : '';
		$video_url  = isset( $p['video_url'] ) ? $p['video_url'] : '';

		$widgets = array();

		if ( $heading ) {
			$widgets[] = $this->widget( 'heading', array(
				'title'       => $heading,
				'header_size' => 'h2',
				'align'       => 'center',
			) );
		}

		if ( $subheading ) {
			$widgets[] = $this->widget( 'text-editor', array(
				'editor' => '<p style="text-align:center;">' . esc_html( $subheading ) . '</p>',
			) );
		}

		// Detect video type from URL.
		$video_type = 'youtube';
		$settings   = array();

		if ( false !== strpos( $video_url, 'vimeo.com' ) ) {
			$video_type = 'vimeo';
			$settings['vimeo_url'] = $video_url;
		} elseif ( false !== strpos( $video_url, 'youtube.com' ) || false !== strpos( $video_url, 'youtu.be' ) ) {
			$video_type = 'youtube';
			$settings['youtube_url'] = $video_url;
		} else {
			$video_type = 'hosted';
			$settings['hosted_url'] = array( 'url' => $video_url );
		}

		$settings['video_type'] = $video_type;

		$widgets[] = $this->widget( 'video', $settings );

		return $this->wrap_section( $widgets, array(
			'padding' => array( 'top' => '60', 'bottom' => '60', 'left' => '20', 'right' => '20', 'unit' => 'px' ),
		), $use_containers );
	}

	// ---------------------------------------------------------------
	// Helpers
	// ---------------------------------------------------------------

	/**
	 * Create a widget element.
	 *
	 * @param string $type     Widget type.
	 * @param array  $settings Widget settings.
	 * @return array Widget element.
	 */
	private function widget( $type, $settings = array() ) {
		return array(
			'id'         => $this->id(),
			'elType'     => 'widget',
			'widgetType' => $type,
			'settings'   => $settings,
			'elements'   => array(),
		);
	}

	/**
	 * Wrap widgets in a section/container.
	 *
	 * @param array $widgets        Widget elements.
	 * @param array $settings       Section settings.
	 * @param bool  $use_containers Use container layout.
	 * @return array Section or container element.
	 */
	private function wrap_section( $widgets, $settings = array(), $use_containers = false ) {
		if ( $use_containers ) {
			return array(
				'id'       => $this->id(),
				'elType'   => 'container',
				'settings' => $settings,
				'elements' => $widgets,
			);
		}

		// Classic: section > column > widgets.
		return array(
			'id'       => $this->id(),
			'elType'   => 'section',
			'settings' => $settings,
			'elements' => array(
				array(
					'id'       => $this->id(),
					'elType'   => 'column',
					'settings' => array( '_column_size' => 100 ),
					'elements' => $widgets,
				),
			),
		);
	}
}
