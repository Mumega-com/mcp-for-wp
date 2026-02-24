<?php
/**
 * Guides & Documentation
 *
 * Provides context-aware guides for AI assistants on how to use
 * Site Pilot AI tools effectively.
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Guides class.
 *
 * Returns detailed, topic-specific guides for AI assistants.
 * Topics are dynamically filtered based on active plugins.
 */
class Spai_Guides {

	/**
	 * Get all available guide topics with descriptions.
	 *
	 * Filters topics based on active plugins/capabilities.
	 *
	 * @return array List of available topics.
	 */
	public static function get_topics() {
		$core         = new Spai_Core();
		$capabilities = $core->get_capabilities();

		$topics = array(
			array(
				'topic'       => 'elementor',
				'title'       => 'Elementor Page Builder',
				'description' => 'Widget reference, layout modes (section vs container), nesting rules, responsive breakpoints, and common widget settings.',
				'requires'    => 'elementor',
			),
			array(
				'topic'       => 'seo',
				'title'       => 'SEO Management',
				'description' => 'Yoast vs RankMath field mapping, bulk SEO operations, noindex/nofollow, Open Graph and Twitter meta.',
				'requires'    => 'seo',
			),
			array(
				'topic'       => 'menus',
				'title'       => 'Navigation Menus',
				'description' => 'Menu structure, theme locations, item types (custom link, page, category), nesting and ordering.',
				'requires'    => null,
			),
			array(
				'topic'       => 'media',
				'title'       => 'Media Management',
				'description' => 'Upload methods (file, URL, base64), supported formats, featured images, and media library management.',
				'requires'    => null,
			),
			array(
				'topic'       => 'content',
				'title'       => 'Content Management',
				'description' => 'Post types, taxonomies, block editor vs classic, custom fields, bulk operations, and content search.',
				'requires'    => null,
			),
			array(
				'topic'       => 'forms',
				'title'       => 'Forms Integration',
				'description' => 'CF7, WPForms, and Gravity Forms detection, listing, inspection, and embedding via Elementor.',
				'requires'    => 'forms',
			),
			array(
				'topic'       => 'woocommerce',
				'title'       => 'WooCommerce',
				'description' => 'Products, orders, and categories management via Site Pilot AI tools.',
				'requires'    => 'woocommerce',
			),
			array(
				'topic'       => 'workflows',
				'title'       => 'Workflow Templates',
				'description' => 'Step-by-step guides for common tasks: building landing pages, SEO audits, site redesign, menu setup, and more.',
				'requires'    => null,
			),
			array(
				'topic'       => 'troubleshooting',
				'title'       => 'Troubleshooting',
				'description' => 'Common errors, debugging tips, and fixes for frequent issues with Site Pilot AI tools.',
				'requires'    => null,
			),
		);

		// Filter based on active capabilities.
		$has_seo   = ! empty( $capabilities['yoast'] )
			|| ! empty( $capabilities['rankmath'] )
			|| ! empty( $capabilities['aioseo'] )
			|| ! empty( $capabilities['seopress'] );
		$has_forms = ! empty( $capabilities['cf7'] )
			|| ! empty( $capabilities['wpforms'] )
			|| ! empty( $capabilities['gravityforms'] )
			|| ! empty( $capabilities['ninjaforms'] );

		$capability_map = array(
			'elementor'   => ! empty( $capabilities['elementor'] ),
			'seo'         => $has_seo,
			'forms'       => $has_forms,
			'woocommerce' => ! empty( $capabilities['woocommerce'] ),
		);

		$filtered = array();
		foreach ( $topics as $topic ) {
			$req = $topic['requires'];
			if ( null === $req || ( isset( $capability_map[ $req ] ) && $capability_map[ $req ] ) ) {
				unset( $topic['requires'] );
				$filtered[] = $topic;
			}
		}

		return $filtered;
	}

	/**
	 * Get a guide by topic.
	 *
	 * @param string $topic Topic slug.
	 * @return array|WP_Error Guide content or error.
	 */
	public static function get_guide( $topic ) {
		$method = 'guide_' . $topic;

		if ( ! method_exists( __CLASS__, $method ) ) {
			return new WP_Error(
				'invalid_topic',
				sprintf( 'Unknown guide topic: %s. Call wp_get_guide() with no topic to see available topics.', $topic ),
				array( 'status' => 404 )
			);
		}

		return call_user_func( array( __CLASS__, $method ) );
	}

