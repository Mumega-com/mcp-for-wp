<p align="center">
  <img src="https://raw.githubusercontent.com/Mumega-com/mcp-for-wp/main/site-pilot-ai/assets/banner-1544x500.png" alt="mumcp — 239 MCP tools for WordPress" width="100%">
</p>

<h1 align="center">mumcp</h1>

<p align="center">
  <strong>239 MCP tools for WordPress. Every AI model. Free.</strong>
</p>

<p align="center">
  <a href="#install">Install</a> •
  <a href="#how-it-works">How It Works</a> •
  <a href="#tools">239 Tools</a> •
  <a href="#examples">Examples</a> •
  <a href="#blueprints">24 Blueprints</a> •
  <a href="https://mucp.mumega.com">Website</a>
</p>

<p align="center">
  <a href="https://github.com/Mumega-com/mcp-for-wp/stargazers"><img src="https://img.shields.io/github/stars/Mumega-com/mcp-for-wp?style=flat-square" alt="Stars"></a>
  <a href="https://github.com/Mumega-com/mcp-for-wp/releases"><img src="https://img.shields.io/github/v/release/Mumega-com/mcp-for-wp?style=flat-square" alt="Release"></a>
  <img src="https://img.shields.io/badge/tools-239-blue?style=flat-square" alt="Tools">
  <img src="https://img.shields.io/badge/blueprints-24-blue?style=flat-square" alt="Blueprints">
  <img src="https://img.shields.io/badge/MCP-compatible-brightgreen?style=flat-square" alt="MCP">
  <img src="https://img.shields.io/badge/WordPress-5.0%2B-21759b?style=flat-square" alt="WordPress">
  <img src="https://img.shields.io/badge/Elementor-4.x-92003B?style=flat-square" alt="Elementor">
  <img src="https://img.shields.io/badge/license-GPL--2.0-orange?style=flat-square" alt="License">
  <img src="https://img.shields.io/badge/price-free-brightgreen?style=flat-square" alt="Free">
</p>

---

mumcp turns any WordPress site into an MCP server. AI assistants (Claude, Gemini, GPT, Cursor, Windsurf) manage your entire site through natural language — pages, Elementor layouts, WooCommerce products, media, SEO, menus, and more.

```
You: "Build a landing page with a hero, 3 feature cards, and a CTA"
AI:  wp_build_page → full Elementor page with styled sections, flex grid, shadows, hover effects
```

<!-- TODO: Replace with actual demo GIF
<p align="center">
  <img src="https://raw.githubusercontent.com/Mumega-com/mcp-for-wp/main/docs/demo.gif" alt="mumcp demo" width="800">
</p>
-->

## How It Works

```mermaid
graph LR
    A[🤖 AI Assistant] -->|MCP JSON-RPC| B[mumcp Plugin]
    B -->|REST API| C[WordPress]
    B -->|Document API| D[Elementor]
    B -->|WC API| E[WooCommerce]
    
    subgraph "Your WordPress Site"
        C --- F[Pages & Posts]
        C --- G[Media Library]
        C --- H[Menus & Settings]
        D --- I[Layouts & Widgets]
        D --- J[Templates & Parts]
        E --- K[Products & Orders]
    end
    
    subgraph "AI Clients"
        L[Claude Code] --> A
        M[Claude Desktop] --> A
        N[Cursor] --> A
        O[Windsurf] --> A
        P[Gemini] --> A
    end
```

## Why mumcp?

| | mumcp | WordPress MCP Adapter | Royal MCP | InstaWP mcp-wp |
|---|---|---|---|---|
| **MCP Tools** | **239** | ~20 | 37 | ~30 |
| **Blueprints** | **24 types** | 0 | 0 | 0 |
| **Elementor** | Full (build + edit + templates + theme) | No | No | No |
| **WooCommerce** | 21 tools | No | No | No |
| **LearnPress** | 18 tools | No | No | No |
| **Role-scoped keys** | 5 roles | No | No | No |
| **Validation** | Auto-fix IDs, keys, nesting | No | No | No |
| **Install** | WordPress plugin | Requires Abilities API | WordPress plugin | External Node.js |
| **Price** | **Free** | Free | Free | Free |

## Install

```bash
wp plugin install https://mumega.com/mcp-updates/mumega-mcp-latest.zip --activate
```

