# WP AI Operator

<p align="center">
  <strong>Control WordPress with AI</strong><br>
  MCP Server + WordPress Plugin + OpenClaw Skill
</p>

<p align="center">
  <a href="#quick-start">Quick Start</a> •
  <a href="#installation">Installation</a> •
  <a href="#features">Features</a> •
  <a href="#tools">36 Tools</a> •
  <a href="#multi-site">Multi-Site</a> •
  <a href="#python-client">Python Client</a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/version-2.0.0-blue" alt="Version">
  <img src="https://img.shields.io/badge/plugin-v1.0.15-green" alt="Plugin">
  <img src="https://img.shields.io/badge/MCP-compatible-green" alt="MCP">
  <img src="https://img.shields.io/badge/WordPress-5.0%2B-blue" alt="WordPress">
  <img src="https://img.shields.io/badge/license-GPL--2.0-orange" alt="License">
</p>

---

```
You: "Create a landing page for our summer sale with hero, features, and testimonials"

Claude: *Creates page, builds Elementor layout, sets SEO metadata*
        "Done! https://yoursite.com/summer-sale (draft)"
```

## Quick Start

1. **Install Site Pilot AI** on your WordPress site (from Plugins → Add New or upload manually)
2. **Copy your API key** from WP Admin → Site Pilot AI
3. **Connect Claude Desktop, Claude Code, or ChatGPT:**
   - **Native MCP endpoint**: `/wp-json/site-pilot-ai/v1/mcp` (recommended, no external server needed)
   - **npm MCP server**: `npm install -g wp-ai-operator` → `wp-ai-operator --setup`

That's it! Now your AI assistant can control WordPress directly.

## Overview

WP AI Operator is a complete solution for controlling WordPress sites with AI assistants like Claude Code and Claude Desktop. It uses the **Model Context Protocol (MCP)** to expose WordPress functionality as AI-callable tools.

### Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                   Claude Code / Desktop / ChatGPT                │
└─────────────────────────────────────────────────────────────────┘
                                │ MCP
                  ┌─────────────┼─────────────┐
                  │             │             │
                  ▼             ▼             ▼
        ┌──────────────┐ ┌──────────┐ ┌──────────────┐
        │   Native MCP │ │npm Server│ │   Cloudflare │
        │  (WordPress) │ │ (Node.js)│ │    Worker    │
        └──────┬───────┘ └────┬─────┘ └──────┬───────┘
               │              │               │
               └──────────────┼───────────────┘
                              │ REST API
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    WordPress Plugin (PHP)                        │
│         site-pilot-ai.php • 40+ REST Endpoints                   │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────────────┐   │
│  │   Core   │ │   SEO    │ │  Forms   │ │    Elementor     │   │
│  │ 14 tools │ │ 5 tools  │ │ 8 tools  │ │     9 tools      │   │
│  └──────────┘ └──────────┘ └──────────┘ └──────────────────┘   │
│                      Microkernel Architecture                    │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│  WordPress  │  Elementor  │  Yoast/RankMath  │  CF7/WPForms    │
└─────────────────────────────────────────────────────────────────┘
```

## Distribution Channels

Site Pilot AI is available through **2 distribution channels**:

| Channel | Purpose | When to Use |
|---------|---------|-------------|
| **Native MCP Endpoint** | Direct WordPress connection | Recommended for all setups |
| **npm MCP Server** | Local MCP server with multi-site config | Multiple sites, local development |

### Native MCP Endpoint

Built-in MCP server at `/wp-json/site-pilot-ai/v1/mcp` — no external dependencies needed.

```json
{
  "mcpServers": {
    "wordpress": {
      "command": "curl",
      "args": ["-X", "POST", "https://yoursite.com/wp-json/site-pilot-ai/v1/mcp",
               "-H", "X-API-Key: spai_your_key",
               "-H", "Content-Type: application/json",
               "--data-binary", "@-"]
    }
  }
}
```

### npm MCP Server

```bash
npm install -g wp-ai-operator
wp-ai-operator --setup    # Interactive setup
wp-ai-operator --test     # Test connection
```

## Features

| Category | Capabilities |
|----------|-------------|
| **Content** | Posts, Pages, Media uploads, Drafts management |
| **Page Builders** | Elementor Pro: layouts, widgets, templates, landing pages |
| **SEO** | Yoast, RankMath, AIOSEO, SEOPress integration |
| **Forms** | Contact Form 7, WPForms, Gravity Forms, Ninja Forms |
| **Multi-Site** | Manage multiple WordPress sites from one config |
| **OpenClaw** | Integrated skill for Claude Code discovery |

## Installation

### 1. WordPress Plugin (Site Pilot AI v1.0.15)

Upload `site-pilot-ai/site-pilot-ai.php` to your WordPress site:

```bash
# Copy to plugins folder
scp site-pilot-ai/site-pilot-ai.php user@server:/var/www/html/wp-content/plugins/