	/**
	 * Elementor guide.
	 *
	 * @return array Guide content.
	 */
	public static function guide_elementor() {
		$core         = new Spai_Core();
		$capabilities = $core->get_capabilities();
		$layout_mode  = isset( $capabilities['elementor_layout_mode'] ) ? $capabilities['elementor_layout_mode'] : 'section';

		return array(
			'topic'   => 'elementor',
			'title'   => 'Elementor Page Builder Guide',
			'layout_mode' => $layout_mode,
			'sections' => array(
				array(
					'heading' => 'Layout Modes',
					'content' => 'Elementor has two layout modes. This site uses: ' . $layout_mode . ".\n\n"
						. "**Section mode (classic):** section -> column(s) -> widget(s)\n"
						. "**Container mode (flexbox):** container -> container(s) -> widget(s)\n\n"
						. "Always check the layout mode via wp_site_info or wp_introspect before building pages. Using the wrong mode will cause rendering failures.",
				),
				array(
					'heading' => 'Element Structure',
					'content' => "Every element MUST have an `id` (8-char alphanumeric). The plugin auto-generates missing IDs but it is best practice to provide them.\n\n"
						. "**Section mode structure:**\n"
						. "```json\n"
						. "[{\n"
						. "  \"id\": \"sec12345\", \"elType\": \"section\",\n"
						. "  \"settings\": {},\n"
						. "  \"elements\": [{\n"
						. "    \"id\": \"col12345\", \"elType\": \"column\",\n"
						. "    \"settings\": {\"_column_size\": 100},\n"
						. "    \"elements\": [{ \"id\": \"wid12345\", \"elType\": \"widget\", \"widgetType\": \"heading\", \"settings\": {\"title\": \"Hello\"} }]\n"
						. "  }]\n"
						. "}]\n"
						. "```\n\n"
						. "**Container mode structure:**\n"
						. "```json\n"
						. "[{\n"
						. "  \"id\": \"con12345\", \"elType\": \"container\",\n"
						. "  \"settings\": {\"flex_direction\": \"row\"},\n"
						. "  \"elements\": [{ \"id\": \"wid12345\", \"elType\": \"widget\", \"widgetType\": \"heading\", \"settings\": {\"title\": \"Hello\"} }]\n"
						. "}]\n"
						. "```",
				),
				array(
					'heading' => 'Multi-Column Sections',
					'content' => "In section mode, multi-column layouts need the `structure` setting on the section:\n"
						. "- 2 columns: `\"structure\": \"20\"` with `_column_size` of 50 + 50\n"
						. "- 3 columns: `\"structure\": \"30\"` with `_column_size` of 33 + 33 + 33\n"
						. "- 4 columns: `\"structure\": \"40\"` with `_column_size` of 25 + 25 + 25 + 25\n\n"
						. "Column sizes MUST sum to 100.",
				),
				array(
					'heading' => 'Common Widget Types',
					'content' => "| Widget | widgetType | Key Settings |\n"
						. "|--------|-----------|---------------|\n"
						. "| Heading | `heading` | title, header_size (h1-h6), align |\n"
						. "| Text Editor | `text-editor` | editor (HTML content) |\n"
						. "| Image | `image` | image.url, image.id, image_size |\n"
						. "| Button | `button` | text, link.url, link.is_external, size, button_type |\n"
						. "| Icon | `icon` | selected_icon.value, selected_icon.library |\n"
						. "| Spacer | `spacer` | space.size, space.unit |\n"
						. "| Divider | `divider` | style, weight, color, width |\n"
						. "| Image Box | `image-box` | image.url, title_text, description_text |\n"
						. "| Icon Box | `icon-box` | selected_icon, title_text, description_text |\n"
						. "| Star Rating | `star-rating` | rating.value, star_style |\n"
						. "| Counter | `counter` | starting_number, ending_number, prefix, suffix |\n"
						. "| Progress Bar | `progress-bar` | title, percent |\n"
						. "| Tabs | `tabs` | tabs[].tab_title, tabs[].tab_content |\n"
						. "| Accordion | `accordion` | tabs[].tab_title, tabs[].tab_content |\n"
						. "| Video | `video` | video_type, youtube_url, vimeo_url |\n"
						. "| Google Maps | `google_maps` | address, zoom.size |\n"
						. "| Form | `form` | form_name, form_fields[] |\n\n"
						. "Use `wp_get_elementor_widgets` to list all available widgets on this site.\n"
						. "Use `wp_get_widget_schema(widget_type=\"heading\")` to get the full schema for a specific widget.",
				),
				array(
					'heading' => 'Responsive Breakpoints',
					'content' => "Elementor supports responsive settings via suffixes:\n"
						. "- Desktop (default): `align`, `padding`\n"
						. "- Tablet: `align_tablet`, `padding_tablet`\n"
						. "- Mobile: `align_mobile`, `padding_mobile`\n\n"
						. "Hide on specific devices:\n"
						. "```json\n"
						. "{\"hide_desktop\": \"yes\", \"hide_tablet\": \"\", \"hide_mobile\": \"\"}\n"
						. "```",
				),
				array(
					'heading' => 'Background & Styling',
					'content' => "Common styling keys:\n"
						. "- Background: `background_background: \"classic\"`, `background_color: \"#FFFFFF\"`\n"
						. "- Background image: `background_image.url`, `background_position`, `background_size`\n"
						. "- Background overlay: `background_overlay_background: \"classic\"`, `background_overlay_color`\n"
						. "- Padding: `padding: {top: \"40\", right: \"20\", bottom: \"40\", left: \"20\", unit: \"px\"}`\n"
						. "- Margin: `margin: {top: \"0\", bottom: \"0\", unit: \"px\"}`\n"
						. "- Typography: `typography_typography: \"custom\"`, `typography_font_family`, `typography_font_size`, `typography_font_weight`\n"
						. "- Color: `title_color`, `text_color`, `color` (varies by widget)",
				),
				array(
					'heading' => 'Relevant Tools',
					'content' => "- `wp_get_elementor(id)` — Get Elementor data for a page\n"
						. "- `wp_get_elementor_summary(id)` — Get a compact summary of the page structure\n"
						. "- `wp_set_elementor(id, elementor_data)` — Set full Elementor data for a page\n"
						. "- `wp_edit_section(id, section_id, elements)` — Replace a single section/container\n"
						. "- `wp_edit_widget(id, widget_id, settings)` — Update a single widget's settings\n"
						. "- `wp_get_elementor_widgets()` — List all registered widgets\n"
						. "- `wp_get_widget_schema(widget_type)` — Get the full control schema for a widget\n"
						. "- `wp_elementor_status()` — Check Elementor version and configuration\n"
						. "- `wp_preview_elementor(id)` — Get a rendered preview of the page\n"
						. "- `wp_regenerate_elementor_css()` — Force CSS regeneration after changes\n"
						. "- `wp_bulk_find_replace(id, search, replace)` — Find and replace in Elementor data",
				),
			),
		);
	}

