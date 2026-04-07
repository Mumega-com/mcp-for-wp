# Site Pilot AI

<p align="center">
  <strong>Reusable AI production system for WordPress operators</strong><br>
  Native MCP server, reusable site structure, and draft-first publishing workflows
</p>

<p align="center">
  <a href="#quick-start">Quick Start</a> •
  <a href="#features">Features</a> •
  <a href="#tools">90+ Tools</a> •
  <a href="#operator-workflow">Operator Workflow</a> •
  <a href="#ai-integrations">AI Integrations</a> •
  <a href="#api-reference">API Reference</a> •
  <a href="docs/PRODUCT_ROADMAP.md">Roadmap</a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/tools-90%2B-blue" alt="Tools">
  <img src="https://img.shields.io/badge/plugin-v2.2.0-green" alt="Plugin">
  <img src="https://img.shields.io/badge/MCP-compatible-green" alt="MCP">
  <img src="https://img.shields.io/badge/WordPress-5.0%2B-blue" alt="WordPress">
  <img src="https://img.shields.io/badge/license-GPL--2.0-orange" alt="License">
</p>

---

```
You: "Use our SaaS landing-page archetype, pull in the pricing proof section,
save any strong new sections as reusable parts, and build a draft from this mockup"

Claude: *Builds the draft in Elementor, links it to the design reference,
        creates reusable parts, and keeps the page in draft for review*
        "Done. Draft created with linked assets and reusable sections."
```

## Why This Exists

Most WordPress AI tools generate isolated pages. Site Pilot AI is built for operators who ship repeatedly and need AI to remember how the site should be built.

- Keep a site character that models inherit automatically
- Reuse Elementor parts and full-page archetypes instead of rebuilding layouts
- Turn screenshots, mockups, and Figma work into reusable site assets
- Build drafts first, review them, then publish with traceable provenance

## Quick Start

### 1. Install the Plugin

Install **Site Pilot AI** from WordPress Plugins → Add New, or upload manually.

### 2. Get Your API Key

Go to **WP Admin → Site Pilot AI** and copy your API key.

### 3. Connect Your AI

**Native MCP endpoint** (recommended — no external server needed):

Add to your Claude Desktop `claude_desktop_config.json` or Claude Code `.mcp.json`:

```json
{
  "mcpServers": {
    "wordpress": {
      "url": "https://yoursite.com/wp-json/site-pilot-ai/v1/mcp",
      "headers": {
        "X-API-Key": "spai_your_key_here"
      }
    }
  }
}
```

**npm MCP server** (for multi-site or local development):

```bash
npm install -g wp-ai-operator
wp-ai-operator --setup
```

## Features

| Category | Capabilities |
|----------|-------------|
| **Operator System** | Guided site character, onboarding, reusable workflow, public `llms.txt` |
| **Content** | Posts, Pages, Media uploads, Drafts, Bulk operations |
| **Page Builders** | Elementor: layouts, widgets, templates, landing pages, reusable parts, archetypes |
| **Design Intake** | Image-based design references, Figma intake, design provenance, draft generation |
| **SEO** | Yoast, RankMath, AIOSEO, SEOPress — get/set/analyze/bulk |
| **Commerce** | WooCommerce products, orders, analytics, product archetypes |
| **Forms** | Contact Form 7, WPForms, Gravity Forms, Ninja Forms |
| **Gutenberg** | Block content read/write, block types, block patterns |
| **Site Management** | Theme info, options, health checks, taxonomies, permalinks |
| **Webhooks** | Create, manage, test, and monitor webhook subscriptions |
| **Security** | API key management, rate limiting, activity logging |
| **AI Integrations** | Stock photos (Pexels), image generation (OpenAI/Gemini), alt text, TTS |

## Operator Workflow

1. Define the site character once.
2. Save approved screenshots, mockups, or Figma work as design references.
3. Promote proven layouts into archetypes and reusable Elementor parts.
4. Ask your AI to build drafts from those approved assets.
5. Keep strong new sections by saving them back into the library.

## Tools

### Content Management (~20 tools)
Posts and pages CRUD, bulk create (up to 50 per batch), drafts management, featured images, search, post meta, media uploads from file or URL.

### Elementor (~15 tools)
Get/set page data, templates, landing page builder, section management, widget updates, global colors/typography, custom code, widget inspector with full controls schema, reusable parts, and archetype-driven page assembly.

### SEO (~5 tools)
Get/set SEO metadata, content analysis with scoring, bulk SEO updates, plugin detection. Supports Yoast, RankMath, AIOSEO, SEOPress.

### Forms (~8 tools)
List/create/update/delete forms, get submissions, programmatic submission, plugin detection. Supports CF7, WPForms, Gravity Forms, Ninja Forms.

### Gutenberg (~4 tools)
Read/write block content, list registered block types, list block patterns.

### Taxonomies (~3 tools)
Create, update, delete terms across any taxonomy (categories, tags, custom).

### Webhooks (~7 tools)
Full webhook lifecycle: list events, create/update/delete subscriptions, test delivery, view logs.

### Site Utilities (~10 tools)
Site info, theme info, plugin detection, analytics, health checks, option management, permalink flushing, feedback.

### Security & Admin (~6 tools)
API key management (list/create/revoke), rate limit status/config/reset.

