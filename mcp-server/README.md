# Site Pilot AI

[![npm version](https://img.shields.io/npm/v/site-pilot-ai.svg)](https://www.npmjs.com/package/site-pilot-ai)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

**MCP Server for WordPress** — Manage posts, pages, SEO, forms & Elementor from Claude Desktop, Cursor, Windsurf and any MCP client.

A thin stdio-to-HTTP proxy that forwards all MCP requests to your WordPress site's built-in MCP endpoint. Tools are always in sync with the plugin — zero local definitions, zero maintenance.

## How It Works

```
MCP Client (stdio) → site-pilot-ai (proxy) → WordPress Plugin (JSON-RPC over HTTP)
```

The WordPress plugin exposes a complete MCP endpoint at `/wp-json/site-pilot-ai/v1/mcp`. This npm package connects to it and proxies `tools/list`, `tools/call`, `resources/list`, and `resources/read` — so every tool the plugin provides is automatically available to your AI client.

- **65+ tools** — content, Elementor, SEO, forms, media, settings, and more
- **Zero dependencies** — single-file bundle, runs on Node 18+
- **Always in sync** — update the plugin, tools appear instantly

## Quick Start

### 1. Install WordPress Plugin

Install **Site Pilot AI** on your WordPress site:
1. Download from [GitHub releases](https://github.com/Digidinc/wp-ai-operator/releases)
2. Upload to WordPress: **WP Admin > Plugins > Add New > Upload Plugin**
3. Activate and copy your API key from **Site Pilot AI** (top-level admin menu)

### 2. Run Setup Wizard

```bash
npx -y site-pilot-ai --setup
```

This will:
- Prompt for your WordPress URL and API key
- Test the connection
- Save configuration to `~/.wp-ai-operator/config.json`
- Show Claude Desktop config snippet

### 3. Configure Your MCP Client

**Claude Desktop** — add to `claude_desktop_config.json`:

```json
{
  "mcpServers": {
    "wordpress": {
      "command": "npx",
      "args": ["-y", "site-pilot-ai"],
      "env": {
        "WP_URL": "https://your-site.com",
        "WP_API_KEY": "your-api-key"
      }
    }
  }
}
```

**Cursor / Windsurf** — same format in their MCP settings.

### 4. Restart Your Client

Tools appear automatically. Try: *"Show me my site info"*

## Available Tools

All tools come from the WordPress plugin. Update the plugin to get new tools — no npm update needed.

**Content Management** — `wp_list_posts`, `wp_create_post`, `wp_update_post`, `wp_delete_post`, `wp_list_pages`, `wp_create_page`, `wp_update_page`, `wp_delete_page`, `wp_search`, `wp_list_drafts`, `wp_delete_all_drafts`

**Site & Settings** — `wp_site_info`, `wp_introspect`, `wp_analytics`, `wp_detect_plugins`, `wp_get_options`, `wp_update_options`, `wp_get_settings`, `wp_update_settings`

**Media** — `wp_upload_media`, `wp_upload_media_from_url`, `wp_list_media`, `wp_delete_media`

**Elementor** — `wp_get_elementor`, `wp_set_elementor`, `wp_list_elementor_templates`, `wp_apply_elementor_template`, `wp_create_landing_page`, `wp_add_elementor_section`, `wp_update_elementor_widget`, `wp_get_elementor_globals`, `wp_clone_elementor_page`

**SEO** (requires Yoast / RankMath / AIOSEO / SEOPress) — `wp_get_seo`, `wp_set_seo`, `wp_analyze_seo`, `wp_bulk_seo`, `wp_get_seo_plugin`

**Forms** (requires CF7 / WPForms / Gravity Forms / Elementor Pro) — `wp_list_forms`, `wp_get_form`, `wp_create_form`, `wp_update_form`, `wp_delete_form`, `wp_get_form_submissions`, `wp_submit_form`, `wp_get_form_plugin`

**...and more.** Run `npx site-pilot-ai --test` to connect and see the full list.

## Configuration

### Environment Variables

```bash
WP_URL=https://your-site.com       # WordPress site URL
WP_API_KEY=spai_...                 # Site Pilot AI API key
WP_SITE_NAME=default                # Optional, for multi-site configs
WP_CONFIG_PATH=~/custom/config.json # Optional, custom config path
```

### Config File

Location: `~/.wp-ai-operator/config.json`

```json
{
  "sites": {
    "default": {
      "url": "https://your-site.com",
      "apiKey": "spai_...",
      "name": "My Site"
    },
    "staging": {
      "url": "https://staging.your-site.com",
      "apiKey": "spai_...",
      "name": "Staging"
    }
  },
  "defaultSite": "default"
}
```

Environment variables take priority over the config file.

## CLI Commands

```bash
npx site-pilot-ai              # Start MCP server (stdio transport)
npx site-pilot-ai --setup      # Interactive setup wizard
npx site-pilot-ai --test       # Test WordPress connection
npx site-pilot-ai --version    # Show version
npx site-pilot-ai --help       # Show help
```

## Troubleshooting

### Connection Failed

```bash
npx site-pilot-ai --test
```

Verify:
1. WordPress site is accessible
2. Site Pilot AI plugin is activated
3. API key is correct (regenerate in WP Admin if needed)
4. REST API is not blocked by firewall or security plugin

### No Tools Appearing

1. Restart your MCP client
2. Check config: `cat ~/.wp-ai-operator/config.json`
3. Test connection: `WP_URL=... WP_API_KEY=... npx site-pilot-ai --test`
4. Check client logs for MCP errors

### Plugin Requirements

**Required:**
- WordPress 5.9+
- Site Pilot AI plugin

**Optional (enables more tools):**
- **Elementor** / Elementor Pro — page builder tools
- **Yoast SEO** / RankMath / AIOSEO / SEOPress — SEO tools
- **Contact Form 7** / WPForms / Gravity Forms — form tools

## Development

```bash
git clone https://github.com/Digidinc/wp-ai-operator.git
cd wp-ai-operator/mcp-server
bun install
bun run build       # Single-file bundle to dist/index.js
node dist/index.js --test
```

## License

MIT © DigID

---

**Documentation:** https://github.com/Digidinc/wp-ai-operator
**Issues:** https://github.com/Digidinc/wp-ai-operator/issues
**WordPress Plugin:** https://github.com/Digidinc/wp-ai-operator/releases