# Or via WP-CLI
wp plugin install /path/to/site-pilot-ai.php --activate
```

Activate in **WordPress Admin → Plugins**, then get your API key from **Site Pilot AI** (top-level menu).

### 2. MCP Server (npm)

```bash
# Clone repository
git clone https://github.com/Digidinc/wp-ai-operator.git
cd wp-ai-operator/mcp-server

# Install and build
npm install
npm run build
```

### 3. Configure Claude Code

Add to `~/.claude.json`:

```json
{
  "mcpServers": {
    "site-pilot-ai": {
      "command": "node",
      "args": ["/path/to/wp-ai-operator/mcp-server/dist/index.js"],
      "env": {
        "WP_URL": "https://yoursite.com",
        "WP_API_KEY": "spai_your_api_key_here"
      }
    }
  }
}
```

Or use a config file for multiple sites:

```json
{
  "mcpServers": {
    "site-pilot-ai": {
      "command": "node",
      "args": ["/path/to/wp-ai-operator/mcp-server/dist/index.js"],
      "env": {
        "WP_CONFIG_PATH": "~/.wp-ai-operator/config.json"
      }
    }
  }
}
```

### 4. OpenClaw Skill (Optional)

The skill is pre-installed at `~/.agents/skills/wp-ai-operator/` and symlinked to OpenClaw. It enables keyword-based activation when you mention WordPress, Elementor, SEO, or forms.

## Development Quality Checks

Site Pilot AI now includes CI checks for PHP linting, coding standards (tests), and unit tests.

```bash
cd site-pilot-ai
composer install
composer test
composer lint:tests
```

GitHub Actions workflow: `.github/workflows/site-pilot-ai-ci.yml`

## Tools

### Core Extension (14 tools)

| Tool | Description |
|------|-------------|
| `wp_site_info` | Get site name, version, theme, active plugins |
| `wp_analytics` | API activity logs and metrics |
| `wp_detect_plugins` | Detect installed plugin capabilities |
| `wp_list_posts` | List posts with filters (status, category, pagination) |
| `wp_get_post` | Get single post by ID |
| `wp_create_post` | Create new post with categories, tags, featured image |
| `wp_update_post` | Update existing post |
| `wp_delete_post` | Delete or trash post |
| `wp_list_pages` | List pages with status filter |
| `wp_create_page` | Create page with Elementor support |
| `wp_update_page` | Update page content or Elementor data |
| `wp_upload_media` | Upload file from local path |
| `wp_upload_media_from_url` | Upload media from external URL |
| `wp_list_drafts` | List all drafts (posts and pages) |
| `wp_delete_all_drafts` | Bulk delete all drafts |

### SEO Extension (5 tools)

Supports: **Yoast SEO**, **RankMath**, **AIOSEO**, **SEOPress**

| Tool | Description |
|------|-------------|
| `wp_get_seo` | Get SEO title, description, focus keyword, canonical |
| `wp_set_seo` | Set SEO metadata (syncs to active SEO plugin) |
| `wp_analyze_seo` | Analyze content with score and recommendations |
| `wp_bulk_seo` | Bulk update SEO for multiple posts |
| `wp_get_seo_plugin` | Detect which SEO plugin is active |

### Forms Extension (8 tools)

Supports: **Contact Form 7**, **WPForms**, **Gravity Forms**, **Ninja Forms**

| Tool | Description |
|------|-------------|
| `wp_list_forms` | List all forms across plugins |
| `wp_get_form` | Get form structure and fields |
| `wp_create_form` | Create new form with fields |
| `wp_update_form` | Update form configuration |
| `wp_delete_form` | Delete form |
| `wp_get_form_submissions` | Get form entries/submissions |
| `wp_submit_form` | Programmatic form submission |
| `wp_get_form_plugin` | Detect active form plugins |

### Elementor Extension (9 tools)

Supports: **Elementor** and **Elementor Pro**

| Tool | Description |
|------|-------------|
| `wp_get_elementor` | Get page Elementor JSON data |
| `wp_set_elementor` | Set/replace Elementor data |
| `wp_list_elementor_templates` | List saved templates (sections, pages, popups) |
| `wp_apply_elementor_template` | Apply template to page (replace/append/prepend) |
| `wp_create_landing_page` | Create complete landing page with hero, features, CTA |
| `wp_add_elementor_section` | Add section to existing page |
| `wp_update_elementor_widget` | Update specific widget settings |
| `wp_get_elementor_globals` | Get global colors, fonts, settings |
| `wp_clone_elementor_page` | Clone page with all Elementor data |

## Multi-Site Configuration

Create `~/.wp-ai-operator/config.json`:

```json
{
  "sites": {
    "production": {
      "url": "https://example.com",
      "apiKey": "spai_prod_key_here",
      "name": "Production"
    },
    "staging": {
      "url": "https://staging.example.com",
      "apiKey": "spai_staging_key_here",
      "name": "Staging"
    },
    "blog": {
      "url": "https://blog.example.com",
      "apiKey": "spai_blog_key_here",
      "name": "Blog"
    }
  },
  "defaultSite": "production",
  "enabledExtensions": ["core", "seo", "forms", "elementor"]
}
```

Then specify site in your prompts:

```
You: "On the staging site, create a test landing page"
You: "List all forms on the blog site"
You: "Publish this post to production and staging"
```

## Python Client

For scripting and automation:

```python
from wp_mcp_client import WPAIOperatorClient