	/**
	 * SEO guide.
	 *
	 * @return array Guide content.
	 */
	public static function guide_seo() {
		$core         = new Spai_Core();
		$capabilities = $core->get_capabilities();

		$active_plugin = 'none';
		if ( ! empty( $capabilities['yoast'] ) ) {
			$active_plugin = 'yoast';
		} elseif ( ! empty( $capabilities['rankmath'] ) ) {
			$active_plugin = 'rankmath';
		} elseif ( ! empty( $capabilities['aioseo'] ) ) {
			$active_plugin = 'aioseo';
		} elseif ( ! empty( $capabilities['seopress'] ) ) {
			$active_plugin = 'seopress';
		}

		return array(
			'topic'        => 'seo',
			'title'        => 'SEO Management Guide',
			'active_plugin' => $active_plugin,
			'sections'     => array(
				array(
					'heading' => 'SEO Plugin Detection',
					'content' => 'Active SEO plugin: **' . $active_plugin . "**\n\n"
						. "Site Pilot AI auto-detects and normalizes SEO fields across plugins. The `wp_get_seo` and `wp_set_seo` tools work with any supported SEO plugin.\n\n"
						. "Use `wp_detect_plugins()` to confirm which SEO plugin is active.",
				),
				array(
					'heading' => 'Field Mapping (Normalized)',
					'content' => "Site Pilot AI normalizes SEO fields so you can use the same keys regardless of plugin:\n\n"
						. "| Field | Description |\n"
						. "|-------|-------------|\n"
						. "| `title` | SEO title / meta title |\n"
						. "| `description` | Meta description |\n"
						. "| `focus_keyword` | Primary keyword / keyphrase |\n"
						. "| `noindex` | Prevent search engine indexing (boolean) |\n"
						. "| `nofollow` | Prevent link following (boolean) |\n"
						. "| `canonical_url` | Canonical URL override |\n"
						. "| `og_title` | Open Graph title |\n"
						. "| `og_description` | Open Graph description |\n"
						. "| `og_image` | Open Graph image URL |\n"
						. "| `twitter_title` | Twitter card title |\n"
						. "| `twitter_description` | Twitter card description |\n"
						. "| `twitter_image` | Twitter card image URL |",
				),
				array(
					'heading' => 'Bulk SEO Operations',
					'content' => "Use `wp_bulk_seo` to update SEO fields for multiple posts/pages at once:\n"
						. "```json\n"
						. "wp_bulk_seo(items=[\n"
						. "  {\"id\": 10, \"title\": \"My Page Title\", \"description\": \"Page description\"},\n"
						. "  {\"id\": 20, \"title\": \"Another Page\", \"noindex\": true}\n"
						. "])\n"
						. "```\n\n"
						. "Use `wp_analyze_seo(id)` to get an SEO analysis and score for a specific page.\n"
						. "Use `wp_seo_status()` to see which SEO plugin is active and its version.",
				),
				array(
					'heading' => 'Noindex / Nofollow',
					'content' => "To prevent a page from appearing in search engines:\n"
						. "```json\n"
						. "wp_set_seo(id=123, noindex=true)\n"
						. "```\n\n"
						. "For site-wide noindex (e.g., staging sites):\n"
						. "```json\n"
						. "wp_set_noindex(noindex=true)\n"
						. "```\n\n"
						. "Use `wp_update_options(blog_public=false)` to discourage search engines via WordPress settings.",
				),
				array(
					'heading' => 'Relevant Tools',
					'content' => "- `wp_get_seo(id)` — Get SEO meta for a post/page\n"
						. "- `wp_set_seo(id, ...)` — Set SEO meta fields\n"
						. "- `wp_analyze_seo(id)` — Analyze SEO and get score/recommendations\n"
						. "- `wp_bulk_seo(items)` — Bulk update SEO for multiple items\n"
						. "- `wp_seo_status()` — Check active SEO plugin and config\n"
						. "- `wp_set_noindex(noindex)` — Set site-wide noindex",
				),
			),
		);
	}

