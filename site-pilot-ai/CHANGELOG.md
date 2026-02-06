# Changelog

All notable changes to Site Pilot AI will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
