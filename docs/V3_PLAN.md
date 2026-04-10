# mumcp v3.0 — White-Label AI for WordPress Agencies

## Vision

Agencies install mumcp on client sites, connect their branded AI, charge clients for "AI site management." We power the backend. The plugin stays free. Revenue comes from agency subscriptions.

## The Product Stack

```
Agency Dashboard (mumega.com)
    ↓ manages
Client WordPress Sites (each has mumcp installed)
    ↓ powered by
AI Chat (OpenAI/Gemini via agency's key, or our CF Workers AI)
    ↓ executes
239 MCP Tools → WordPress/Elementor/WooCommerce
```

## V3 Milestones

### Phase 1: Chat Excellence (v2.9)
**Goal:** Make the chat tab genuinely useful — not a demo, a daily tool.

- [ ] Multi-model support in chat: OpenAI GPT-4o, Claude, Gemini (user picks in Settings)
- [ ] Conversation history persistence (stored in custom post type, survives page reload)
- [ ] Confirmation before destructive actions ("Delete page 95? [Confirm/Cancel]")
- [ ] Streaming responses (SSE from Worker → real-time text in chat)
- [ ] Context window: auto-inject current page Elementor summary when user is on a page
- [ ] Tool execution results shown inline with formatted output
- [ ] Chat available on frontend for logged-in admins (floating widget)

### Phase 2: Agency Dashboard (v2.10)
**Goal:** One place for agencies to manage all client sites.

- [ ] Agency registration page on mucp.mumega.com
- [ ] Dashboard: list all connected sites, status, last activity, version
- [ ] Site health monitoring: is the plugin active, is the MCP endpoint responding
- [ ] Centralized API key management: create/revoke keys for all sites
- [ ] Usage analytics: tool calls per site per day, most used tools
- [ ] Deploy mumcp proxy worker for agencies (one endpoint, routes to all sites)

### Phase 3: White-Label (v3.0)
**Goal:** Agency's brand, our infrastructure.

- [ ] Custom branding: agency sets their name, logo, colors in dashboard
- [ ] White-label chat: "Powered by [Agency Name]" instead of "mumcp"
- [ ] Custom domain: agency's MCP proxy at `ai.agencyname.com`
- [ ] Client-facing chat widget: end clients talk to AI on their own site
- [ ] Embeddable chat for any page (shortcode + Elementor widget)
- [ ] Agency billing: Stripe integration, per-site pricing, auto-invoicing

### Phase 4: Intelligence (v3.1)
**Goal:** The AI gets smarter with every interaction.

- [ ] Conversation logging (opt-in): what users asked, which tools were called, what worked
- [ ] Site-specific fine-tuning: LoRA adapter trained on each site's patterns
- [ ] Blueprint generation from existing pages: "analyze this page, save as a blueprint"
- [ ] Design system extraction: AI reads the site, generates a style guide
- [ ] Proactive suggestions: "Your homepage hero hasn't changed in 30 days — want a refresh?"

## Architecture

### Current (v2.8)
```
User → Chat Tab → WP AJAX → CF Worker (Llama) or OpenAI → tool call → REST dispatch → WordPress
User → Claude/Cursor → MCP endpoint → REST dispatch → WordPress
```

### V3 Target
```
Agency Dashboard (Next.js on Vercel/CF Pages)
    ↓ manages sites via
mumcp Proxy (CF Worker)
    ↓ routes to
Client Sites (each with mumcp plugin)
    ↓ chat powered by
Agency's AI key (OpenAI/Claude) or shared CF Workers AI
    ↓ white-label chat widget
Client sees "Agency AI" — never sees "mumcp"
```

### Data Flow
```
Agency creates account → gets proxy subdomain → adds client sites
    ↓
Client site installs mumcp → generates API key → registers with proxy
    ↓
Agency dashboard shows site status → can manage all sites from one place
    ↓
End client opens chat widget → "Hi, I'm [Agency] AI" → manages their site
    ↓
Conversation logs → anonymized → train better models → everyone benefits
```

