# Changelog

All notable changes to Site Pilot AI will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.43] - 2026-02-07

### Fixed
- Pro MCP tools now unlock based on active license (single-plugin distribution), not a separate Pro add-on.

## [1.0.42] - 2026-02-07

### Added
- MCP tools for Elementor Theme Builder templates (get/create/update/delete).

## [1.0.41] - 2026-02-07

### Added
- MCP tools for Elementor Theme Builder templates (get/create/update/delete).

## [1.0.40] - 2026-02-07

### Added
- `wp_introspect` MCP tool and `/introspect` REST endpoint so AI clients can discover tools, auth, and capabilities automatically.

### Changed
- Use `activate_plugins` for WP Admin access checks so all administrators can access the plugin screens on managed hosts.

## [1.0.39] - 2026-02-07

### Changed
- Switch Freemius integration to single-plugin distribution (no premium zip required after checkout).
- Allow Freemius `Account`/`Pricing` pages for administrators even if `manage_options` is restricted by the host.

## [1.0.38] - 2026-02-07

### Fixed
- Ensure Freemius `Account` and `Pricing` admin pages are registered under the plugin menu to prevent "Sorry, you are not allowed to access this page." on managed hosts.

## [1.0.37] - 2026-02-07

### Added
- Include license status in the `/site-info` API response so clients can detect Pro state.

## [1.0.36] - 2026-02-07

### Fixed
- Treat Freemius plan names case-insensitively so Agency/Pro plans correctly mark the site as Pro.
- Hide upgrade/Go Pro CTAs in plugin action links when Pro is active (including Freemius-injected links).

## [1.0.35] - 2026-02-07

### Added
- Alerting via existing SPAI webhooks on error spikes (5xx, 401/403).
- Privacy controls for logging: disable storing `response_data`, configurable redaction keys.
- Dashboard widget showing recent API activity.

## [1.0.34] - 2026-02-07

### Added
- WP Admin `Activity Log` page for traceability (request actions, endpoints, status codes, redacted details).

## [1.0.33] - 2026-02-07

### Fixed
- Improve RankMath detection and report LearnPress capability.

### Added
- Admin-scoped MCP tools for reading options (homepage), menus, generic content listing, and CPT deletion.

## [1.0.32] - 2026-02-07

### Changed
- Release automation update.

## [1.0.31] - 2026-02-07

### Changed
- Release automation update.

## [1.0.30] - 2026-02-06

### Changed
- Switched to single-plugin distribution (no separate `site-pilot-ai-premium` packaging). Pro stays license-gated.

## [1.0.29] - 2026-02-06

### Fixed
- Ensure Freemius updates include Pro module files by shipping `includes/pro/**` in both free + premium zips and gating Pro loading by license.

## [1.0.28] - 2026-02-06

### Fixed
- Correctly detect premium vs free mode in Freemius SDK (`is_premium`) so premium installs receive premium updates (and Pro modules ship).

## [1.0.27] - 2026-02-06

### Added
- Bundled all Pro endpoints into the premium package (SEO, Forms, Themes, Users, Widgets, WooCommerce, Multilang) with no separate Pro plugin install.

## [1.0.26] - 2026-02-06

### Added
- Bundled Elementor Pro endpoints into the premium package (no separate Pro plugin install).

## [1.0.25] - 2026-02-06

### Fixed
- Prevented premium activation fatal when both `site-pilot-ai` and `site-pilot-ai-premium` are installed at the same time.
- Restored Freemius premium-version configuration and detect premium/free mode from the plugin directory name.

## [1.0.24] - 2026-02-06

### Fixed
- Disabled Freemius premium-version auto swap to prevent checkout activation crashes on installs that use separate Pro plugin packaging.

## [1.0.23] - 2026-02-06

### Fixed
- Switched Freemius product configuration to premium-version mode (`has_premium_version: true`, `has_addons: false`) to match non-add-on distribution.

## [1.0.22] - 2026-02-06

### Fixed
- Prevented activation/deletion fatals when `spa_fs()` exists but does not return a valid Freemius instance.
- Guarded Freemius SDK hook registration (`add_filter`/`add_action`) behind runtime instance checks.
- Hardened license helper methods to safely handle unavailable Freemius instance methods.

## [1.0.21] - 2026-02-06