	/**
	 * Menus guide.
	 *
	 * @return array Guide content.
	 */
	public static function guide_menus() {
		return array(
			'topic'    => 'menus',
			'title'    => 'Navigation Menus Guide',
			'sections' => array(
				array(
					'heading' => 'Menu Structure',
					'content' => "WordPress menus consist of:\n"
						. "- **Menu** — a named collection of items (e.g., \"Main Menu\", \"Footer Menu\")\n"
						. "- **Menu Location** — a theme-defined slot (e.g., \"primary\", \"footer\")\n"
						. "- **Menu Item** — a link in the menu (page, post, category, custom URL)\n\n"
						. "Menus are assigned to locations. A location can have one menu, but a menu can be assigned to multiple locations.",
				),
				array(
					'heading' => 'Menu Item Types',
					'content' => "| Type | Description | Required Fields |\n"
						. "|------|-------------|------------------|\n"
						. "| `custom` | Custom URL link | title, url |\n"
						. "| `post_type` | Link to a page/post | title, object (\"page\"/\"post\"), object_id |\n"
						. "| `taxonomy` | Link to a category/tag | title, object (\"category\"/\"post_tag\"), object_id |",
				),
				array(
					'heading' => 'Sub-menus (Nesting)',
					'content' => "Create sub-menu items by setting `parent_id` to the ID of the parent item:\n"
						. "```json\n"
						. "wp_add_menu_item(menu_id=5, title=\"Services\", type=\"custom\", url=\"/services\")\n"
						. "// Returns item_id: 101\n"
						. "wp_add_menu_item(menu_id=5, title=\"Web Design\", type=\"custom\", url=\"/services/web-design\", parent_id=101)\n"
						. "```\n\n"
						. "Most themes support 2-3 levels of nesting. Deeper nesting may not render properly.",
				),
				array(
					'heading' => 'Quick Setup',
					'content' => "Use `wp_setup_menu` for a one-shot menu creation:\n"
						. "```json\n"
						. "wp_setup_menu(name=\"Main Menu\", location=\"primary\", page_ids=[10, 20, 30])\n"
						. "```\n\n"
						. "This creates the menu, adds the specified pages as items, and assigns it to the location.",
				),
				array(
					'heading' => 'Relevant Tools',
					'content' => "- `wp_list_menus()` — List all menus\n"
						. "- `wp_list_menu_locations()` — List theme locations and assigned menus\n"
						. "- `wp_setup_menu(name, location, page_ids)` — Quick menu setup\n"
						. "- `wp_list_menu_items(menu_id)` — List items in a menu\n"
						. "- `wp_add_menu_item(menu_id, title, ...)` — Add an item\n"
						. "- `wp_update_menu_item(menu_id, item_id, ...)` — Update an item\n"
						. "- `wp_delete_menu_item(menu_id, item_id)` — Remove an item\n"
						. "- `wp_reorder_menu_items(menu_id, items)` — Reorder items\n"
						. "- `wp_delete_menu(menu_id)` — Delete a menu\n"
						. "- `wp_assign_menu_location(menu_id, location)` — Assign menu to location",
				),
			),
		);
	}