client = WPAIOperatorClient()

# List posts
posts = client.list_posts(per_page=20, status="publish")

# Create landing page
page = client.create_landing_page(
    title="Summer Sale",
    headline="50% Off Everything",
    cta_text="Shop Now",
    cta_url="/shop",
    features=[
        {"title": "Free Shipping", "icon": "fas fa-truck"},
        {"title": "Easy Returns", "icon": "fas fa-undo"},
    ]
)

# Multi-site publish
results = client.publish_to_all(
    {"title": "New Post", "content": "...", "status": "publish"},
    sites=["production", "blog"]
)

# Health check all sites
status = client.health_check()
```

### CLI Usage

```bash
# Check all sites
python wp_mcp_client.py health

# List configured sites
python wp_mcp_client.py sites

# List posts on specific site
python wp_mcp_client.py posts staging

# List Elementor templates
python wp_mcp_client.py templates production
```

## Example Workflows

### Create a Landing Page

```
You: Create a landing page with:
- Title: "Summer Adventure Tours"
- Headline: "Ride Beyond Limits"
- Features: Expert Guides, Mountain Trails, All Skill Levels
- CTA: "Book Now" → /contact
- Colors: dark blue primary, coral accent
```

### Bulk SEO Optimization

```
You: Analyze SEO for all blog posts and fix issues:
- Add focus keywords based on titles
- Generate meta descriptions from content
- Ensure titles are 50-60 characters
```

### Form Creation

```
You: Create a contact form with:
- Name (required)
- Email (required)
- Phone (optional)
- Message (required)
- Send submissions to support@example.com
```

### Cross-Site Content Sync

```
You: Take the landing page from production (ID 45) and
clone it to staging with title "Test Landing Page"
```

## API Reference

Base URL: `https://yoursite.com/wp-json/site-pilot-ai/v1/`

### Authentication

```bash
# Header (recommended)
curl -H "X-API-Key: spai_xxx" https://site.com/wp-json/site-pilot-ai/v1/site-info

# Query parameter
curl "https://site.com/wp-json/site-pilot-ai/v1/site-info?api_key=spai_xxx"
```

### Core Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/site-info` | Site information and capabilities |
| GET | `/analytics` | API activity logs |
| GET | `/plugins` | Detect installed plugins |
| GET/POST | `/posts` | List/create posts |
| GET/PUT/DELETE | `/posts/{id}` | Single post operations |
| GET/POST | `/pages` | List/create pages |
| GET/PUT | `/pages/{id}` | Single page operations |
| POST | `/media` | Upload media (multipart) |
| POST | `/media/from-url` | Upload from URL |
| GET | `/drafts` | List drafts |
| DELETE | `/drafts/delete-all` | Bulk delete drafts |
| POST | `/mcp` | Native MCP endpoint (JSON-RPC 2.0) |

### SEO Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/POST | `/seo/{id}` | Get/set SEO metadata |
| GET | `/seo/{id}/analyze` | Analyze SEO with score |
| POST | `/seo/bulk` | Bulk SEO update |
| GET | `/seo/plugin` | Detect SEO plugin |

### Forms Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/POST | `/forms` | List/create forms |
| GET/PUT/DELETE | `/forms/{id}` | Single form operations |
| GET | `/forms/{id}/submissions` | Get form entries |
| POST | `/forms/{id}/submit` | Submit form data |
| GET | `/forms/plugins` | Detect form plugins |