### Fixed
- Prevented activation/deletion fatals when `spa_fs()` exists but does not return a valid Freemius instance.
- Guarded Freemius SDK hook registration (`add_filter`/`add_action`) behind runtime instance checks.
- Hardened license helper methods to safely handle unavailable Freemius instance methods.

## [1.0.20] - 2026-02-06

### Added
- MCP tool annotations for safety hints: `readOnlyHint`, `openWorldHint`, `destructiveHint`
- New MCP tools: `wp_search` and `wp_fetch`
- New REST endpoints: `/search`, `/fetch`, and `/oauth/token` (client credentials grant)
- OAuth settings in admin: enable toggle, client ID, client secret (hashed), token TTL
- ChatGPT readiness documentation:
  - `docs/CHATGPT_CONFORMANCE.md`
  - `docs/CHATGPT_APP_SUBMISSION.md`
- ChatGPT/MCP conformance script: `tests/test-chatgpt-conformance.sh`

### Changed
- Cloudflare MCP transport adapter now supports configurable response mode (`auto`, `json`, `sse`)
- MCP notification handling in worker transport now returns `204` with empty body

### Security
- OAuth bearer tokens now enforce existing read/write/admin scope checks through API auth flow

## [1.0.18] - 2026-02-06

### Added
- Scoped API key lifecycle management: create, list, and revoke keys with metadata (`created_at`, `last_used_at`, `revoked_at`)
- API key scope enforcement (`read`, `write`, `admin`) across REST routes and MCP tool calls
- MCP tools for API key operations: `wp_list_api_keys`, `wp_create_api_key`, `wp_revoke_api_key`
- Development quality pipeline with CI checks for PHP syntax lint, coding standards (tests), and PHPUnit
- PHPUnit scaffolding and baseline tests for auth, rate limiter, and MCP endpoint behavior

### Security
- Legacy plaintext API keys are now force-hashed during migration into scoped key storage
- API key regeneration now revokes previous active scoped keys before issuing a new primary key

## [1.0.17] - 2026-02-06

### Security
- Identity-aware rate limiting: valid keys get `key:<hash>` bucket, invalid attempts get `invalid:<IP>` bucket
- Removed admin user fallback in `set_api_user_context()` — returns 500 if `spai_api_agent` user missing instead of silently operating as admin
- Removed `?api_key=` query parameter authentication — prevents key leakage in URLs, server logs, and Referer headers
- SSRF protection added to media upload-by-URL endpoint via `Spai_Security::validate_external_url()`
- Webhook URLs re-validated at send time to defend against DNS rebinding attacks
- Webhook delivery: timeout reduced to 15s, redirects disabled (`redirection => 0`), SSL enforced

### Fixed
- Rate limiter sliding window bug: `set_transient()` TTL now uses remaining window time instead of full 60/3600s (was extending window on every request)
- Rate limiter `remaining` count prevented from going negative with `max(0, ...)`
- Rate limiter window data properly validated and reset when expired or malformed