	/**
	 * Media guide.
	 *
	 * @return array Guide content.
	 */
	public static function guide_media() {
		return array(
			'topic'    => 'media',
			'title'    => 'Media Management Guide',
			'sections' => array(
				array(
					'heading' => 'Upload Methods',
					'content' => "Site Pilot AI supports three upload methods:\n\n"
						. "1. **From URL** (recommended for AI): `wp_upload_media_from_url(url=\"https://example.com/photo.jpg\")`\n"
						. "2. **Base64**: `wp_upload_media_b64(data=\"/9j/4AAQ...\", filename=\"photo.jpg\", mime_type=\"image/jpeg\")`\n"
						. "3. **File upload**: `wp_upload_media(file=...)` — multipart form data\n\n"
						. "URL uploads are the simplest for AI assistants. The plugin downloads the file and adds it to the media library.",
				),
				array(
					'heading' => 'Supported Formats',
					'content' => "WordPress supports these formats by default:\n"
						. "- **Images**: jpg, jpeg, png, gif, webp, svg (if enabled), ico\n"
						. "- **Documents**: pdf, doc, docx, ppt, pptx, odt, xls, xlsx\n"
						. "- **Audio**: mp3, ogg, wav, m4a\n"
						. "- **Video**: mp4, m4v, mov, wmv, avi, webm, ogv\n\n"
						. "SVG support depends on plugins or theme. Maximum upload size varies by server config.",
				),
				array(
					'heading' => 'Featured Images',
					'content' => "Set a featured image (thumbnail) for any post or page:\n"
						. "```json\n"
						. "// Upload first, then set as featured\n"
						. "wp_upload_media_from_url(url=\"https://example.com/hero.jpg\")\n"
						. "// Returns: {id: 456, url: \"...\"}\n"
						. "wp_set_featured_image(id=123, image_id=456)\n"
						. "```\n\n"
						. "Or set during page/post creation:\n"
						. "```json\n"
						. "wp_create_page(title=\"My Page\", featured_media=456)\n"
						. "```",
				),
				array(
					'heading' => 'Stock Photos & AI Images',
					'content' => "If integrations are configured:\n"
						. "- `wp_search_stock_photos(query)` — Search Pexels stock photos\n"
						. "- `wp_download_stock_photo(photo_id)` — Download and add to media library\n"
						. "- `wp_generate_image(prompt)` — Generate an image with AI (DALL-E)\n"
						. "- `wp_generate_featured_image(id, prompt)` — Generate and set as featured image\n"
						. "- `wp_generate_alt_text(id)` — Auto-generate alt text for an image\n"
						. "- `wp_describe_image(id)` — Get AI description of an image\n\n"
						. "Use `wp_integrations_status()` to check if these integrations are configured.",
				),
				array(
					'heading' => 'Relevant Tools',
					'content' => "- `wp_list_media(per_page, page, mime_type)` — List media library items\n"
						. "- `wp_upload_media_from_url(url)` — Upload from URL\n"
						. "- `wp_upload_media_b64(data, filename, mime_type)` — Upload from base64\n"
						. "- `wp_upload_media(file)` — Upload file\n"
						. "- `wp_delete_media(id)` — Delete media item\n"
						. "- `wp_set_featured_image(id, image_id)` — Set featured image\n"
						. "- `wp_screenshot_url(url)` — Take a screenshot of a URL",
				),
			),
		);
	}

