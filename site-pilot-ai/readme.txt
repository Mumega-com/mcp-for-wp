=== Site Pilot AI ===
Contributors: digidinc
Donate link: https://sitepilotai.com
Tags: ai, claude, mcp, wordpress, elementor
Requires at least: 5.0
Tested up to: 6.9.1
Requires PHP: 7.4
Stable tag: 1.0.31
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

1. Admin dashboard with API key management
2. Detected plugins and capabilities
3. Available API endpoints
4. Creating a post with Claude
5. Elementor integration in action

== Changelog ==

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

Site Pilot AI does not collect or transmit any data to external servers. All data stays on your WordPress installation. Activity logs are stored locally and can be configured or disabled in settings.

== Support ==

* Documentation: [sitepilotai.com/docs](https://sitepilotai.com/docs)
* Support Forum: [wordpress.org/support/plugin/site-pilot-ai](https://wordpress.org/support/plugin/site-pilot-ai)
* GitHub: [github.com/Digidinc/site-pilot-ai](https://github.com/Digidinc/site-pilot-ai)
