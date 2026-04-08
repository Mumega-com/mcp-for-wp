# Known Issues & Platform Workarounds

Issues in WordPress, Elementor, and hosting environments that mumcp works around. This is not a bug list for mumcp — it's a reference for developers who encounter these behaviors.

## Elementor

### Document::save() silently fails in REST/MCP context
- **Behavior:** `Document::save()` returns a non-WP_Error value but persists 0 elements
- **Cause:** Elementor's internal capability check in `save_elements()` silently bails without a logged-in user with editor caps
- **Impact:** Affects all Elementor save operations via API
- **Workaround:** mumcp always overwrites raw `_elementor_data` meta directly after `Document::save()` (meta_overwrite pattern). Applied in `set_elementor`, `save_elements_to_page`, `create_theme_template`.
- **Versions affected:** Elementor 3.x, 4.x

### Document cache holds stale data
- **Behavior:** `documents->get($id)` returns a cached document object even after meta has been updated
- **Cause:** `Documents_Manager` stores docs in a private `$_documents` array keyed by post ID
- **Impact:** CSS regeneration and same-request renders use stale data
- **Workaround:** Call `documents->get($id, false)` with `$from_cache = false` after meta writes

### CSS not regenerated after direct meta write
- **Behavior:** Elementor's CSS file is empty/stale after `update_post_meta('_elementor_data', ...)`
- **Cause:** `Document::save()` triggers CSS rebuild internally; direct meta writes don't
- **Workaround:** Delete `_elementor_css` meta + `Post::create($id)->update()` + CSS prime via loopback GET

### Counter widget only accepts numbers
- **Behavior:** Text values ("Free", "✓") cast to `0` in `ending_number`
- **Cause:** Counter widget's `ending_number` control is type `number`
- **Workaround:** mumcp's `build_stats` blueprint detects non-numeric values and uses heading widget instead

### Container flex children need explicit width
- **Behavior:** Flex container children with no `width` setting auto-size to content, all fit in one row
- **Cause:** Elementor defaults `_element_width` to `auto` in flexbox mode
- **Workaround:** mumcp sets `_element_width: initial` + `width: {size: 30, unit: '%'}` on card containers

### `isInner` required on nested containers
- **Behavior:** Nested container renders as `e-parent` instead of `e-child`, breaking flex layout
- **Cause:** Elementor uses `isInner` flag to differentiate parent vs child containers
- **Workaround:** mumcp validator auto-sets `isInner: true` on nested containers

## WordPress

### `wp_delete_post` fails on `elementor_library` in REST context
- **Behavior:** Returns `false` for custom post types via API
- **Cause:** Capability check uses `delete_post` cap which may not map to custom post types in REST
- **Workaround:** mumcp uses dedicated `delete_template` method with direct `wp_delete_post($id, $force)`

### Object cache serves stale post meta
- **Behavior:** `get_post_meta` returns old data immediately after `update_post_meta`
- **Cause:** WordPress object cache (especially with persistent backends like Redis/Memcached)
- **Workaround:** `wp_cache_delete($id, 'post_meta')` + `clean_post_cache($id)` after every write. Also `wp_cache_flush_group('post_meta')` if available.

## Hosting

### SiteGround Supercacher ignores URL-level purge
- **Behavior:** `sg_cachepress_purge_cache($url)` doesn't clear the full-page cache
- **Cause:** SiteGround's aggressive caching layer operates above WordPress
- **Workaround:** Also call `\SiteGround_Optimizer\Supercacher\Supercacher::purge_cache()` for global purge

### HostGator/Endurance page cache
- **Behavior:** Changes don't appear on frontend despite successful save
- **Cause:** `endurance-page-cache` must-use plugin caches aggressively
- **Workaround:** mumcp calls `clean_post_cache()` which triggers Endurance hooks

### Shared hosting WAF blocks large JSON payloads
- **Behavior:** `wp_set_elementor` with large payloads returns 403 or empty response
- **Cause:** ModSecurity or similar WAF rules on shared hosts
- **Workaround:** Use `elementor_data_base64` parameter to send base64-encoded JSON

## SEO Plugins

### Yoast, RankMath, AIOSEO use different meta keys
- **Behavior:** Each SEO plugin stores meta tags in different post meta keys
- **Impact:** Tools need to detect which plugin is active and use correct keys
- **Workaround:** mumcp's `wp_get_seo` / `wp_set_seo` auto-detect the active SEO plugin and normalize keys

## Contributing

Found a platform bug we should work around? [File an issue](https://github.com/Mumega-com/mcp-for-wp/issues/new?template=bug_report.md) with the label `platform-bug`.