### Elementor Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/POST | `/elementor/{id}` | Get/set page data |
| GET | `/elementor/templates` | List templates |
| POST | `/elementor/{id}/apply-template` | Apply template |
| POST | `/elementor/landing-page` | Create landing page |
| POST | `/elementor/{id}/sections` | Add section |
| PUT | `/elementor/{id}/widgets/{widget_id}` | Update widget |
| GET | `/elementor/globals` | Global settings |
| POST | `/elementor/{id}/clone` | Clone page |

## Project Structure

```
wp-ai-operator/
├── mcp-server/                    # MCP Server (Node.js/TypeScript)
│   ├── src/
│   │   ├── index.ts              # Main entry point
│   │   ├── kernel/               # Microkernel core
│   │   │   └── index.ts
│   │   ├── extensions/           # Pluggable extensions
│   │   │   ├── base.ts
│   │   │   ├── core.ts           # Posts, pages, media
│   │   │   ├── seo.ts            # SEO integration
│   │   │   ├── forms.ts          # Forms integration
│   │   │   ├── elementor.ts      # Page builder
│   │   │   └── index.ts
│   │   └── types/
│   │       └── index.ts          # Type definitions
│   ├── package.json
│   └── tsconfig.json
│
├── site-pilot-ai/                 # WordPress Plugin (PHP)
│   ├── site-pilot-ai.php         # Bootstrap + version
│   ├── includes/
│   │   ├── api/                  # REST endpoints + native MCP
│   │   ├── core/                 # Posts, pages, media, Elementor
│   │   ├── mcp/                  # Tool registries + Integration SDK
│   │   ├── pro/                  # Pro modules (premium package)
│   │   └── admin/                # Admin UI
│   └── readme.txt
│
├── docs/                          # API docs, widget reference, OpenAPI spec
└── README.md

# OpenClaw Skill (installed separately)
~/.agents/skills/wp-ai-operator/
├── SKILL.md                       # Main documentation
├── skill.json                     # Metadata & triggers
├── wp_mcp_client.py              # Python client
├── reference/                     # Setup guides
│   ├── mcp-setup.md
│   ├── wordpress-endpoints.md
│   └── authentication.md
└── examples/
    ├── sample_config.json
    └── create_landing_page.py
```

## Development

### Build MCP Server

```bash
cd mcp-server
npm install
npm run build      # Production build
npm run dev        # Development with hot reload
```

### Test API Connection

```bash
curl -s "https://yoursite.com/wp-json/site-pilot-ai/v1/site-info" \
  -H "X-API-Key: spai_your_key" | jq
```

### Add New Extension

1. Create `mcp-server/src/extensions/myextension.ts`
2. Extend `BaseExtension` class
3. Implement `getTools()` and handlers
4. Register in `extensions/index.ts`
5. Add WordPress endpoints to `site-pilot-ai/site-pilot-ai.php`

## Security

- API keys stored in WordPress `wp_options` table (hashed)
- All inputs sanitized via WordPress functions
- Activity logging for audit trail
- HTTPS required for production
- SSRF protection on media uploads and webhooks
- Rate limiting configurable per site
- Consider IP whitelisting for sensitive sites

## Troubleshooting

### "No WordPress sites configured"

Set environment variables or create `~/.wp-ai-operator/config.json`.

### "401 Unauthorized"

1. Check API key in WordPress Admin → Site Pilot AI
2. Verify key matches config exactly (no extra spaces)
3. Regenerate key if needed

### "Elementor data not saving"

1. Ensure Elementor is active
2. Clear Elementor cache after updates
3. Check page template is set to Elementor Full Width

### "Forms not detected"

The plugin auto-detects CF7, WPForms, Gravity Forms, and Ninja Forms. Ensure at least one is installed and active.

## Roadmap

- [x] Native MCP endpoint (v1.0.15)
- [x] Integration Registry — third-party plugin SDK (v1.0.58)
- [x] Capability-aware tool filtering (v1.0.60)
- [ ] WooCommerce extension (products, orders, customers)
- [ ] ACF (Advanced Custom Fields) support
- [ ] Gutenberg blocks manipulation
- [ ] Scheduled post publishing
- [ ] Media library management
- [ ] User management tools
- [ ] R2 media staging for batch uploads

## License

GPL v2 or later

## Credits

<p align="center">
  <strong>Built by <a href="https://digid.ca">DigID Inc</a></strong><br>
  Part of the Mumega Ecosystem
</p>

---

<p align="center">
  <a href="https://github.com/Digidinc/wp-ai-operator/issues">Report Bug</a> •
  <a href="https://github.com/Digidinc/wp-ai-operator/issues">Request Feature</a>
</p>