## Revenue Model

| Tier | Price | Includes |
|------|-------|----------|
| **Free** | $0 | Plugin + 239 tools + MCP + free Llama chat |
| **Agency Starter** | $49/mo | 5 sites, dashboard, white-label chat, proxy |
| **Agency Pro** | $149/mo | 25 sites, custom domain, priority support |
| **Agency Scale** | $399/mo | Unlimited sites, API access, dedicated proxy |

**Revenue at scale:**
- 100 agencies on Starter: $4,900/mo
- 50 agencies on Pro: $7,450/mo
- 20 agencies on Scale: $7,980/mo
- **Total: $20,330/mo ($243,960/yr ARR)**

## Technical Requirements

### Chat Tab (v2.9)
- Add Anthropic Claude API support alongside OpenAI
- Add Google Gemini API support
- Model picker in Settings → Integrations
- Store conversations in `spai_chat_history` custom post type
- Add confirmation dialog for destructive tools (delete, replace, update)
- SSE streaming endpoint for real-time responses

### Agency Dashboard (v2.10)
- Next.js app on Cloudflare Pages or Vercel
- Auth: Clerk or custom JWT
- DB: Cloudflare D1 or Supabase
- Site registry: same KV as mumcp-proxy worker
- API: REST endpoints for site management
- WebSocket for real-time site status

### White-Label (v3.0)
- Plugin settings: `spai_white_label` option with name, logo_url, colors
- Chat widget renders from these settings
- Shortcode: `[mumcp_chat]`
- Elementor widget: drag-and-drop chat
- Custom domain via Cloudflare for SaaS (CF for Platforms)
- Stripe Billing integration with metered usage

### Intelligence (v3.1)
- Conversation logging to D1 database (opt-in, privacy-first)
- LoRA fine-tuning pipeline: export training data → train on HuggingFace → upload to CF Workers AI
- Blueprint extraction: read Elementor JSON → generate blueprint schema
- Site analyzer: crawl all pages, extract design patterns, generate style guide

## Timeline

| Phase | Version | Duration | Deliverable |
|-------|---------|----------|-------------|
| Chat Excellence | v2.9 | 2 weeks | Multi-model chat, history, streaming, confirmations |
| Agency Dashboard | v2.10 | 3 weeks | Dashboard app, site management, proxy deployment |
| White-Label | v3.0 | 3 weeks | Branding, custom domain, client widget, billing |
| Intelligence | v3.1 | 4 weeks | Conversation logging, LoRA pipeline, proactive AI |

## Success Metrics

- **v2.9**: Chat used daily on 10+ sites (not just demo)
- **v2.10**: 5 agencies signed up for dashboard beta
- **v3.0**: 1 agency running white-label for 10+ clients
- **v3.1**: Fine-tuned model outperforms base Llama on WordPress tasks by 40%

## What We Already Have

- [x] 239 MCP tools (most complete WordPress MCP server)
- [x] 24 page blueprints
- [x] Chat tab with OpenAI integration
- [x] Cloudflare Workers AI (free Llama fallback)
- [x] Role-scoped API keys (5 roles)
- [x] mumcp-proxy worker (built, needs deployment)
- [x] Claude Code plugin (6 skills + wp-builder agent)
- [x] Elementor v4 Atomic editor support
- [x] Admin UI (Setup, Chat, Library, Tools, Settings)
- [x] GitHub webhook for issue-driven development
- [x] Self-hosted + WP.org dual build pipeline

## What We Need to Build

- [ ] Claude/Gemini API support in chat handler
- [ ] Conversation persistence
- [ ] Streaming responses
- [ ] Destructive action confirmations
- [ ] Agency dashboard (Next.js)
- [ ] White-label settings + rendering
- [ ] Chat shortcode + Elementor widget
- [ ] Stripe billing integration
- [ ] Conversation logging pipeline
- [ ] LoRA fine-tuning pipeline
