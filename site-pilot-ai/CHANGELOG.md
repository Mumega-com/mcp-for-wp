# Changelog

All notable changes to Site Pilot AI will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