### Added
- `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `X-RateLimit-Reset` headers on all SPAI REST responses
- `Retry-After` header on 429 rate-limit responses
- MCP `resources/list` handler (returns empty resources array)
- MCP `resources/read` handler (returns resource-not-found error)
- MCP `resources` capability advertised in `initialize` response
- `is_api_key_match()` extracted method for cleaner auth validation

## [1.0.16] - 2026-02-06

### Fixed
- Freemius premium activation fatal error — switched from `has_premium_version` to `has_addons` architecture
- Test Connection button now works reliably — bypasses internal REST dispatch which doesn't carry API key headers
- Pro plugin admin hook corrected from `tools_page_site-pilot-ai` to `toplevel_page_site-pilot-ai`

### Changed
- Tested up to WordPress 6.9.1

## [1.0.15] - 2026-02-06

### Security
- Removed `manage_options` and `edit_theme_options` from `spai_api_agent` role (principle of least privilege)
- Added SSRF protection: `Spai_Security::validate_external_url()` blocks private/reserved IPs on webhooks and media upload
- API keys now generated with `bin2hex(random_bytes(24))` — 192 bits of cryptographic randomness
- MCP batch requests capped at 10 per call (was unlimited)
- CORS now respects `allowed_origins` setting; wildcard only when unconfigured
- Webhook delivery: explicit `sslverify => true`, redirects disabled (`redirection => 0`)
- Elementor data validated for max size (5MB) and nesting depth (30 levels)
- Migration: existing installs auto-strip `manage_options` from API role on activation

### Added
- New `Spai_Security` utility class for SSRF and payload validation
- Native MCP endpoint (`POST /wp-json/site-pilot-ai/v1/mcp`) — direct Claude Desktop/Code connection
- Top-level admin menu with SVG icon (moved from buried Tools submenu)
- Tabbed admin interface: Setup, Connect AI, Settings, Advanced
- One-click Test Connection button with AJAX site-info check
- Copy-paste AI config guides: Claude Desktop, Claude Code, ChatGPT
- First-activation welcome banner with visible API key and copy button
- License/Upgrade card on Settings tab for Freemius Pro activation
- `do_action('spai_admin_settings_cards')` hook for Pro extensions
- ChatGPT OpenAPI spec (`docs/openapi-chatgpt.yaml`, 17 endpoints)
- Cloudflare Worker MCP handler for remote MCP connections
- npm MCP server package with `--setup`, `--version`, `--test` CLI flags

### Fixed
- Fatal error: `log_activity()` access level conflict in MCP class (private vs protected inheritance)
- MCP activity logging: corrected column names to match `wp_spai_activity_log` schema
- Freemius `first-path` aligned with new top-level menu (`admin.php?page=site-pilot-ai`)
- MCP namespace consistency: all components use `site-pilot-ai/v1`
- Admin page hook: `toplevel_page_site-pilot-ai` (was `tools_page_...`)
- MCP tool count: 30 tools (removed 6 non-existent endpoint mappings)

## [1.0.14] - 2026-02-05

### Security
- API keys now hashed using `wp_hash_password()` instead of plain text storage
- New `spai_api_agent` role with limited capabilities (not full admin)
- New `spai_bot` service account for handling API requests
- API key shown only once after regeneration, then masked in UI
- Legacy plain-text keys auto-migrate to hashed on first API request
- Freemius SDK private method calls wrapped in try-catch

### Fixed
- Corrected `is_premium` flag to `false` for free version
- Improved backward compatibility for existing installations

## [1.0.13] - 2026-02-05

### Changed
- Switched plugin updates from GitHub to Freemius
- Removed custom GitHub updater class
- Removed `uninstall.php` (using Freemius `after_uninstall` hook instead)

### Added
- Transient and cron cleanup in Freemius uninstall hook

### Removed
- `class-spai-updater.php`
- GitHub token settings from admin

## [1.0.12] - 2026-02-04

### Changed
- Updated Freemius SDK integration with correct configuration
- Renamed `spai_fs()` to `spa_fs()` per Freemius conventions
- Added multisite network support (`WP_FS__PRODUCT_23824_MULTISITE`)

### Added
- 14-day trial configuration with payment requirement
- Custom connect message for opt-in screen

## [1.0.11] - 2026-02-04

### Added
- Freemius SDK integration for licensing and updates
- License management abstraction layer (`Spai_License` class)
- Upgrade banner in admin dashboard
- Support for Pro, Agency plans via Freemius

### Changed
- License checking now uses unified interface (supports Freemius or custom backend)

## [1.0.0] - 2024-01-01

### Added
- Initial release
- REST API with 14 endpoints
- Posts CRUD operations (create, read, update, delete)
- Pages CRUD operations
- Media upload (file and URL)
- Draft management (list and bulk delete)
- Basic Elementor support (get/set page data, create Elementor page)
- API key authentication
- Activity logging with configurable retention
- Admin settings page
- Plugin detection for Elementor, SEO plugins, form plugins, WooCommerce
- WordPress.org compliant readme.txt
- Internationalization support
- Clean uninstall

### Security
- CSRF protection with nonces
- Capability checks on all admin actions
- Input sanitization using WordPress functions
- Output escaping
- Secure API key generation
- Rate limiting ready architecture

## [Unreleased]

### Planned
- Pro add-on with full Elementor integration
- SEO module (Yoast, RankMath, AIOSEO, SEOPress)
- Forms module (CF7, WPForms, Gravity Forms, Ninja Forms)
- Landing page builder
- Template management
- Agency tier with multi-site support