	/**
	 * Content guide.
	 *
	 * @return array Guide content.
	 */
	public static function guide_content() {
		return array(
			'topic'    => 'content',
			'title'    => 'Content Management Guide',
			'sections' => array(
				array(
					'heading' => 'Post Types',
					'content' => "WordPress has built-in and custom post types:\n\n"
						. "| Type | Tool Prefix | Description |\n"
						. "|------|------------|-------------|\n"
						. "| `post` | `wp_list_posts`, `wp_create_post` | Blog posts |\n"
						. "| `page` | `wp_list_pages`, `wp_create_page` | Static pages |\n"
						. "| (custom) | `wp_list_content(post_type=...)` | Products, courses, etc. |\n\n"
						. "Use `wp_detect_plugins()` to discover registered custom post types.",
				),
				array(
					'heading' => 'Taxonomies',
					'content' => "Taxonomies organize content:\n"
						. "- **Categories** — hierarchical (parent/child). Use `wp_list_categories()`.\n"
						. "- **Tags** — flat labels. Use `wp_list_tags()`.\n"
						. "- **Custom taxonomies** — registered by plugins (e.g., product_cat for WooCommerce).\n\n"
						. "Manage terms with `wp_create_term`, `wp_update_term`, `wp_delete_term`.",
				),
				array(
					'heading' => 'Block Editor vs Classic',
					'content' => "WordPress 5.0+ uses the Gutenberg block editor by default.\n\n"
						. "- Use `wp_get_blocks(id)` and `wp_set_blocks(id, blocks)` for block content.\n"
						. "- Use `wp_list_block_types()` to see available blocks.\n"
						. "- If Classic Editor is active, use `content` field in `wp_create_post/page`.\n\n"
						. "**Elementor pages bypass both editors.** Use `wp_set_elementor()` instead.",
				),
				array(
					'heading' => 'Custom Fields (Post Meta)',
					'content' => "Read and write custom fields with:\n"
						. "```json\n"
						. "wp_get_post_meta(id=123)\n"
						. "// Returns all meta keys and values\n\n"
						. "wp_set_post_meta(id=123, meta_key=\"my_field\", meta_value=\"hello\")\n"
						. "```\n\n"
						. "Common meta keys vary by theme and plugins. Use `wp_get_post_meta` first to discover existing keys.",
				),
				array(
					'heading' => 'Bulk Operations',
					'content' => "- `wp_bulk_create_pages(pages=[...])` — Create multiple pages at once\n"
						. "- `wp_bulk_create_posts(posts=[...])` — Create multiple posts at once\n"
						. "- `wp_bulk_update_pages(pages=[...])` — Update multiple pages\n"
						. "- `wp_bulk_update_posts(posts=[...])` — Update multiple posts\n"
						. "- `wp_batch_update(operations=[...])` — Mixed batch operations\n"
						. "- `wp_delete_all_drafts()` — Clean up all draft posts and pages",
				),
				array(
					'heading' => 'Search & Fetch',
					'content' => "- `wp_search(query, type, status)` — Search posts/pages by keyword\n"
						. "- `wp_fetch(id)` or `wp_fetch(url)` — Get a single post/page by ID or URL\n"
						. "- `wp_get_page_by_slug(slug)` — Find a page by its URL slug\n"
						. "- `wp_list_content(post_type)` — List any custom post type",
				),
				array(
					'heading' => 'Relevant Tools',
					'content' => "- `wp_list_posts`, `wp_create_post`, `wp_update_post`, `wp_delete_post`\n"
						. "- `wp_list_pages`, `wp_create_page`, `wp_update_page`, `wp_delete_page`\n"
						. "- `wp_clone_page(id)` — Duplicate a page\n"
						. "- `wp_list_categories()`, `wp_list_tags()`\n"
						. "- `wp_create_term`, `wp_update_term`, `wp_delete_term`\n"
						. "- `wp_list_drafts()`, `wp_delete_all_drafts()`",
				),
			),
		);
	}