Or download from [mucp.mumega.com](https://mucp.mumega.com) and upload via WP Admin > Plugins > Add New.

## Connect

### Claude Code / Claude Desktop
```json
{
  "mcpServers": {
    "mumcp": {
      "url": "https://your-site.com/wp-json/site-pilot-ai/v1/mcp",
      "headers": { "X-API-Key": "spai_your_key_here" }
    }
  }
}
```

### Cursor / Windsurf
Same URL and key — add in your MCP server settings.

### Claude Code Plugin
```bash
claude plugin marketplace add https://github.com/Mumega-com/mumcp-claude-plugin.git
claude plugin install mumcp@mumcp
```
Adds `/mumcp:setup`, `/mumcp:tools`, `/mumcp:elementor`, `/mumcp:design` skills + `wp-builder` agent.

## Tools

239 tools across 15 categories:

| Category | Tools | What |
|----------|-------|------|
| **content** | 28 | Pages, posts, drafts, bulk ops, search |
| **elementor** | 12 | Get/set data, edit sections, edit widgets |
| **elementor-build** | 8 | Build pages from blueprints, landing pages |
| **elementor-templates** | 15 | Templates, archetypes, reusable parts |
| **elementor-theme** | 10 | Theme builder, conditions, custom code |
| **elementor-info** | 5 | Widget schemas, help, CSS regen |
| **site** | 37 | Menus, options, CSS, design refs, guides |
| **media** | 7 | Upload file/URL/base64, screenshot |
| **woocommerce** | 21 | Products, orders, categories, analytics |
| **learnpress** | 18 | Courses, lessons, quizzes, curriculum |
| **seo** | 10 | Meta tags, analysis, bulk SEO, indexing |
| **taxonomy** | 5 | Categories, tags, custom terms |
| **gutenberg** | 4 | Blocks, patterns, block types |
| **admin** | 16 | API keys, rate limits, settings, updates |
| **webhooks** | 7 | Create, test, monitor deliveries |

## Role-Scoped API Keys

```mermaid
graph TD
    A[API Key] -->|role| B{Role}
    B -->|admin| C["🔓 239 tools — full access"]
    B -->|designer| D["🎨 82 tools — Elementor + media + site"]
    B -->|editor| E["✏️ 99 tools — content + design + SEO"]
    B -->|author| F["📝 40 tools — content + media"]
    B -->|custom| G["⚙️ pick categories"]
```

Create keys via WP Admin > mumcp > Setup, or `wp_create_api_key(label, role)`.

## Blueprints

Build full pages with one call. 24 section types:

| Type | What it builds |
|------|---------------|
| `hero` | Full-width hero with heading, CTA, background |
| `features` | Icon-box card grid with shadows, hover effects |
| `cta` | Call-to-action banner with button |
| `pricing` | Price table columns with feature lists |
| `faq` | Accordion with Q&A |
| `testimonials` | Quote cards with ratings |
| `team` | Team member cards with images |
| `portfolio` | Project showcase grid |
| `blog_grid` | Blog post cards |
| `services` | Service cards with pricing |
| `about` | Image + text side-by-side |
| `process_steps` | Numbered step cards |
| `social_proof` | Star ratings + quotes |
| `product_showcase` | Product highlight with features |
| `before_after` | Comparison columns |
| `newsletter` | Email signup CTA |
| `stats` | Animated number counters |
| `gallery` | Image gallery grid |
| `text` | Simple text section |
| `map` | Google Maps embed |
| `countdown` | Countdown timer |
| `logo_grid` | Partner/client logos |
| `video` | Video embed |
| `contact_form` | Contact form section |

## Examples

### Build a page
```
wp_build_page(title: "Services", sections: [
  {type: "hero", heading: "Our Services", button_text: "Get Started"},
  {type: "features", columns: 3, items: [
    {icon: "fas fa-rocket", title: "Fast", desc: "Speed matters"},
    {icon: "fas fa-shield-alt", title: "Secure", desc: "Bank-grade"},
    {icon: "fas fa-heart", title: "Reliable", desc: "99.9% uptime"}
  ]},
  {type: "cta", heading: "Ready?", button_text: "Contact Us"}
])
```

### Edit one widget
```
wp_edit_widget(page_id: 42, widget_id: "abc123", settings: {title_text: "New Title"})
```

### Upload an image
```
wp_upload_media_from_url(url: "https://example.com/photo.jpg", title: "Hero image")
```

### Manage WooCommerce
```
wc_create_product(name: "T-Shirt", regular_price: "29.99", type: "simple")
```

## Elementor Features

- **24 blueprint types** — hero, features, cta, pricing, team, portfolio, services, about, and more
- **Validation** — auto-fixes missing IDs, wrong widget keys, nesting errors
- **Fuzzy matching** — typo in widget type? "Did you mean 'heading'?"
- **Save persistence** — forces direct meta overwrite after Document::save()
- **CSS regeneration** — auto-rebuilds CSS, purges SiteGround/WP Rocket/LiteSpeed
- **Container + classic mode** — works with both Elementor layout modes

## Roadmap

- [x] 239 MCP tools across 15 categories
- [x] 24 page blueprints
- [x] Role-scoped API keys (5 roles)
- [x] Elementor validation + auto-fix
- [x] Admin UI (Setup, Library, Tools, Settings)
- [x] Claude Code plugin with 6 skills
- [ ] WordPress.org listing (submitted, pending)
- [ ] Managed MCP proxy for agencies
- [ ] 30+ blueprint types
- [ ] Visual diff — show what changed after MCP edits
- [ ] Multi-site dashboard
- [ ] WooCommerce product page blueprints

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for setup instructions and what we need help with.

## Security

See [SECURITY.md](SECURITY.md) for our vulnerability disclosure policy.

## Links

- **Website:** [mucp.mumega.com](https://mucp.mumega.com)
- **Claude Code Plugin:** [Mumega-com/mumcp-claude-plugin](https://github.com/Mumega-com/mumcp-claude-plugin)
- **MCP Proxy:** [Mumega-com/mumcp-proxy](https://github.com/Mumega-com/mumcp-proxy)
- **WordPress.org:** pending approval (slug: mumega-mcp)
- **Download:** [mumega-mcp-latest.zip](https://mumega.com/mcp-updates/mumega-mcp-latest.zip)

## License

GPL v2 or later. All 239 tools are free. No paywalls, no locked features.

---

<p align="center">
  Built by <a href="https://mumega.com">Mumega</a>
</p>
