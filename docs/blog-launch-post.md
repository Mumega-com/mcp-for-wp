# 239 Free MCP Tools for WordPress — Connect Claude, Gemini, GPT to Your Site in 2 Minutes

WordPress has 43% of the web. AI agents are the future of site management. But until now, connecting the two required custom code, fragile REST API wrappers, or expensive SaaS tools.

**mumcp** changes that. One plugin, 239 MCP tools, every AI model, completely free.

## What is it?

mumcp is a WordPress plugin that turns your site into an MCP (Model Context Protocol) server. Once installed, any MCP-compatible AI assistant — Claude, Gemini, GPT, Cursor, Windsurf, or your local Ollama — can manage your entire site through natural language.

```
You: "Build a landing page with a hero, 3 feature cards, and a CTA"
AI:  → creates a full Elementor page with styled sections, flex grid, shadows, hover effects
```

No code. No Elementor JSON. No WordPress admin.

## The numbers

- **239 MCP tools** across 15 categories
- **14 page blueprints** (hero, features, pricing, FAQ, testimonials, stats, and more)
- **Elementor 4 support** with validation that auto-fixes your AI's mistakes
- **Role-scoped API keys** — give your designer bot 82 tools, your content writer 40
- **All free** — no premium tier, no locked features, no trial periods

## How it compares

The closest competitor is Royal MCP with 37 tools. We have 239. Elementor's Angie is in beta with credit limits. We're production-ready with no limits.

| Feature | mumcp | Royal MCP | Elementor Angie |
|---------|-------|-----------|-----------------|
| MCP tools | 239 | 37 | Unknown |
| Elementor support | Full (build + edit + templates) | No | Yes (limited) |
| WooCommerce | 21 tools | No | No |
| Price | Free | Free | Free (beta credits) |
| Role-scoped keys | Yes (5 roles) | No | No |
| Page blueprints | 14 types | No | No |

## Install in 2 minutes

**Step 1: Install the plugin**
```bash
wp plugin install https://mumega.com/mcp-updates/mumega-mcp-latest.zip --activate
```

**Step 2: Generate an API key**
WP Admin → mumcp → Setup → Generate API Key

**Step 3: Connect your AI**
```json
{
  "mcpServers": {
    "mumcp": {
      "url": "https://your-site.com/wp-json/site-pilot-ai/v1/mcp",
      "headers": {"X-API-Key": "spai_your_key"}
    }
  }
}
```

That's it. Your AI can now manage your entire WordPress site.

## What can it do?

### Build pages from blueprints
```
wp_build_page(title: "Services", sections: [
  {type: "hero", heading: "Our Services", button_text: "Get Started"},
  {type: "features", columns: 3, items: [
    {icon: "fas fa-rocket", title: "Fast", desc: "Lightning speed"},
    {icon: "fas fa-shield-alt", title: "Secure", desc: "Bank-grade security"},
    {icon: "fas fa-heart", title: "Reliable", desc: "99.9% uptime"}
  ]},
  {type: "cta", heading: "Ready?", button_text: "Contact Us"}
])
```

### Edit one widget without touching the rest
```
wp_edit_widget(page_id: 42, widget_id: "abc123", settings: {title_text: "New Title"})
```

### Manage WooCommerce
```
wc_create_product(name: "T-Shirt", regular_price: "29.99", type: "simple")
```

### SEO, media, menus, taxonomies, courses, and more
All 239 tools documented. Your AI discovers them automatically via `wp_introspect()`.

## For Claude Code users

Install our Claude Code plugin for guided setup and WordPress knowledge:
```bash
claude plugin marketplace add https://github.com/Mumega-com/mumcp-claude-plugin.git
claude plugin install mumcp@mumcp
```

## Why we made it free

We're Mumega — a small AI agency building tools for the WordPress ecosystem. We believe the MCP layer should be free infrastructure, not a monetized gateway. Revenue comes from managed hosting, support, and custom development — not from locking tools behind paywalls.

239 tools. Every AI model. Your WordPress site. Free forever.

**Links:**
- Website: https://mucp.mumega.com
- GitHub (plugin): https://github.com/Mumega-com/mcp-for-wp
- GitHub (Claude Code plugin): https://github.com/Mumega-com/mumcp-claude-plugin
- Download: https://mumega.com/mcp-updates/mumega-mcp-latest.zip

---
*Built by Mumega — https://mumega.com*
