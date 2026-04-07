# mumcp

<p align="center">
  <strong>239 MCP tools for WordPress. Every AI model. Free.</strong>
</p>

<p align="center">
  <a href="#install">Install</a> •
  <a href="#connect">Connect</a> •
  <a href="#tools">239 Tools</a> •
  <a href="#examples">Examples</a> •
  <a href="https://mucp.mumega.com">Website</a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/tools-239-blue" alt="Tools">
  <img src="https://img.shields.io/badge/version-2.4.1-green" alt="Version">
  <img src="https://img.shields.io/badge/MCP-compatible-brightgreen" alt="MCP">
  <img src="https://img.shields.io/badge/WordPress-5.0%2B-blue" alt="WordPress">
  <img src="https://img.shields.io/badge/Elementor-4.x-purple" alt="Elementor">
  <img src="https://img.shields.io/badge/license-GPL--2.0-orange" alt="License">
  <img src="https://img.shields.io/badge/price-free-brightgreen" alt="Free">
</p>

---

mumcp turns any WordPress site into an MCP server. AI assistants (Claude, Gemini, GPT, Cursor, Windsurf) manage your entire site through natural language — pages, Elementor layouts, WooCommerce products, media, SEO, menus, and more.

```
You: "Build a landing page with a hero, 3 feature cards, and a CTA"
AI:  wp_build_page → creates full Elementor page with styled sections, flex grid, shadows, hover effects
```

## Install

```bash
wp plugin install https://mumega.com/mcp-updates/mumega-mcp-latest.zip --activate
```

Or download from [mucp.mumega.com](https://mucp.mumega.com) and install via WP Admin > Plugins > Add New > Upload.

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
claude plugin add Mumega-com/mumcp-claude-plugin
```
Gives you `/mumcp:connect`, `/mumcp:tools`, `/mumcp:elementor`, `/mumcp:design` skills.

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

## Examples

### Build a page
```
wp_build_page(title: "Services", sections: [
  {type: "hero", heading: "Our Services", button_text: "Get Started"},
  {type: "features", columns: 3, items: [
    {icon: "fas fa-rocket", title: "Fast", desc: "Speed matters", button_text: "Learn More"},
    {icon: "fas fa-shield-alt", title: "Secure", desc: "Bank-grade", button_text: "Learn More"},
    {icon: "fas fa-heart", title: "Reliable", desc: "99.9% uptime", button_text: "Learn More"}
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

## Role-Scoped API Keys

Control which tools each AI model can access:

| Role | Tools | Best for |
|------|-------|----------|
| admin | 239 | Full access |
| designer | ~82 | Page building (Elementor + media + site) |
| editor | ~99 | Content + design + SEO |
| author | ~40 | Content writing |
| custom | pick | Specific use cases |

Create keys via WP Admin or `wp_create_api_key(label, role)`.

## Elementor Features

- **14 blueprint types** — hero, features, cta, pricing, faq, testimonials, and more
- **Validation** — auto-fixes missing IDs, wrong widget keys, nesting errors
- **Fuzzy matching** — typo in widget type? "Did you mean 'heading'?"
- **Save persistence** — verifies data actually persists after Elementor's document save
- **CSS regeneration** — auto-rebuilds CSS after changes
- **Container + classic mode** — works with both Elementor layout modes

## Links

- **Website:** [mucp.mumega.com](https://mucp.mumega.com)
- **Claude Code Plugin:** [Mumega-com/mumcp-claude-plugin](https://github.com/Mumega-com/mumcp-claude-plugin)
- **WordPress.org:** pending approval (slug: mumega-mcp)
- **Download:** [mumega-mcp-latest.zip](https://mumega.com/mcp-updates/mumega-mcp-latest.zip)

## License

GPL v2 or later. All 239 tools are free. No paywalls, no locked features.

---

Built by [Mumega](https://mumega.com)
