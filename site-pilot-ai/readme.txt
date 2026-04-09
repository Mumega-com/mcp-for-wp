=== mumcp ===
Contributors: mumega
Donate link: https://mucp.mumega.com
Tags: ai, claude, mcp, model-context-protocol, elementor, api
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 2.7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Turn WordPress into a reusable AI production system. Connect Claude and other MCP-compatible assistants to pages, products, Elementor parts, archetypes, design references, and site character.

== Description ==

MUCP connects your WordPress site to AI assistants like Claude using the Model Context Protocol (MCP). Instead of generating isolated one-off pages, it helps operators build with reusable structure: guided site character, Elementor parts, page archetypes, WooCommerce product archetypes, and design references from screenshots or Figma. Your AI assistant can create drafts, preserve brand patterns, save reusable sections back into the library, and manage the rest of WordPress through natural language. Every feature is free.

= Key Features =

* **Content Management** - Create, edit, and delete posts and pages
* **Media Handling** - Upload files or import from URLs
* **Draft Management** - List and bulk-delete drafts
* **Full Elementor** - Get/set Elementor data, templates, landing pages, widget control
* **Elementor 4 Ready** - Tested on WordPress 6.9.1 with Elementor 4.0.0, with verified fallback when Elementor's document save does not persist data
* **SEO Tools** - Yoast, RankMath, AIOSEO, SEOPress integration
* **Forms** - Contact Form 7, WPForms, Gravity Forms, Ninja Forms
* **WooCommerce** - Products, orders, customers, categories, analytics
* **AI Integrations** - Image generation, stock photos, text-to-speech, alt text, and Figma design intake
* **Structured Design System** - Reusable Elementor parts, page archetypes, product archetypes, guided site character, and image-based design references
* **Operator Workflow** - Onboarding, library curation, draft-first generation, provenance, and recovery-friendly update guidance
* **Theme Builder** - Create and manage Elementor theme templates
* **Secure API** - API key authentication with activity logging
* **MCP Compatible** - Works with Claude Code and Claude Desktop
* **200+ MCP Tools** - All features included, no paid tiers

= How It Works =

1. Install and activate the plugin
2. Copy your API key from mumcp in the admin menu
3. Configure your MCP server with the API key
4. Define your site character and save reusable assets like archetypes, parts, and design references
5. Start building draft pages and products with natural language

= Example Commands =

* "Create a blog post about summer recipes"
* "Run an SEO audit on all published pages"
* "Build a landing page from our SaaS archetype and reuse the pricing proof section"
* "Save this screenshot as a design reference, create a draft page from it, and keep strong sections as reusable parts"
* "Upload this image and set it as the featured image for post 123"

== Installation ==

= From WordPress Admin =

1. Go to Plugins → Add New
2. Search for "mumcp"
3. Click Install Now, then Activate
4. Go to mumcp in the admin menu to get your API key

= Manual Installation =

1. Download the plugin ZIP file
2. Go to Plugins → Add New → Upload Plugin
3. Select the ZIP file and click Install Now
4. Activate the plugin
5. Go to mumcp in the admin menu to get your API key

= MCP Server Setup =

Add to your `~/.claude.json`:

`{
  "mcpServers": {
    "site-pilot-ai": {
      "command": "node",
      "args": ["/path/to/mcp-server/dist/index.js"],
      "env": {
        "WP_URL": "https://yoursite.com",
        "WP_API_KEY": "spai_your_api_key_here"
      }
    }
  }
}`

== Frequently Asked Questions ==

= What is MCP? =

Model Context Protocol (MCP) is an open protocol that enables AI assistants like Claude to interact with external tools and services. mumcp exposes your WordPress site as an MCP-compatible tool.

= Is this secure? =

Yes. All requests require a unique API key. Keys are hashed using WordPress password hashing (not stored in plain text). A dedicated service account with limited capabilities handles API requests. Activity logging tracks all API usage for auditing.

= Does it work with any AI? =

MUCP works with any AI assistant that supports the MCP protocol. Currently, this includes Claude Code and Claude Desktop. More integrations are planned.

= Do I need coding skills? =

No. Once configured, you control WordPress through natural language. The AI handles all the technical details.

= What about Elementor? =

Full Elementor support is included: get/set page data, templates, landing pages, widgets, reusable parts, page archetypes, and full page building capabilities. All features are free.

= Who is this for? =

MUCP is best for operators: founders, marketers, agencies, and site managers who ship pages or products repeatedly and want AI to work from approved structure instead of rebuilding everything from scratch.

= Does it support Elementor 4? =

Yes. The current stack has been tested on WordPress 6.9.1 with Elementor 4.0.0. mumcp verifies that Elementor saves actually persist `_elementor_data`, and automatically falls back to a direct meta save when Elementor reports success but stores nothing. Landing page generation was also verified on the local Elementor 4 test stack.

= Can I use this on multiple sites? =

Each site needs its own plugin installation and API key. Multi-site management features are included.

== Screenshots ==

1. Setup tab — Activity log showing recent API requests
2. Connect AI tab — One-click configuration for Claude Desktop and Claude Code
3. Settings tab — Plugin configuration and integrations
4. Advanced tab — REST API reference with copy-paste curl examples

== Changelog ==

= 2.7.0 =

