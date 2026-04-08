# Plugin & Theme Compatibility

mumcp auto-detects installed plugins and adapts its toolset accordingly. Here's what we support and how deep the integration goes.

## Compatibility Matrix

### Page Builders

| Plugin | Status | Tools | What mumcp can do |
|--------|--------|-------|-------------------|
| **Elementor** (Free) | Full support | 45 | Build pages from blueprints, edit widgets/sections, manage templates, validate data, regenerate CSS |
| **Elementor Pro** | Full support | +20 | Theme builder templates, archetypes, reusable parts, landing pages, globals, custom code |
| **Gutenberg** | Basic support | 4 | Get/set blocks, list block types and patterns |
| **Divi** | Not yet | 0 | Planned — needs widget schema mapping |
| **Beaver Builder** | Not yet | 0 | Planned |
| **Bricks** | Not yet | 0 | Community request |

### SEO

| Plugin | Status | Tools | Notes |
|--------|--------|-------|-------|
| **Yoast SEO** | Full | 10 | Meta title, description, focus keyword, noindex, social, canonical |
| **RankMath** | Full | 10 | Same normalized keys as Yoast |
| **All in One SEO** | Full | 10 | Same normalized keys |
| **SEOPress** | Detected | 0 | Detection only, tools planned |

### E-Commerce

| Plugin | Status | Tools | What mumcp can do |
|--------|--------|-------|-------------------|
| **WooCommerce** | Full | 21 | Products, orders, categories, tags, customers, analytics, product archetypes |
| **Easy Digital Downloads** | Not yet | 0 | Planned |
| **WP eCommerce** | Not yet | 0 | Low priority |

### LMS / Education

| Plugin | Status | Tools | What mumcp can do |
|--------|--------|-------|-------------------|
| **LearnPress** | Full | 18 | Courses, lessons, quizzes, quiz questions, curriculum, categories, stats |
| **LearnDash** | Not yet | 0 | Planned |
| **Tutor LMS** | Not yet | 0 | Community request |

### Forms

| Plugin | Status | Tools | What mumcp can do |
|--------|--------|-------|-------------------|
| **Contact Form 7** | Read | 4 | List forms, get form, get entries, status |
| **WPForms** | Read | 4 | Same |
| **Gravity Forms** | Read | 4 | Same |
| **Ninja Forms** | Read | 4 | Same |
| **Elementor Pro Forms** | Read | 4 | Via Elementor Pro forms integration |

### Media & AI

| Plugin / Service | Status | What mumcp can do |
|-----------------|--------|-------------------|
| **OpenAI API** | Optional | Image generation, alt text, content assistance |
| **Google Gemini** | Optional | Image description, text generation |
| **Pexels** | Optional | Stock photo search and upload |
| **ElevenLabs** | Optional | Text-to-speech audio generation |
| **Figma** | Optional | Design context import (personal token or OAuth) |
| **Screenshot Worker** | Optional | Cloudflare Browser Rendering for high-quality screenshots |

### Caching

| Plugin | Cache Purge | Notes |
|--------|-------------|-------|
| **SiteGround SG Optimizer** | Yes | URL purge + Supercacher global purge |
| **WP Super Cache** | Yes | `wp_cache_post_change()` |
| **W3 Total Cache** | Yes | `w3tc_flush_post()` |
| **WP Rocket** | Yes | `rocket_clean_post()` |
| **LiteSpeed Cache** | Yes | `LiteSpeed\Purge::purge_post()` |
| **WP Fastest Cache** | Yes | `wpfc_clear_post_cache_by_id()` |
| **Endurance Page Cache** | Yes | Via `clean_post_cache()` hook |
| **Redis Object Cache** | Partial | `wp_cache_flush_group()` if available |
| **Cloudflare** | Not yet | Planned — APO purge via API |

### Themes

| Theme | Status | Notes |
|-------|--------|-------|
| **Hello Elementor** | Tested | Recommended for Elementor sites |
| **Hello Plus** | Tested | Extended Hello Elementor |
| **Astra** | Tested | `astra-settings` option read/write supported |
| **GeneratePress** | Tested | `generate_settings` option read/write supported |
| **Eduma** | Tested | RTL support, custom CSS dual-write (`thim_custom_css`) |
| **TwentyTwenty-Five** | Tested | Block theme, Gutenberg focused |
| **Divi Theme** | Untested | Should work for content, not page builder |

### Multisite

| Feature | Status |
|---------|--------|
| Network activation | Yes |
| Per-site provisioning | Yes |
| Cross-site management | Yes (`wp_network_sites`, `wp_switch_site`) |
| Network admin page | Yes |

## How Detection Works

mumcp checks for plugins on every `wp_site_info` or `wp_introspect` call:

```php
'elementor'    => defined('ELEMENTOR_VERSION'),
'woocommerce'  => class_exists('WooCommerce'),
'yoast'        => defined('WPSEO_VERSION'),
'learnpress'   => defined('LEARNPRESS_VERSION'),
'cf7'          => class_exists('WPCF7'),
// ... etc
```

Tools for inactive plugins are automatically hidden from the MCP tools/list response.

## Extending mumcp

Third-party plugins can register their own MCP tools:

```php
add_filter('spai_integrations', function($integrations) {
    $integrations[] = new My_Plugin_MCP_Integration();
    return $integrations;
});
```

See [CONTRIBUTING.md](../CONTRIBUTING.md) for details on the integration API.

## Request a Plugin

Want mumcp to support your favorite plugin? [File a feature request](https://github.com/Mumega-com/mcp-for-wp/issues/new?template=feature_request.md) with the plugin name and what tools you'd need.