### AI Integrations (~8 tools)
Stock photo search/download (Pexels), image generation, featured image generation, alt text generation, image description, excerpt generation, text-to-speech, and Figma/image-based design intake.

## AI Integrations

Site Pilot AI v1.1.0+ includes built-in AI provider integrations configured via **WP Admin → Site Pilot AI → Integrations**:

| Provider | Tools | Tier |
|----------|-------|------|
| **Pexels** | Stock photo search & download | Free |
| **OpenAI** | Image generation, alt text, excerpts | Pro |
| **Gemini** | Image generation (fallback), descriptions | Pro |
| **ElevenLabs** | Text-to-speech | Pro |

Tools auto-select the best configured provider. API keys are stored encrypted in WordPress.

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│              Claude Code / Desktop / ChatGPT / Cursor           │
└─────────────────────────────────────────────────────────────────┘
                              │ MCP (JSON-RPC 2.0)
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
│        site-pilot-ai.php • 90+ MCP Tools • 40+ REST Routes     │
│  ┌────────┐ ┌─────┐ ┌───────┐ ┌──────────┐ ┌──────────────┐   │
│  │  Core  │ │ SEO │ │ Forms │ │ Elementor│ │AI Integration│   │
│  └────────┘ └─────┘ └───────┘ └──────────┘ └──────────────┘   │
│                     Rate Limiting • Activity Logging             │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  WordPress │ Elementor │ Yoast/RankMath │ CF7/WPForms │ Pexels │
└─────────────────────────────────────────────────────────────────┘
```

## Installation

### WordPress Plugin

Upload `site-pilot-ai/` to your plugins folder or install from **Plugins → Add New**:

```bash
wp plugin install /path/to/site-pilot-ai.zip --activate
```

Activate in **WordPress Admin → Plugins**, then get your API key from **Site Pilot AI**.

### npm MCP Server (Optional)

```bash
npm install -g wp-ai-operator
wp-ai-operator --setup    # Interactive setup
wp-ai-operator --test     # Test connection
```

### Multi-Site Configuration

Create `~/.wp-ai-operator/config.json`:

```json
{
  "sites": {
    "production": {
      "url": "https://example.com",
      "apiKey": "spai_prod_key",
      "name": "Production"
    },
    "staging": {
      "url": "https://staging.example.com",
      "apiKey": "spai_staging_key",
      "name": "Staging"
    }
  },
  "defaultSite": "production"
}
```

## API Reference

Base URL: `https://yoursite.com/wp-json/site-pilot-ai/v1/`

### Authentication

```bash
curl -H "X-API-Key: spai_xxx" https://site.com/wp-json/site-pilot-ai/v1/site-info
```

### Core Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/site-info` | Site information and capabilities |
| GET/POST | `/posts` | List/create posts |
| POST | `/posts/bulk` | Bulk create posts (up to 50) |
| GET/PUT/DELETE | `/posts/{id}` | Single post operations |
| GET/POST | `/pages` | List/create pages |
| POST | `/pages/bulk` | Bulk create pages (up to 50) |
| GET/PUT/DELETE | `/pages/{id}` | Single page operations |
| POST | `/media` | Upload media |
| POST | `/media/from-url` | Upload from URL |
| POST | `/mcp` | Native MCP endpoint (JSON-RPC 2.0) |

### Elementor Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/POST | `/elementor/{id}` | Get/set page data |
| GET | `/elementor/widgets` | List widgets or get widget controls |
| GET | `/elementor/templates` | List templates |
| POST | `/elementor/{id}/apply-template` | Apply template |
| POST | `/elementor/landing-page` | Create landing page |
| GET/POST | `/elementor/globals` | Global colors/typography |

### SEO Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/POST | `/seo/{id}` | Get/set SEO metadata |
| GET | `/seo/{id}/analyze` | Analyze SEO with score |
| POST | `/seo/bulk` | Bulk SEO update |

## Development

### Quality Checks

```bash
cd site-pilot-ai
composer install
composer test
composer lint:tests
```

### Project Structure

```
wp-ai-operator/
├── mcp-server/                    # npm MCP Server (Node.js/TypeScript)
├── site-pilot-ai/                 # WordPress Plugin (PHP)
│   ├── site-pilot-ai.php         # Bootstrap + version
│   ├── includes/
│   │   ├── api/                  # REST endpoints + native MCP
│   │   ├── core/                 # Posts, pages, media, Elementor
│   │   ├── mcp/                  # Tool registries + Integration SDK
│   │   ├── pro/                  # Pro modules
│   │   └── admin/                # Admin UI + Integrations page
│   └── readme.txt
├── docs/                          # API docs, widget reference
└── README.md
```

## Security

- API keys hashed using WordPress password hashing
- Dedicated service account with limited capabilities
- Activity logging for audit trail
- Rate limiting (configurable per key)
- SSRF protection on media uploads and webhooks
- Input validation with "did you mean?" suggestions

## License

GPL v2 or later

---

<p align="center">
  <strong>Built by <a href="https://digid.ca">DigID Inc</a></strong><br>
  <a href="https://github.com/Digidinc/wp-ai-operator/issues">Report Bug</a> •
  <a href="https://github.com/Digidinc/wp-ai-operator/issues">Request Feature</a>
</p>
