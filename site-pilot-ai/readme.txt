=== Site Pilot AI ===
Contributors: digidinc
Donate link: https://sitepilotai.com
Tags: ai, claude, mcp, elementor, api
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.1.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Control WordPress with AI. Expose posts, pages, media, and Elementor to AI assistants via the Model Context Protocol (MCP).

== Description ==

Site Pilot AI lets you control your WordPress site using AI assistants like Claude. Using the Model Context Protocol (MCP), your AI assistant can create posts, manage pages, upload media, and work with Elementor - all through natural language.

= Key Features =

* **Content Management** - Create, edit, and delete posts and pages
* **Media Handling** - Upload files or import from URLs
* **Draft Management** - List and bulk-delete drafts
* **Basic Elementor** - Get and set Elementor page data
* **Secure API** - API key authentication with activity logging
* **MCP Compatible** - Works with Claude Code and Claude Desktop

= How It Works =

1. Install and activate the plugin
2. Copy your API key from Site Pilot AI in the admin menu
3. Configure your MCP server with the API key
4. Start controlling WordPress with natural language

= Example Commands =

* "Create a blog post about summer recipes"
* "List all draft pages"
* "Upload this image and set it as the featured image for post 123"
* "Get the Elementor data for the homepage"

= Pro Features =

Upgrade to Site Pilot AI Pro for advanced features:

* **Full Elementor Integration** - Templates, landing pages, clone pages, widget control
* **SEO Tools** - Yoast, RankMath, AIOSEO, SEOPress integration
* **Forms** - Contact Form 7, WPForms, Gravity Forms, Ninja Forms
* **Landing Page Builder** - One-click AI-generated landing pages
* **Priority Support** - 48-hour response time

[Learn more about Pro →](https://sitepilotai.com/pricing)

== Installation ==

= From WordPress Admin =

1. Go to Plugins → Add New
2. Search for "Site Pilot AI"
3. Click Install Now, then Activate
4. Go to Site Pilot AI in the admin menu to get your API key

= Manual Installation =

1. Download the plugin ZIP file
2. Go to Plugins → Add New → Upload Plugin
3. Select the ZIP file and click Install Now
4. Activate the plugin
5. Go to Site Pilot AI in the admin menu to get your API key

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

Model Context Protocol (MCP) is an open protocol that enables AI assistants like Claude to interact with external tools and services. Site Pilot AI exposes your WordPress site as an MCP-compatible tool.

= Is this secure? =

Yes. All requests require a unique API key. Keys are hashed using WordPress password hashing (not stored in plain text). A dedicated service account with limited capabilities handles API requests. Activity logging tracks all API usage for auditing.

= Does it work with any AI? =

Site Pilot AI works with any AI assistant that supports the MCP protocol. Currently, this includes Claude Code and Claude Desktop. More integrations are planned.

= Do I need coding skills? =

No. Once configured, you control WordPress through natural language. The AI handles all the technical details.

= What about Elementor? =

The free version includes basic Elementor support (get/set page data). The Pro version adds templates, landing pages, widgets, and full page building capabilities.

= Can I use this on multiple sites? =

Each site needs its own plugin installation and API key. The Pro version includes multi-site management features.

== Screenshots ==

1. Setup tab — Activity log showing recent API requests
2. Connect AI tab — One-click configuration for Claude Desktop and Claude Code
3. Settings tab — Free vs Pro feature comparison and license management
4. Advanced tab — REST API reference with copy-paste curl examples

== Changelog ==

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

= 1.0.20 =
Recommended update: adds ChatGPT-readiness improvements (search/fetch tools, safety annotations, OAuth client-credentials mode, transport compatibility, and conformance checks).

= 1.0.18 =
Recommended update: introduces scoped API key management with enforced permissions and improved release quality checks.

= 1.0.15 =
Major security hardening and new features: native MCP endpoint, redesigned admin UI, SSRF protection, and cryptographic API keys. Recommended for all users.

= 1.0.14 =
Security update: API keys now hashed, dedicated service account for API requests. Recommended for all users.

= 1.0.13 =
Plugin updates now handled via Freemius. Automatic updates will work seamlessly.

= 1.0.0 =
Initial release of Site Pilot AI. Control WordPress with AI assistants!

== Privacy Policy ==

Site Pilot AI does not collect or transmit any user content to external servers. All content data stays on your WordPress installation. Activity logs are stored locally and can be configured or disabled in settings.

== External Services ==

This plugin connects to the following external services:

= Freemius =
Site Pilot AI uses [Freemius](https://freemius.com/) for license management, analytics, and plugin updates.
* Data sent: Plugin version, WordPress version, PHP version, site URL (for license validation and update checks)
* Terms of Service: https://freemius.com/terms/
* Privacy Policy: https://freemius.com/privacy/

== Support ==

* Documentation: [sitepilotai.com/docs](https://sitepilotai.com/docs)
* Support Forum: [wordpress.org/support/plugin/site-pilot-ai](https://wordpress.org/support/plugin/site-pilot-ai)
* GitHub: [github.com/Digidinc/site-pilot-ai](https://github.com/Digidinc/site-pilot-ai)