* All 239 tools free — no licensing gate, no trial, no restrictions
* Elementor v4 Atomic editor support in validator (#211)
* KNOWN_ISSUES.md and COMPATIBILITY.md documentation
* Launch-ready README with architecture diagram and competitor table

= 2.7.0 =

* New: Elementor v4 Atomic editor support in validator (#211)
* New: KNOWN_ISSUES.md and COMPATIBILITY.md documentation

= 2.6.0 =

* New: 10 blueprint types — team, portfolio, blog_grid, services, about, process_steps, social_proof, product_showcase, before_after, newsletter (24 total)
* Launch blog post drafted

= 2.5.3 =

* Enhancement: wp_create_theme_template dry_run support (#203)
* Enhancement: Kit CSS unscoped selector warnings (#205)
* Enhancement: wp_set_elementor_globals layout params (container_width, breakpoints)
* Closed: #200 (SG cache), #201 (counter text), #202 (globals), #204 (delete exists)

= 2.5.2 =

* Fix: SiteGround full-page cache purge after Elementor saves
* Fix: Stats blueprint text values (Free, checkmarks) no longer render as 0 — uses heading widget for non-numeric stats (#201)
* Filed: #200-#205 from Antigravity full-day feedback

= 2.5.1 =

* Fix: wp_create_theme_template elementor_data now renders on frontend and loads in editor

= 2.5.0 =

* New: Restructured admin navigation — Setup, Library, Integrations, Tools, Settings, Activity Log
* New: Setup page with API key generation, MCP config for all clients, connection status
* New: Library page extracted — archetypes, reusable parts, design references
* New: Settings page extracted — logging, CORS, rate limits, site context

= 2.4.1 =

* Fix fatal error when updater file excluded from WP.org build

= 2.4.0 =

* Renamed to mumcp (5+ chars for WP.org slug generation)
* Removed self-updater from WP.org build (not permitted)
* Fixed SQL escaping in activity log for WP.org scanner

= 2.3.5 =

* Plugin renamed to mumcp everywhere

= 2.3.4 =

* Plugin URI changed to mucp.mumega.com — avoid WP.org trademark filter on previous URL

= 2.3.3 =

* Removed "for WordPress" from plugin name per WP.org trademark policy

= 2.3.2 =

* All plugin and site URLs updated to mucp.mumega.com

= 2.3.1 =

* Updated GitHub repository references to Mumega-com/mcp-for-wp
* Plugin URI updated to mucp.mumega.com
* Site branding updated to match plugin name

= 2.3.0 =

* Renamed to "mumcp" for WordPress.org compliance
* Author updated from DigID Inc to Mumega
* Plugin URI updated to mumega.com/mcp
* Copyright updated to 2026
* Freemius and license endpoints excluded from build via .distignore

= 2.2.7 =

* Fix: Force direct meta overwrite after Document::save() to prevent revision/cache divergence on existing pages (#198)

= 2.2.6 =

* Fix: wp_regenerate_elementor_css returning 0 pages — method_exists vs property check (#196)

= 2.2.5 =

* Fix: Counter widget typography keys added to schema (#194)
* Fix: wp_edit_section/wp_set_elementor persist on second call — v2.2.4 fallback cache flush now in all save paths (#195, #197)
* All fixes from 2.2.4 included (was missing #194 in the 2.2.4 zip)

= 2.2.4 =

* Fix: Empty front-end render after MCP Elementor save — flush document cache and post_content in fallback path (#187)
* Fix: wp_edit_widget silently failed to persist — same fallback cache issue in shared save helper
* Fix: Validator false positives on icon-box 'align' key — merge static schema with live Elementor controls
* Fix: Removed icon-box title_size control rename that corrupted valid h-tag setting (#190)
* Enhancement: Split elementor tool category (99 tools) into 5 subcategories for Gemini 100-tool limit (#191)

= 2.2.3 =

* Fix: features blueprint — inner card containers now have explicit width (30%/22%/47% by column count) so flex-wrap produces a 3x3 grid instead of a single row
* Fix: features blueprint — icon-box widgets now left-align icon and text by default (align: left)
* Fix: features blueprint — card containers now include white background, 12px border-radius, box-shadow, and CSS hover lift effect
* Fix: features blueprint — button widget rendered with proper styling when item provides button_text/url
* Fix: icon-box widget schema — added align and title_typography_font_size keys to prevent spurious "unknown key" validation warnings

= 2.2.2 =

* Fix: Elementor archetype and reusable-part updates now preserve existing metadata when a request only changes content
* UI: Clarify in the Library that SPAI archetypes and reusable parts are Elementor templates with SPAI metadata layered on top
* Carry forward the `2.2.1` shared-host-safe Elementor mutation path and operator workflow improvements

= 2.2.1 =

* Fix: Pro Elementor template, archetype, and part create/update routes now accept `elementor_data_base64` for shared-host compatibility
* Docs: Added HostGator / ModSecurity guidance for Elementor mutations using form-encoded and base64 payloads
* Carry forward the `2.2.0` operator workflow, design-reference, archetype, and library improvements

= 2.2.0 =
* Add operator-focused admin polish with onboarding, update recovery, Library health, lineage, and drill-down asset views
* Extend design references so screenshots can generate Elementor draft pages and reusable parts directly from the Library
* Update README and plugin messaging to position Site Pilot AI as a reusable AI production system for WordPress operators

= 2.1.0 =
* Add image-based design references so uploaded screenshots and mockups can be stored as reusable site assets
* Add MCP and REST tools for storing, listing, reading, and updating design references
* Add a structured build_from_design_reference workflow for turning approved design images into local pages, archetypes, and reusable parts
* Update onboarding and media guidance so models preserve approved screenshots as design references before building

= 2.0.0 =
* Add structured site-building primitives with reusable Elementor parts, page archetypes, and WooCommerce product archetypes
* Add guided site character authoring, public llms.txt output, and AI-facing context inheritance for page and product archetypes
* Add Figma integration with personal token and OAuth configuration, plus REST and MCP design intake tools
* Add Library and Integrations admin improvements for curating archetypes, parts, and design connections

= 1.8.6 =
* Test release: validate live auto-update from 1.8.5 with the filesystem-aware updater
* Carry forward updater hardening and Elementor 4 save verification from 1.8.5

= 1.8.5 =
* Initialize the WordPress filesystem before self-updates and return actionable upgrader errors instead of a generic failure
* Carry forward updater hardening and Elementor 4 save verification from 1.8.4

= 1.8.4 =
* Test release: validate end-to-end WordPress self-update from the canonical mumega.com manifest
* Carry forward updater hardening and Elementor 4 save verification from 1.8.3

= 1.8.3 =
* Fix: updater now prefers the newer valid manifest instead of letting stale spai_update_info block releases
* Fix: Elementor 4 save path now verifies Document::save() persistence and falls back automatically when Elementor stores zero sections
* Enhancement: Section and widget edit flows now use the shared verified save helper

= 1.8.2 =
* Fix: Templates created via wp_save_section_as_template had empty content — Document::save() doesn't persist _elementor_data for elementor_library posts, now falls back to direct meta write with verification (#185)
* Enhancement: Elementor 4 compatibility hardening — save paths now verify Document::save() persistence and fall back automatically when Elementor stores zero sections
* Enhancement: Section and widget edit flows now use the shared verified save helper for more consistent Elementor 4 behavior

= 1.8.1 =
* Fix: Posts widget uses wrong setting keys — post_type auto-renamed to posts_post_type, columns to classic_columns, etc. (#182)
* Fix: Posts widget schema rewritten with correct skin-prefixed keys (classic_columns, classic_posts_per_page, posts_post_type)
* Enhancement: Posts, loop-grid, portfolio widgets now show key settings in wp_get_elementor_summary

= 1.8.0 =
* Add: wp_get_elementor_summary now includes element IDs and section index — agents can target sections for add/remove/replace (#181)
* Add: Blueprint Registry — wp_list_blueprints + wp_get_blueprint for generating individual sections without creating pages
* Add: wp_save_section_as_template — extract a section from a live page and save as reusable Elementor template
* Enhancement: All Elementor save responses include next_step hints guiding agents to verify
* Enhancement: Rewritten MCP tool descriptions with workflow guidance (wp_get_elementor_summary, wp_add_section, wp_set_elementor, wp_build_page, wp_apply_elementor_template, etc.)

= 1.7.7 =
* Fix: astra-settings option now accessible via wp_get_option/wp_update_option (hyphenated key wasn't matched by astra_ prefix rule)
* Enhancement: Added generate_settings (GeneratePress) to exact-match allowed options

= 1.7.6 =
* Enhancement: Updater now checks Cloudflare Worker for version info — no SSH needed for deploys
* Enhancement: spai_update_info option enables MCP-based deploys
* Enhancement: wp_trigger_update accepts package_url for forced installs

= 1.7.5 =
* Fix: wp_build_page silent failure to persist Elementor data — Document::save() fallback now uses boolean flag with post-write verification (#178)
* Enhancement: wp_build_page response includes meta_verified and sections_saved fields

= 1.7.4 =
* Add: wp_check_update MCP tool — check for plugin updates via AI
* Add: wp_trigger_update MCP tool — install plugin updates via AI

= 1.7.3 =
* Fix: wp_build_page, wp_create_landing_page, wp_apply_template, wp_create_theme_template now use Document::save() API — pages render on frontend immediately (#177)
* Fix: All Elementor save paths consolidated to use Document::save() with meta_direct fallback

= 1.7.2 =
* Add: Self-hosted auto-updater — plugin updates directly from mucp.mumega.com
* Add: "Check for updates" link in plugin row meta

= 1.7.1 =
* Fix: Elementor save now uses Document::save() API instead of direct meta write — properly regenerates CSS and updates post_content (#176)
* Fix: Cloned/rebuilt pages now render correctly on the frontend without manual editor re-save
* Fix: CSS file size no longer stuck at 33 bytes after API saves
* Enhancement: save_method field in response reports "document_save" (new) or "meta_direct" (fallback)

= 1.7.0 =
* Renamed to Mumega Site Pilot AI
* All 200+ MCP tools are now free — no Pro tier, no license required
* Removed Freemius SDK dependency
* SEO, WooCommerce, Forms, Theme Builder, AI integrations — all included
* Author URI updated to digid.ca
* Expanded External Services disclosure in readme
* Simplified admin UI — removed upgrade banners and license management

= 1.6.0 =
* New: wp_add_section — add a section/container at any position (start, end, before/after element ID)
* New: wp_remove_section — remove any element by ID (top-level or nested)
* New: wp_replace_section — replace an element in-place by ID
* New: wp_patch_elementor — batch multiple operations (add, remove, replace, settings) in a single request with one DB write
* New: strip_defaults parameter on wp_get_elementor — strips default widget settings to reduce payload 70-80%
* Enhancement: Shared save_elements_to_page helper — validate, write, verify, CSS regen, cache clear in one path
* Enhancement: Section-level CRUD eliminates need to send full page JSON for targeted edits

= 1.5.2 =
* Fix: wp_build_page now properly initializes Elementor documents — sets _elementor_version and _elementor_pro_version meta, clears caches (#174)
* Fix: wp_set_elementor no longer silently drops sections — validation runs before DB write, single write, count verification with error on mismatch (#175)
* Fix: Response now includes sections_saved and sections_submitted counts for data integrity verification
* Fix: Removed ineffective ini_set calls from MCP handler (called too late to affect PHP input parsing)

= 1.5.1 =
* New: WordPress Multisite support — network-wide activation, per-site provisioning (#14)
* New: wp_network_sites — list all sites in a multisite network with plugin/key status
* New: wp_network_switch — switch MCP context to a different network site
* New: wp_network_stats — network-wide statistics (total sites, posts, users, storage)
* New: Network Admin page for managing Site Pilot AI across all sites
* Enhancement: Auto-provision plugin tables and API keys on new site creation
* Enhancement: Bot user fallback for cross-site authentication in multisite
* Enhancement: Network-wide uninstall cleanup iterates all sites

= 1.5.0 =
* New: wp_preview_elementor — render page content as HTML, text, or summary with element stats (#168)
* New: elementor_data_base64 parameter for wp_set_elementor — base64 encoding bypasses all quoting/escaping issues (#165)
* Fix: JSON recovery for corrupted Elementor payloads (stripslashes, double-encoding unwrap, UTF-8 fix)
* Fix: Re-encode decoded Elementor JSON to ensure clean storage
* Enhancement: Preview endpoint returns sections/columns/widgets count, widget types list, word count

= 1.4.4 =
* Fix: Force JSON content-type on MCP responses, prevents SSE content-type errors during rapid calls (#164)
* Fix: Support elementor_snippet post type in wp_create_post with auto-set _elementor_location meta (#172)
* Enhancement: wp_regenerate_elementor_css now explains skipped pages with reasons and supports force=true (#173)
* Enhancement: elementor_snippet included in Elementor data GET/SET allowed types

= 1.4.3 =
* Fix: Page-specific CSS now verified after generation; auto-primes via loopback if empty (#169)
* Fix: Eduma theme custom CSS dual-write to thim_custom_css option (#171)
* Fix: Cache-busting on CSS verification loopback request
* Enhancement: 9 new Pro widget schemas (theme-post-content, theme-site-logo, loop-grid, gallery, table-of-contents, hotspot, search-form, etc.) (#167)
* Enhancement: Expanded nav-menu widget keys (dropdown, toggle, submenu styles)
* Enhancement: Auto-rename common wrong nav-menu keys (text_color → color_menu_item, etc.)
* Enhancement: Actionable alternatives in custom CSS response when theme doesn't render it

= 1.4.2 =
* Fix: Template assignment scopes (specific_post_type, specific_posts, all_singular, all_archive) now work correctly
* Fix: wp_create_theme_template now accepts post_type and post_ids params for targeted template assignment
* Fix: Custom post types (tp_event, lp_course, etc.) can now be targeted in template conditions
* Enhancement: Better error messages for invalid scope — shows all valid options
* Enhancement: MCP tool descriptions updated with custom post type examples

= 1.4.1 =
* Fix: MCP create/update endpoints now receive parameters correctly (get_json_params → get_params fallback)
* Fix: Affects LearnPress, WooCommerce, Events, Themes, and Site Settings create/update operations via MCP
* Fix: All pro REST controllers now compatible with internal MCP dispatch

= 1.4.0 =
* New: LearnPress LMS integration — 18 MCP tools for courses, lessons, quizzes, curriculum, categories, and LMS stats
* New: ThimPress Events integration — 4 MCP tools for event management (dates, locations, registration)
* New: 17 WooCommerce MCP tools — products, orders, customers, categories, tags, analytics (previously REST-only, now MCP-accessible)
* New: WooCommerce product category create/update endpoints
* New: LearnPress guide topic in wp_get_guide
* Enhancement: All new tools gated by plugin detection (LearnPress, TP Events, WooCommerce)

= 1.3.0 =
* New: MCP server instructions — auto-injected guidance for AI models on connect (WordPress concepts, Elementor rules, best practices)
* New: wp_onboard tool — complete first-connection briefing with site inventory, integrations, tools, and recommendations
* New: wp_get_guide tool — on-demand guides for elementor, seo, menus, media, content, forms, workflows, troubleshooting
* New: wp_get_workflow tool — step-by-step workflow templates (build landing page, SEO audit, content migration, site redesign, etc.)
* New: wp_elementor_widget_help tool — offline widget reference with valid keys, examples, and common mistakes for 35+ widgets
* New: Actionable error hints — all error responses include contextual hints and guide references to help AI models self-correct
* Enhancement: wp_get_elementor_widgets now includes descriptions, categories, and reference availability per widget
* Enhancement: Elementor validation warnings now list valid settings keys when unknown keys are detected
* Enhancement: MCP instructions are dynamic — only include sections for active plugins (Elementor, SEO, WooCommerce, Gutenberg)

= 1.2.0 =
* Fix: wp_bulk_seo "Post not found" for valid page IDs — fixed parameter extraction and ID normalization
* Fix: wp_set_seo noindex now correctly writes to Yoast/RankMath meta with boolean handling
* Fix: wp_get_seo noindex now reads Yoast/RankMath robots meta correctly
* Fix: wp_set_seo OG and Twitter card fields now persist to Yoast/RankMath meta
* Fix: wp_update_menu_item auto-resolves menu_id if omitted (no more misleading 404)
* Fix: wp_fetch now returns private/draft/pending pages with admin API key
* Fix: wp_create_post accepts elementor_snippet and elementor_library post types
* Fix: wp_set_post_meta JSON values stored as PHP arrays (not JSON strings), blocks Elementor internal keys
* Fix: Elementor child containers auto-get isInner:true with validation warning
* Fix: Improved Elementor CSS regeneration after API saves (cache flush, fresh document load)
* Fix: wp_set_custom_css detects themes that strip Additional CSS and warns
* Fix: SSE content-type errors fixed with explicit headers and output buffer cleanup on MCP responses
* Fix: Rate limit headers (X-RateLimit-Remaining, X-RateLimit-Reset) now included on all MCP responses
* New: wp_edit_widget — lightweight widget editing by ID without full elementor_data round-trip
* New: wp_preview_elementor — get rendered HTML output of Elementor content
* New: wp_seo_scan — bulk SEO audit across all published content
* New: wp_seo_report — export complete SEO metadata inventory
* New: wp_submit_to_google_index / wp_google_index_status — Google Indexing API integration
* New: wp_get_widget_schema already existed (confirmed)
* New: wp_update_term already existed (confirmed)
* Enhancement: Per-key rate limit overrides (burst, per_minute, per_hour) for scoped API keys
* Enhancement: wp_regenerate_elementor_css now reports skipped pages with reasons and force flag
* Enhancement: Google Indexing API added as integration provider with service account JSON field

= 1.1.21 =
* New: Feedback relay — all customer feedback automatically creates GitHub issues for plugin authors
* New: Public `/feedback/relay` endpoint with rate limiting and duplicate detection
* Enhancement: `wp_submit_feedback` now phones home to central relay so developers always receive bug reports
* Enhancement: Feedback includes site URL, site name, and plugin version for context

= 1.1.19 =
* Fix: All plugin URLs now use correct domain (mucp.mumega.com) with HTTPS
* Fix: Report a Bug link now points to GitHub issues instead of non-existent page
* Fix: Screenshot Worker docs link corrected

= 1.1.18 =
* New: Screenshot Worker integration card on Integrations page — configure Cloudflare Browser Rendering for high-quality screenshots
* New: MCP tools for integration management — `wp_integrations_status`, `wp_configure_integration`, `wp_test_integration`, `wp_remove_integration`
* New: AI assistants can now set up integrations (screenshot worker, Pexels, OpenAI, etc.) directly via MCP instead of requiring WP admin access
* New: Multi-field provider support in Integration Manager for providers that need URL + token (not just an API key)
* Enhancement: Screenshot class reads from Integration Manager first, falls back to legacy spai_settings
* Enhancement: Integrations admin page now shows provider descriptions and setup guides
* Moved: Screenshot Worker configuration from Settings > General to Integrations page

= 1.1.17 =
* New: Role-based API keys — assign Author, Designer, Editor, Admin, or Custom roles to control which MCP tool categories each key can access
* New: Admin UI for role-based key management with role dropdown, category checkboxes, and role badges
* New: MCP `initialize` response includes role and allowed tool categories for non-admin keys
* New: MCP `tools/list` automatically filters tools based on the API key's role — AI models only see tools they can use
* New: MCP `tools/call` enforces role restrictions — blocked tools return a clear error with allowed categories
* New: `wp_create_api_key` tool now accepts `role` and `tool_categories` parameters
* Enhancement: Predefined roles (Author: content/media/taxonomy, Designer: elementor/gutenberg/media/site, Editor: content/media/taxonomy/seo) for quick setup

= 1.1.16 =
* New: `wp_get_rendered_html` endpoint — fetch the rendered HTML of any page for CSS/font/meta verification (SSRF-safe, same-host only)
* New: `wp_get_elementor_bulk` endpoint — fetch Elementor data for up to 25 pages in a single request
* New: CSS rendering verification on `wp_set_custom_css` — loopback check confirms `<style>` tag is present in rendered output
* New: `custom_css` parameter documented on `wp_set_elementor_globals` for kit-level CSS
* New: Screenshot Worker setup documentation (`docs/SCREENSHOT_SETUP.md`)

= 1.1.15 =
* Fix: Auto-set `isInner: true` on child containers in nested Elementor layouts — prevents editor crashes and broken rendering from API-built pages
* Fix: Block Elementor internal meta keys (`_elementor_data`, `_elementor_page_settings`, etc.) from `wp_set_post_meta` with helpful redirect to `/elementor/{id}` endpoint
* Fix: JSON values in `wp_set_post_meta` are preserved instead of being mangled by `sanitize_text_field()`
* Fix: CSS regeneration now supports `force` param — deletes existing CSS files before regenerating, with detailed per-page reporting (regenerated, skipped with reason, failed with error)
* Fix: `elementor_snippet` post type now accepted by `wp_create_post` (whitelisted as safe non-public type)
* New: Widget schema endpoints — `GET /elementor/widgets` lists all widget types, `GET /elementor/widgets/{type}` returns full controls schema with valid keys, types, defaults, and options
* New: `wp_get_widget_schema` MCP tool — discover valid widget control keys before building pages (prevents silent rendering failures)
* New: `wp_get_elementor_widgets` moved from Pro to Free tier (critical DX for all users)
* New: `preview_url` and `css_regenerated` fields in Elementor save response
* New: Theme compatibility warning on `wp_set_custom_css` — detects Eduma/ThimPress/Flavor themes that use their own CSS system

= 1.1.14 =
* New: Elementor dry-run/validate mode — pass `dry_run=true` to POST /elementor/{id} to validate data without saving (returns warnings and fixes)
* New: RTL site detection — `is_rtl` and `text_direction` fields in GET /site-info response
* New: RTL-aware Elementor validation warnings — flags hardcoded `align: left`, `float: left`, and one-sided margin/padding in custom CSS on RTL sites
* New: RTL flag on multilingual language lists (Pro) — WPML, Polylang, and TranslatePress language arrays now include `is_rtl` per language
* Updated: wp_set_elementor MCP tool supports `dry_run` parameter
* Updated: wp_site_info MCP tool description mentions RTL detection

= 1.1.13 =
* New: `fields` parameter on GET /posts and GET /pages — control which fields are returned (e.g. `fields=id,title,word_count`)
* New: `ids` parameter on GET /posts and GET /pages — fetch specific items by ID without N+1 queries (e.g. `ids=41,42,43`)
* New: `word_count` field available on posts and pages when using `fields` or fetching single items
* Improvement: GET requests now get 2x burst and minute rate limits (reads are non-destructive)
* Improvement: Batch sub-requests no longer consume individual rate limit tokens
* Updated: wp_list_posts and wp_list_pages MCP tool schemas include new ids and fields parameters

= 1.1.12 =
* New: wp_build_page gains 6 section types — contact_form, map, countdown, stats, logo_grid, video
* Improvement: wp_batch_update MCP tool description now includes operation schema, examples, and sequential execution notes
* Improvement: wp_screenshot_url now exposes optional webhook_url parameter for async screenshot delivery

= 1.1.11 =
* Improvement: wp_regenerate_elementor_css now returns detailed confirmation — pages processed, CSS file sizes, method used (regenerated vs cache cleared)
* New: wp_delete_custom_css — remove all Additional CSS from the Customizer
* New: wp_get_css_length — lightweight check for CSS length/line count without returning full body
* New: wp_bulk_update_posts — update multiple posts in one call (up to 50)
* New: wp_bulk_update_pages — update multiple pages in one call (up to 50)

= 1.1.10 =
* Fix: Non-Latin site names (Persian, Arabic, CJK) now produce clean hostname-based slugs in Connect tab instead of URL-encoded gibberish

= 1.1.9 =
* Improvement: Connect AI tab redesigned — new Custom Connector method (recommended), updated Claude Code and npm package configs, all using sitepilotai-{slug} naming
* New: Changelog tab in admin — browse all release notes directly from the plugin dashboard
* Improvement: Advanced tab updated — added /elementor/{id}/summary, /elementor/{id}/edit-section, DELETE /media/{id}, GET /mcp endpoints

= 1.1.8 =
* New: API key can be passed as URL query parameter (?api_key=spai_...) for Claude Desktop custom connectors and MCP clients that don't support custom headers
* New: MCP endpoint now responds to GET requests with server info (capability discovery / health check)

= 1.1.7 =
* New: wp_edit_section — surgically edit a single Elementor element by ID, index, or search criteria without full JSON round-trip
* Improvement: wp_get_option/wp_update_option allowlist expanded with prefix-based matching (elementor_*, wpseo_*, rank_math_*, astra_*, theme_mods_*, woocommerce_*, spai_*) — sensitive keys (passwords, tokens, secrets) are always blocked
* Fix: npm MCP server (site-pilot-ai v2.1.2) — bun build now produces a proper single-file bundle instead of broken split files

= 1.1.6 =
* Fix: Screenshot base64 save now uses correct file extension (.jpg for JPEG, .png for PNG)
* Fix: Screenshot async webhook fires immediately for Cloudflare captures instead of scheduling broken mshots verification
* Fix: MCP error handler now detects all error response shapes consistently (no more silent failures)

= 1.1.5 =
* Fix: wp_fetch no longer wraps response in `item` key — data is now at top level like all other tools
* Fix: wp_create_theme_template and wp_build_page now auto-regenerate Elementor CSS after saving data
* Change: Default burst rate limit increased from 10 to 30 requests per 10 seconds

= 1.1.4 =
* Fix: Theme Builder conditions now update the global `elementor_pro_theme_builder_conditions` index — templates render correctly on the frontend
* New: `elementor_pro_theme_builder_conditions` added to wp_get_option allowlist for debugging

= 1.1.3 =
* Fix: wp_generate_image timeout increased from 60s to 90s for GPT-Image-1-Mini and Imagen 3
* Fix: wp_bulk_seo triple bug — now accepts flat `{id, title, description}` format (normalizes to internal format automatically)
* New: wp_build_page — build pages from semantic section blueprints (hero, features, cta, pricing, faq, testimonials, text, gallery)
* New: wp_create_theme_template — create and assign a Theme Builder template (header/footer/single/archive) in one call
* New: wp_get_elementor_summary — lightweight structural summary of Elementor pages (<1K tokens vs 64K+ full data)
* New: wp_delete_media — delete media attachments (with trash/force option)
* Update: OpenAI image generation switched from DALL-E 3 to GPT-Image-1-Mini (faster, cheaper, base64 output)
* Update: Gemini vision/text models upgraded from 2.0-flash to 2.5-flash

= 1.1.2 =
* New: Tool categories — every MCP tool now has an `annotations.category` field (content, media, elementor, seo, forms, gutenberg, taxonomy, site, webhooks, admin, ai)
* New: Category filtering on tools/list — pass `params.category` to get only tools in a specific category
* New: Admin page (Site Pilot AI > MCP Tools) — toggle entire tool categories on/off to reduce AI context noise
* New: Disabled categories are excluded from tools/list responses automatically

= 1.1.1 =
* Fix: wp_bulk_seo schema mismatch — tool now correctly sends `updates` param to match REST endpoint
* New: wp_bulk_create_posts — create multiple blog posts in one call (up to 50 per batch)
* New: wp_get_elementor_widgets now accepts optional `widget` param to return full controls schema
* Improved: wp_delete_post and wp_delete_webhook tool descriptions clarify behavior
* Updated: README with accurate tool counts and AI Integrations section

= 1.1.0 =
* New: AI Integrations — connect OpenAI, Gemini, ElevenLabs, and Pexels via admin settings page
* New: 8 MCP tools — wp_search_stock_photos, wp_download_stock_photo, wp_generate_image, wp_generate_featured_image, wp_generate_alt_text, wp_describe_image, wp_generate_excerpt, wp_text_to_speech
* New: Admin page (Site Pilot AI > Integrations) for managing API keys with test-connection and encrypted storage
* New: Free tier includes Pexels stock photo search and download; Pro tier unlocks AI generation tools
* New: Auto-provider selection — tools pick the best configured provider (OpenAI > Gemini) automatically

= 1.0.78 =
* Fix: MCP tools/list now emits `"properties": {}` instead of `"properties": []` for parameterless tools (JSON Schema compliance)
* New: Input validation on tools/call — missing required params and unknown params return clear errors with "did you mean?" suggestions
* Improved: REST dispatch errors now include the route for easier debugging

= 1.0.77 =
* New: post_type parameter on wp_create_post, wp_list_posts — create reusable blocks (synced patterns) with post_type=wp_block
* New: Support for any public custom post type through the posts controller
* Security: Blocked dangerous post types (attachment, revision, nav_menu_item, etc.)

= 1.0.76 =
* New: wp_bulk_create_pages — create multiple pages in one call (up to 50)
* New: wp_create_term, wp_update_term, wp_delete_term — full taxonomy management (categories, tags, custom)
* New: wp_get_theme_info — detailed theme info (parent, block vs classic, Elementor layout mode, templates)
* New: wp_flush_permalinks — flush rewrite rules via MCP
* New: wp_get_site_health — content counts, orphan pages, missing thumbnails, active plugins
* New: wp_set_noindex (Pro) — convenience tool for search engine noindex control
* New: slug parameter added to wp_create_post, wp_update_post, wp_create_page, wp_update_page

= 1.0.75 =
* Fix: Last Plugin Check issue — i18n translators comment for protocol description, feedback query annotation

= 1.0.74 =
* Fix: WordPress.org Plugin Check compliance — output escaping, nonce verification, input sanitization
* Fix: All direct database queries annotated with phpcs:ignore for table-name interpolation
* Fix: WP_Filesystem used for file move/chmod operations in media handler
* Fix: wp_delete_file/wp_parse_url used instead of PHP builtins
* Fix: Admin display uses sanitize_key for tab parameter, array_map sanitize for scoped key scopes

= 1.0.73 =
* Fix: Release script now honors .distignore — excludes tests/, vendor/, .sh files from distribution zip
* Fix: Freemius SDK update cache now fully cleared — deletes fs_updates% options and SDK transients
* Fix: Plugin self-update endpoint reliably detects new versions on first call

= 1.0.71 =
* Add: AI Site Context — master prompt / style guide stored in plugin settings
* Add: wp_get_site_context / wp_set_site_context MCP tools
* Add: REST endpoints GET/POST /site-context
* Add: Site context auto-included in wp_introspect response
* Add: Admin Settings → AI Site Context textarea with markdown support

= 1.0.70 =
* Add: Gutenberg block editor MCP tools — wp_get_blocks, wp_set_blocks, wp_list_block_types, wp_list_block_patterns
* Add: REST endpoints /blocks/{id} (GET/POST), /block-types (GET), /block-patterns (GET)
* Add: Gutenberg capability detection — tools auto-activate when block editor is available
* Add: Capability-aware filtering hides Gutenberg tools when Classic Editor forces classic mode

= 1.0.69 =
* Add: wp_get_post_meta / wp_set_post_meta MCP tools with blocked-key safety list
* Add: wp_get_option / wp_update_option MCP tools with whitelisted safe keys
* Add: wp_set_elementor_globals pro MCP tool for global colors, typography, button styles
* Add: REST endpoints /post-meta/{id} (GET/POST) and /option (GET/POST)
* Fix: Batch endpoint now sets body_params and query_params on internal requests
* Fix: update_option wrapped in try/catch to handle Elementor hook exceptions

= 1.0.68 =
* Add: Full menu MCP control — wp_get_menu, wp_create_menu, wp_update_menu pro tools
* Add: classes, target, description params on wp_add_menu_item and wp_update_menu_item
* Add: target and description support in menu REST endpoints
* Add: 14 Elementor Theme Builder widgets to validator (theme-post-info, theme-post-navigation, etc.)
* Add: wp_fetch now flags Elementor pages with elementor:true and usage hint
* Add: MCP server name includes site title (site-pilot-ai:SiteName)
* Fix: MCP proxy sets body_params on POST/PUT requests (fixes wp_set_seo title/description persistence)
* Fix: Freemius is_premium flag now dynamic to prevent "download premium" prompt after purchase
* Fix: Widget validator always merges hardcoded list with live registry to prevent false warnings

= 1.0.67 =
* Add: AI feedback system — wp_submit_feedback and wp_list_feedback MCP tools
* Add: REST endpoints POST/GET /feedback for bug reports, feature requests, and general feedback
* Add: Optional GitHub integration — auto-creates GitHub issues from AI feedback when configured
* Add: Settings for GitHub token and repo (Advanced tab)
* Add: spai_feedback database table for persistent feedback storage

= 1.0.66 =
* Add: Cloudflare Browser Rendering support for wp_screenshot_url (headless Chromium screenshots)
* Add: Settings for screenshot worker URL and auth token
* Add: Base64 screenshot saving to media library
* Fallback to WordPress mshots when Cloudflare worker not configured

= 1.0.65 =
* Add: wp_get_custom_css / wp_set_custom_css MCP tools for managing Additional CSS via API
* Add: REST endpoint /custom-css (GET and POST) with append/replace modes

= 1.0.64 =
* Add: Support slug updates via pages PUT endpoint

= 1.0.63 =
* Fix: Plugin URI and Author URI must differ (WordPress.org requirement)

= 1.0.62 =
* WordPress.org submission prep: HTTPS Plugin/Author URIs, .distignore, external service disclosure, plugin assets
* Condensed changelog for internal development releases

= 1.0.61 =
* Fix: REST update endpoint now clears Freemius SDK cache before checking — updates appear immediately via API
* Fix: OpenAPI spec version bumped to match plugin

= 1.0.60 =
* New: Capability-aware tool filtering — MCP tools/list only shows tools for installed plugins (Elementor, SEO, Forms)
* New: Helpful error messages when calling tools for plugins that aren't installed (e.g. "Tool requires Elementor to be installed")
* New: `get_required_capabilities()` method on tool registries — third-party integrations can declare plugin requirements
* Fix: OpenAPI spec SEO endpoint changed from PUT to POST to match MCP server and Cloudflare Worker

= 1.0.59 =
* Fix: Template apply now sets _elementor_template_type, versions, and regenerates CSS — pages render immediately after apply
* Refactor: Extracted get_all_tools(), get_all_tool_map(), get_registry_for_tool() in MCP controller — eliminated duplicated merge logic

= 1.0.58 =
* New: Integration Registry — third-party plugins can register MCP tools, REST endpoints, and capabilities via `spai_integrations` filter
* New: `Spai_Integration` abstract base class — extend to add AI support to any WordPress plugin
* New: Third-party tools automatically appear in MCP tools/list, /site-info capabilities, and detected integrations
* New: Integrations inherit API key auth via `Spai_Api_Auth` trait — use `$this->verify_api_key($request)` in permission callbacks

= 1.0.57 =
* New: Full element tree validation on Elementor save — auto-generates missing element IDs, validates widget types, checks nesting rules
* New: `warnings` array in Elementor save response — reports unknown widgets (with "did you mean?" suggestions), invalid nesting, auto-fixes applied
* New: `elementor_layout_mode` in site capabilities — reports whether site uses 'container' (Flexbox) or 'section' (classic) layout
* Improved: Widget type validation against Elementor's live registry with fallback to 70+ known widget types
* Improved: Structure validation catches missing `elType`, invalid `elements` arrays, widgets without `widgetType`

= 1.0.56 =
* New: Self-update REST endpoint (`GET/POST /update`) — check and trigger plugin updates via API (#87)
* New: Page-level settings support (`page_settings.custom_css`) on Elementor save (#81)
* New: Set `_elementor_pro_version` meta on save for Pro widget rendering
* Fix: Auto-rename flip-box widget keys (`front_title_text` → `title_text_a`, etc.) to match Elementor Pro schema (#83)
* Improved: Elementor GET response now includes `page_settings` field

= 1.0.55 =
* Fix: Set `_elementor_template_type` and `_elementor_version` meta on save — fixes frontend rendering failures (#88)
* Fix: Auto-rename invalid widget control keys (e.g. `title_size` → `title_typography_font_size`) to prevent Elementor renderer crashes (#90)
* Fix: 429 rate limit responses now always include JSON body with error details and `Retry-After` header (#92)
* New: Purge page cache after Elementor data update — supports SG Optimizer, WP Super Cache, W3TC, WP Rocket, LiteSpeed (#89)
* Improved: Elementor data saved via `update_post_meta` with verification, replacing unreliable `Document->save()` in REST context (#93)

= 1.0.44 - 1.0.54 =
* Internal development releases: Elementor validation improvements, security hardening, cache purging

= 1.0.43 =
* Fix: Pro MCP tools now unlock based on active license (single-plugin distribution), not a separate Pro add-on

= 1.0.42 =
* New: MCP tools for Elementor Theme Builder templates (get/create/update/delete)

= 1.0.41 =
* New: MCP tools for Elementor Theme Builder templates (get/create/update/delete)

= 1.0.21 - 1.0.40 =
* Internal development releases: Freemius integration, licensing, admin UI improvements

= 1.0.20 =
* New: MCP tool annotations (`readOnlyHint`, `openWorldHint`, `destructiveHint`) for safer AI tool usage
* New: MCP tools `wp_search` and `wp_fetch` for content discovery and retrieval
* New: REST endpoints `/search` and `/fetch`
* New: OAuth token endpoint `/oauth/token` (client credentials grant)
* New: OAuth admin settings (enable, client ID, client secret, token TTL)
* New: ChatGPT conformance and submission runbooks
* New: ChatGPT/MCP conformance test script
* Improved: Cloudflare MCP transport adapter supports configurable `auto/json/sse` response mode
* Improved: Notification requests now return HTTP 204 with empty body in worker transport

= 1.0.18 =
* New: Scoped API key lifecycle management (create, list, revoke) with key metadata
* New: API key scope enforcement (read/write/admin) across REST and MCP tool calls
* New: MCP tools for API key operations (wp_list_api_keys, wp_create_api_key, wp_revoke_api_key)
* New: CI workflow for PHP syntax lint, coding standards (tests), and PHPUnit
* New: PHPUnit test scaffolding and baseline coverage for auth/rate-limit/MCP critical flows
* Security: Legacy plaintext API keys are force-hashed during scoped-key migration
* Security: API key regeneration now revokes prior active scoped keys before rotating

= 1.0.17 =
* Security: Identity-aware rate limiting (separate buckets per API key vs IP)
* Security: Removed admin fallback in API user context (principle of least privilege)
* Security: Removed query parameter API key auth (prevents key leakage in URLs/logs)
* Security: SSRF protection on media upload-by-URL endpoint
* Security: Webhook re-validates URL at send time (DNS rebinding defense)
* Security: Webhook timeout reduced to 15s, redirects disabled, SSL enforced
* Fix: Rate limiter sliding window bug — transient TTL now uses remaining window time
* Fix: Rate limiter negative remaining counts prevented
* New: Rate-limit headers (X-RateLimit-*) on all SPAI REST responses
* New: Retry-After header on 429 responses
* New: MCP resources/list and resources/read handlers (spec compliance)
* New: MCP resources capability advertised in initialize response

= 1.0.16 =
* Fix: Freemius premium activation fatal error (switched to add-on architecture)
* Fix: Test Connection button now works reliably (bypasses internal REST dispatch)
* Fix: Pro plugin admin hook corrected for top-level menu
* Tested up to: WordPress 6.9.1

= 1.0.15 =
* Security: Removed manage_options from API agent role (principle of least privilege)
* Security: SSRF protection on webhooks and media upload from URL
* Security: API keys now use cryptographic random_bytes() generation
* Security: MCP batch requests capped at 10 per call
* Security: CORS now respects configured allowed_origins
* Security: Webhook delivery enforces SSL and disables redirect chains
* Security: Elementor data validated for size (5MB) and nesting depth
* New: Dedicated Spai_Security utility class
* New: Native MCP endpoint (/wp-json/site-pilot-ai/v1/mcp) for direct Claude connection
* New: Top-level admin menu with tabbed interface (Setup, Connect AI, Settings, Advanced)
* New: One-click Test Connection on Setup tab
* New: Copy-paste AI config guides for Claude Desktop, Claude Code, and ChatGPT
* New: First-activation welcome banner with visible API key
* New: License/Upgrade card on Settings tab for Freemius Pro activation
* Fixed: Freemius menu config aligned with top-level admin page
* Fixed: MCP namespace consistency (site-pilot-ai/v1)

= 1.0.14 =
* Security: API keys now hashed using wp_hash_password()
* Security: Dedicated spai_api_agent role with limited capabilities
* Security: New spai_bot service account for API requests
* Security: API key shown only once after regeneration
* Security: Freemius SDK calls wrapped in try-catch
* Fixed: is_premium flag corrected for free version

= 1.0.13 =
* Switched to Freemius for plugin updates
* Removed GitHub updater
* Removed uninstall.php (using Freemius after_uninstall hook)
* Improved uninstall cleanup

= 1.0.12 =
* Updated Freemius SDK integration
* Fixed function naming (spa_fs)
* Added multisite support
* Configured 14-day trial

= 1.0.11 =
* Added Freemius SDK for licensing and updates
* Added upgrade banner in admin
* License management abstraction layer

= 1.0.0 =
* Initial release
* Posts and pages CRUD operations
* Media upload (file and URL)
* Draft management
* Basic Elementor support
* API key authentication
* Activity logging
* Admin settings page

== Upgrade Notice ==

= 1.7.2 =
Self-hosted auto-updater replaces Freemius. All features free.

= 1.7.0 =
Major update: all 200+ MCP tools are now free. Freemius SDK removed. Renamed to Mumega Site Pilot AI.

= 1.0.0 =
Initial release. Control WordPress with AI assistants!

== Disclaimer ==

This plugin is provided "as is" without warranty of any kind, express or implied. Use at your own risk. The authors are not liable for any damages, data loss, or issues arising from the use of this plugin. This plugin modifies WordPress content (posts, pages, Elementor data, media, settings) through its MCP and REST API endpoints — always maintain backups before making changes via AI assistants. By using this plugin you accept full responsibility for changes made through its API.

== Privacy Policy ==

MUCP does not collect or transmit any user content to external servers. All content data stays on your WordPress installation. Activity logs are stored locally and can be configured or disabled in settings.

== External Services ==

This plugin may connect to the following external services depending on your configuration:

= WordPress.com mShots =
Used as a fallback for generating website screenshot thumbnails when no Cloudflare Browser Rendering worker is configured.
* Data sent: The URL of the page to screenshot
* When: When the wp_screenshot_url MCP tool is called and no custom screenshot worker is configured
* Service: https://s0.wp.com/mshots/v1/
* Privacy Policy: https://automattic.com/privacy/

= Feedback Relay (optional) =
When a user submits feedback via the wp_submit_feedback tool, the feedback is relayed to a central endpoint so plugin developers receive bug reports. This can be disabled by defining `SPAI_DISABLE_FEEDBACK_RELAY` as true.
* Data sent: Feedback text, site URL, site name, plugin version
* When: Only when the wp_submit_feedback MCP tool is explicitly called
* Endpoint: https://mucp.mumega.com/wp-json/site-pilot-ai/v1/feedback/relay
* Privacy Policy: https://mucp.mumega.com/privacy

= GitHub API (optional) =
If configured in settings, feedback can be automatically posted as GitHub issues.
* Data sent: Feedback text, site name, plugin version
* When: Only when GitHub integration is configured and feedback is submitted
* Service: https://api.github.com/
* Privacy Policy: https://docs.github.com/en/site-policy/privacy-policies/github-general-privacy-statement

= OpenAI API (optional) =
Used for AI image generation (wp_generate_image, wp_generate_featured_image) and alt text generation when configured by the user.
* Data sent: Text prompts or image URLs for processing
* When: Only when the user has configured an OpenAI API key and explicitly calls AI generation tools
* Service: https://api.openai.com/
* Terms of Service: https://openai.com/policies/terms-of-use
* Privacy Policy: https://openai.com/policies/privacy-policy

= Google Gemini API (optional) =
Used as an alternative AI provider for image description and text generation when configured by the user.
* Data sent: Text prompts or image URLs for processing
* When: Only when the user has configured a Gemini API key and explicitly calls AI generation tools
* Service: https://generativelanguage.googleapis.com/
* Terms of Service: https://ai.google.dev/gemini-api/terms
* Privacy Policy: https://policies.google.com/privacy

= ElevenLabs API (optional) =
Used for text-to-speech generation when configured by the user.
* Data sent: Text content to convert to speech
* When: Only when the user has configured an ElevenLabs API key and calls the wp_text_to_speech tool
* Service: https://api.elevenlabs.io/
* Terms of Service: https://elevenlabs.io/terms-of-use
* Privacy Policy: https://elevenlabs.io/privacy-policy

= Pexels API (optional) =
Used for stock photo search and download when configured by the user.
* Data sent: Search queries for stock photos
* When: Only when the user has configured a Pexels API key and calls wp_search_stock_photos or wp_download_stock_photo
* Service: https://api.pexels.com/
* Terms of Service: https://www.pexels.com/api/documentation/#guidelines
* Privacy Policy: https://www.pexels.com/privacy-policy/

== Support ==

* Documentation: [mucp.mumega.com/docs](https://mucp.mumega.com/docs)
* Support Forum: [wordpress.org/support/plugin/site-pilot-ai](https://wordpress.org/support/plugin/site-pilot-ai)
* GitHub: [github.com/Mumega-com/mcp-for-wp](https://github.com/Mumega-com/mcp-for-wp)