	/**
	 * Forms guide.
	 *
	 * @return array Guide content.
	 */
	public static function guide_forms() {
		$core         = new Spai_Core();
		$capabilities = $core->get_capabilities();

		$active_plugins = array();
		if ( ! empty( $capabilities['cf7'] ) ) {
			$active_plugins[] = 'Contact Form 7';
		}
		if ( ! empty( $capabilities['wpforms'] ) ) {
			$active_plugins[] = 'WPForms';
		}
		if ( ! empty( $capabilities['gravityforms'] ) ) {
			$active_plugins[] = 'Gravity Forms';
		}
		if ( ! empty( $capabilities['ninjaforms'] ) ) {
			$active_plugins[] = 'Ninja Forms';
		}

		return array(
			'topic'          => 'forms',
			'title'          => 'Forms Integration Guide',
			'active_plugins' => $active_plugins,
			'sections'       => array(
				array(
					'heading' => 'Detected Form Plugins',
					'content' => 'Active: ' . ( ! empty( $active_plugins ) ? implode( ', ', $active_plugins ) : 'None detected' )
						. "\n\nUse `wp_forms_status()` for detailed plugin and form counts.",
				),
				array(
					'heading' => 'Working with Forms',
					'content' => "1. **List forms**: `wp_list_forms()` — shows all forms across plugins\n"
						. "2. **Inspect form**: `wp_get_form(form_id)` — get form fields and settings\n"
						. "3. **View entries**: `wp_get_form_entries(form_id)` — see submitted data\n\n"
						. "Forms are identified by their plugin-specific IDs. The listing includes the plugin source.",
				),
				array(
					'heading' => 'Embedding Forms in Elementor',
					'content' => "Each form plugin has an Elementor widget:\n\n"
						. "**Contact Form 7:**\n"
						. "```json\n"
						. "{\"elType\": \"widget\", \"widgetType\": \"shortcode\", \"settings\": {\"shortcode\": \"[contact-form-7 id=\\\"123\\\" title=\\\"Contact\\\"]\"}}\n"
						. "```\n\n"
						. "**WPForms:**\n"
						. "```json\n"
						. "{\"elType\": \"widget\", \"widgetType\": \"wpforms\", \"settings\": {\"form_id\": \"123\"}}\n"
						. "```\n\n"
						. "**Gravity Forms:**\n"
						. "```json\n"
						. "{\"elType\": \"widget\", \"widgetType\": \"shortcode\", \"settings\": {\"shortcode\": \"[gravityform id=\\\"1\\\" title=\\\"true\\\"]\"}}\n"
						. "```",
				),
				array(
					'heading' => 'Relevant Tools',
					'content' => "- `wp_forms_status()` — Check form plugin status\n"
						. "- `wp_list_forms()` — List all forms\n"
						. "- `wp_get_form(form_id)` — Get form details and fields\n"
						. "- `wp_get_form_entries(form_id)` — Get submitted entries",
				),
			),
		);
	}

	/**
	 * WooCommerce guide.
	 *
	 * @return array Guide content.
	 */
	public static function guide_woocommerce() {
		return array(
			'topic'    => 'woocommerce',
			'title'    => 'WooCommerce Guide',
			'sections' => array(
				array(
					'heading' => 'Overview',
					'content' => "WooCommerce products are a custom post type (`product`). Use `wp_list_content(post_type=\"product\")` to list them.\n\n"
						. "WooCommerce data is stored in post meta and custom tables. Use `wp_get_post_meta(id)` to inspect product meta.",
				),
				array(
					'heading' => 'Product Management',
					'content' => "**List products:**\n"
						. "```json\n"
						. "wp_list_content(post_type=\"product\", status=\"publish\")\n"
						. "```\n\n"
						. "**Product meta keys:**\n"
						. "| Key | Description |\n"
						. "|-----|-------------|\n"
						. "| `_regular_price` | Regular price |\n"
						. "| `_sale_price` | Sale price |\n"
						. "| `_sku` | Stock keeping unit |\n"
						. "| `_stock` | Stock quantity |\n"
						. "| `_stock_status` | instock / outofstock |\n"
						. "| `_weight` | Product weight |\n"
						. "| `_thumbnail_id` | Featured image ID |",
				),
				array(
					'heading' => 'Product Categories',
					'content' => "WooCommerce uses the `product_cat` taxonomy:\n"
						. "```json\n"
						. "wp_create_term(taxonomy=\"product_cat\", name=\"Electronics\")\n"
						. "```",
				),
				array(
					'heading' => 'Relevant Tools',
					'content' => "- `wp_list_content(post_type=\"product\")` — List products\n"
						. "- `wp_delete_content(post_type=\"product\", id)` — Delete a product\n"
						. "- `wp_get_post_meta(id)` — Get product meta\n"
						. "- `wp_set_post_meta(id, meta_key, meta_value)` — Update product meta\n"
						. "- `wp_create_term(taxonomy=\"product_cat\", ...)` — Create product category\n"
						. "- `wp_set_featured_image(id, image_id)` — Set product image",
				),
			),
		);
	}

