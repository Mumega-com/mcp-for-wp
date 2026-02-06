# Site Pilot AI

[![npm version](https://img.shields.io/npm/v/site-pilot-ai.svg)](https://www.npmjs.com/package/site-pilot-ai)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

**MCP Server for WordPress** - Manage posts, pages, SEO, forms & Elementor from Claude Desktop, Cursor, Windsurf and any MCP client.

Control your WordPress sites with AI through the Model Context Protocol. Built on a microkernel architecture with pluggable extensions.

## Quick Start

### 1. Run Setup Wizard

```bash
npx -y site-pilot-ai --setup
```

This will:
- Prompt for your WordPress URL and API key
- Test the connection
- Save configuration to `~/.wp-ai-operator/config.json`
- Show Claude Desktop config

### 2. Install WordPress Plugin

Install the **Site Pilot AI** plugin on your WordPress site:
1. Download from [GitHub releases](https://github.com/Digidinc/wp-ai-operator/releases)
2. Upload to WordPress: **WP Admin > Plugins > Add New > Upload Plugin**
3. Activate and copy your API key from **Site Pilot AI** (top-level admin menu)

### 3. Configure Claude Desktop

Add to your `claude_desktop_config.json`:

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

### 4. Restart Claude Desktop

Tools will appear automatically. Try: "Show me my site info"

## Features

30 tools across 4 extensions:

### Core (14 tools)
- `wp_site_info` - Site information (theme, plugins, stats)
- `wp_analytics` - API activity logs
- `wp_detect_plugins` - Detect installed plugins
- `wp_list_posts` / `wp_create_post` / `wp_update_post` / `wp_delete_post` - Post management
- `wp_list_pages` / `wp_create_page` / `wp_update_page` - Page management
- `wp_upload_media` / `wp_upload_media_from_url` - Media library
- `wp_list_drafts` / `wp_delete_all_drafts` - Draft cleanup

### SEO (5 tools)
Works with **Yoast SEO, RankMath, All-in-One SEO, SEOPress**:
- `wp_get_seo` / `wp_set_seo` - SEO metadata (title, description, Open Graph)
- `wp_analyze_seo` - SEO quality analysis with suggestions
- `wp_bulk_seo` - Batch SEO updates
- `wp_get_seo_plugin` - Detect active SEO plugin

### Forms (8 tools)
Works with **Contact Form 7, WPForms, Gravity Forms, Elementor Forms, Ninja Forms**:
- `wp_list_forms` / `wp_get_form` - Browse forms
- `wp_create_form` / `wp_update_form` / `wp_delete_form` - Form builder
- `wp_get_form_submissions` - View submissions
- `wp_submit_form` - Programmatic submissions
- `wp_get_form_plugin` - Detect active form plugin

### Elementor (9 tools)
Full page builder control:
- `wp_get_elementor` / `wp_set_elementor` - Page data (sections, widgets)
- `wp_list_elementor_templates` / `wp_apply_elementor_template` - Template system
- `wp_create_landing_page` - Complete landing pages (hero, features, CTA)
- `wp_add_elementor_section` - Add sections to pages
- `wp_update_elementor_widget` - Modify widgets
- `wp_get_elementor_globals` - Global colors/typography
- `wp_clone_elementor_page` - Duplicate pages

## Configuration

### Environment Variables

```bash
WP_URL=https://your-site.com
WP_API_KEY=your-api-key
WP_SITE_NAME=default  # Optional, for multi-site configs
```

### Config File Format

Location: `~/.wp-ai-operator/config.json`

```json
{
  "sites": {
    "default": {
      "url": "https://your-site.com",
      "apiKey": "your-api-key",
      "name": "My Site"
    },
    "staging": {
      "url": "https://staging.your-site.com",
      "apiKey": "staging-key",
      "name": "Staging"
    }
  },
  "defaultSite": "default",
  "enabledExtensions": ["core", "seo", "forms", "elementor"]
}
```

### Multi-Site Management

Use the `site` parameter in tool calls:

```json
{
  "tool": "wp_create_post",
  "arguments": {
    "site": "staging",
    "title": "Test Post",
    "content": "<p>Hello world</p>"
  }
}
```

Or set `WP_SITE_NAME` environment variable.

## CLI Commands

```bash
# Start MCP server (stdio transport)
site-pilot-ai

# Run interactive setup wizard
site-pilot-ai --setup

# Test WordPress connection
site-pilot-ai --test

# Show version
site-pilot-ai --version

# Show help
site-pilot-ai --help
```

## Tool Reference

| Tool | Extension | Purpose | Key Parameters |
|------|-----------|---------|----------------|
| **Site Management** | | | |
| `wp_site_info` | Core | Site info (theme, plugins, stats) | `site?` |
| `wp_analytics` | Core | API activity logs | `days?, site?` |
| `wp_detect_plugins` | Core | Detect installed plugins | `site?` |
| **Posts** | | | |
| `wp_list_posts` | Core | List posts | `per_page?, status?, site?` |
| `wp_create_post` | Core | Create post | `title, content, status?, categories?, tags?, site?` |
| `wp_update_post` | Core | Update post | `id, title?, content?, status?, site?` |
| `wp_delete_post` | Core | Delete post | `id, force?, site?` |
| **Pages** | | | |
| `wp_list_pages` | Core | List pages | `per_page?, status?, site?` |
| `wp_create_page` | Core | Create page | `title, content?, status?, template?, site?` |
| `wp_update_page` | Core | Update page | `id, title?, content?, status?, site?` |
| **Media** | | | |
| `wp_upload_media` | Core | Upload file | `file_path, site?` |
| `wp_upload_media_from_url` | Core | Upload from URL | `url, filename, site?` |
| **Drafts** | | | |
| `wp_list_drafts` | Core | List drafts | `site?` |
| `wp_delete_all_drafts` | Core | Bulk delete drafts | `site?` |
| **SEO** | | | |
| `wp_get_seo` | SEO | Get SEO metadata | `post_id, site?` |
| `wp_set_seo` | SEO | Set SEO metadata | `post_id, title?, description?, focus_keyword?, og_*, site?` |
| `wp_analyze_seo` | SEO | Analyze SEO quality | `post_id, keyword?, site?` |
| `wp_bulk_seo` | SEO | Batch SEO updates | `updates[], site?` |
| `wp_get_seo_plugin` | SEO | Detect SEO plugin | `site?` |
| **Forms** | | | |
| `wp_list_forms` | Forms | List all forms | `plugin?, site?` |
| `wp_get_form` | Forms | Get form details | `form_id, plugin?, site?` |
| `wp_create_form` | Forms | Create form | `title, plugin, fields[], settings?, site?` |
| `wp_update_form` | Forms | Update form | `form_id, plugin, fields?, settings?, site?` |
| `wp_delete_form` | Forms | Delete form | `form_id, plugin, site?` |
| `wp_get_form_submissions` | Forms | View submissions | `form_id, plugin?, limit?, site?` |
| `wp_submit_form` | Forms | Submit form data | `form_id, plugin, data, site?` |
| `wp_get_form_plugin` | Forms | Detect form plugin | `site?` |
| **Elementor** | | | |
| `wp_get_elementor` | Elementor | Get page data | `page_id, site?` |
| `wp_set_elementor` | Elementor | Set page data | `page_id, elementor_data, site?` |
| `wp_list_elementor_templates` | Elementor | List templates | `type?, site?` |
| `wp_apply_elementor_template` | Elementor | Apply template | `page_id, template_id, mode?, site?` |
| `wp_create_landing_page` | Elementor | Create landing page | `title, headline, cta_text, cta_url, features?, testimonials?, colors?, site?` |
| `wp_add_elementor_section` | Elementor | Add section | `page_id, position?, section_data, site?` |
| `wp_update_elementor_widget` | Elementor | Update widget | `page_id, widget_id, settings, site?` |
| `wp_get_elementor_globals` | Elementor | Get global styles | `site?` |
| `wp_clone_elementor_page` | Elementor | Clone page | `page_id, new_title, site?` |

## Troubleshooting

### Connection Failed

```bash
# Test connection
site-pilot-ai --test

# Verify:
# 1. WordPress site is accessible
# 2. Site Pilot AI plugin is active
# 3. API key is correct
# 4. Firewall allows REST API access
```

### Invalid API Key

- Check **WP Admin > Site Pilot AI**
- Regenerate key if needed
- Update config: `site-pilot-ai --setup`

### Plugin Requirements

**Required:**
- Site Pilot AI plugin (included with package)

**Optional (for extensions):**
- **SEO:** Yoast SEO / RankMath / All-in-One SEO / SEOPress
- **Forms:** Contact Form 7 / WPForms / Gravity Forms / Elementor Pro / Ninja Forms
- **Elementor:** Elementor / Elementor Pro

### Missing Tools

If tools don't appear:
1. Restart Claude Desktop
2. Check config file exists: `~/.wp-ai-operator/config.json`
3. Verify env vars: `echo $WP_URL $WP_API_KEY`
4. Check MCP server logs in Claude Desktop

### Permission Errors

Ensure WordPress user has:
- `edit_posts`, `edit_pages` - For content management
- `manage_options` - For site settings
- `upload_files` - For media uploads

## Architecture

**Microkernel design:**
- **Kernel** - Core API client, config management, multi-site routing
- **Extensions** - Pluggable modules (Core, SEO, Forms, Elementor)
- **Gateway** - MCP protocol adapter (stdio transport)

Extensions are loaded dynamically and can be enabled/disabled in config.

## Development

```bash
git clone https://github.com/Digidinc/wp-ai-operator.git
cd wp-ai-operator/mcp-server
npm install
npm run build

# Test locally
node dist/index.js --test
```

## License

MIT © DigID

---

**Documentation:** https://github.com/Digidinc/wp-ai-operator
**Issues:** https://github.com/Digidinc/wp-ai-operator/issues
**WordPress Plugin:** https://github.com/Digidinc/wp-ai-operator/releases