	/**
	 * Workflows guide (delegates to Spai_Workflows).
	 *
	 * @return array Guide content.
	 */
	public static function guide_workflows() {
		$workflows = Spai_Workflows::get_all();

		return array(
			'topic'       => 'workflows',
			'title'       => 'Workflow Templates',
			'description' => 'Step-by-step guides for common tasks. Use wp_get_workflow(name="...") to get the full workflow for a specific task.',
			'workflows'   => $workflows,
		);
	}

	/**
	 * Troubleshooting guide.
	 *
	 * @return array Guide content.
	 */
	public static function guide_troubleshooting() {
		return array(
			'topic'    => 'troubleshooting',
			'title'    => 'Troubleshooting Guide',
			'sections' => array(
				array(
					'heading' => 'Elementor Data Not Rendering',
					'content' => "**Symptom:** Page is blank or shows wrong content after `wp_set_elementor`.\n\n"
						. "**Causes and fixes:**\n"
						. "1. **Wrong layout mode** — Check `elementor_layout_mode` in `wp_site_info()`. Use sections or containers accordingly.\n"
						. "2. **Missing element IDs** — Every element needs a unique `id`. The plugin auto-generates but check the warnings in the response.\n"
						. "3. **Invalid widget type** — Check the `warnings` array in the response. Use `wp_get_elementor_widgets()` to see valid types.\n"
						. "4. **CSS cache** — Run `wp_regenerate_elementor_css()` after making changes.\n"
						. "5. **Page template** — Elementor pages need the right template. Use `wp_update_page_template(id, template=\"elementor_header_footer\")`.",
				),
				array(
					'heading' => 'API Key Issues',
					'content' => "**401 Unauthorized:**\n"
						. "- Verify key is sent in `X-API-Key` header\n"
						. "- Key may be revoked — check with site admin\n"
						. "- Key may have expired\n\n"
						. "**403 Forbidden (category restriction):**\n"
						. "- API key role may not include the tool category\n"
						. "- Use `wp_list_api_keys()` to check key permissions\n"
						. "- Error message includes allowed categories",
				),
				array(
					'heading' => 'Rate Limiting',
					'content' => "**429 Too Many Requests:**\n"
						. "- Default: 60 requests per minute\n"
						. "- Check with `wp_rate_limit_status()`\n"
						. "- Admin can adjust with `wp_update_rate_limit()`\n"
						. "- Wait for the cooldown period and retry",
				),
				array(
					'heading' => 'Tool Not Found',
					'content' => "**\"Unknown tool\" error:**\n"
						. "- Tool may require a plugin that is not active (e.g., Elementor tools need Elementor)\n"
						. "- Tool may be a Pro feature — check `wp_introspect()` for available tools\n"
						. "- Tool category may be disabled by admin\n"
						. "- Use `wp_introspect()` to see all available tools",
				),
				array(
					'heading' => 'Elementor Column Size Errors',
					'content' => "**Warning: column sizes do not sum to 100:**\n"
						. "- In section mode, all `_column_size` values in a section must sum to 100\n"
						. "- For 2 columns: 50 + 50, or 33 + 67, etc.\n"
						. "- For 3 columns: 33 + 33 + 33 (rounding is OK)\n"
						. "- In container mode, `_column_size` is not used — use `flex_direction` and `width` instead",
				),
				array(
					'heading' => 'SEO Fields Not Saving',
					'content' => "**SEO tool returns error:**\n"
						. "- Verify an SEO plugin is installed: `wp_detect_plugins()`\n"
						. "- Check the right fields: `wp_get_seo(id)` to see current values\n"
						. "- Some fields are plugin-specific — use normalized field names",
				),
				array(
					'heading' => 'General Debugging',
					'content' => "1. `wp_introspect()` — Full system overview\n"
						. "2. `wp_detect_plugins()` — Check active plugins\n"
						. "3. `wp_site_info()` — WordPress and PHP version\n"
						. "4. `wp_get_site_health()` — WordPress site health report\n"
						. "5. `wp_elementor_status()` — Elementor-specific status",
				),
			),
		);
	}
}
