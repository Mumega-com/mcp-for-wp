# MCP Streamable HTTP Integration

This Cloudflare Worker now supports the **Model Context Protocol (MCP)** via Streamable HTTP, allowing Claude Desktop and other MCP clients to connect directly without any local installation.

## Architecture

```
MCP Client (Claude Desktop/ChatGPT) → POST /mcp → Cloudflare Worker → REST API → WordPress
```

## Endpoint

`POST https://your-worker.workers.dev/mcp`

Transport behaviors:

- Streamable HTTP JSON response (`application/json`)
- SSE-framed response (`text/event-stream`) when enabled or requested
- Notifications return `204` with empty body

The MCP endpoint implements JSON-RPC 2.0 protocol and exposes 36 WordPress management tools across 4 categories:

### Tool Categories

1. **Core (14 tools)**: Site info, posts, pages, media, drafts
2. **SEO (5 tools)**: SEO metadata, analysis, bulk updates
3. **Forms (8 tools)**: Form management, submissions, plugins
4. **Elementor (9 tools)**: Page builder, templates, landing pages

## Configuration

### 1. Set Site Config

The MCP endpoint uses the `default` site from `SITE_CONFIGS`, or the first configured site if no default exists.

```bash
# Set SITE_CONFIGS secret
wrangler secret put SITE_CONFIGS
```

When prompted, paste JSON:

```json
{
  "default": {
    "id": "default",
    "name": "Musical Unicorn Farm",
    "url": "https://musicalunicornfarm.com",
    "apiKey": "digid_xxx"
  }
}
```

### 1b. Set Transport Mode

Set the worker variable `MCP_TRANSPORT_MODE` in `wrangler.toml`:

- `auto` (default): JSON by default; switches to SSE when client sends `Accept: text/event-stream`
- `json`: always return JSON transport responses
- `sse`: always return SSE transport responses

Example:

```toml
[vars]
MCP_TRANSPORT_MODE = "auto"
```

### 2. Claude Desktop Integration

Add to your Claude Desktop config (`~/Library/Application Support/Claude/claude_desktop_config.json` on macOS):

```json
{
  "mcpServers": {
    "wordpress-unicorn": {
      "transport": {
        "type": "streamableHttp",
        "url": "https://wp-ai-gateway.your-subdomain.workers.dev/mcp"
      }
    }
  }
}
```

Restart Claude Desktop to load the server.

### 3. Verify Connection

In Claude Desktop, check the MCP icon. You should see "wordpress-unicorn" connected with 36 tools available.

## Available Tools

### Core Tools

| Tool | Description |
|------|-------------|
| `wp_site_info` | Get site information |
| `wp_analytics` | Get site analytics |
| `wp_detect_plugins` | Detect installed plugins |
| `wp_list_posts` | List blog posts |
| `wp_create_post` | Create new blog post |
| `wp_update_post` | Update existing post |
| `wp_delete_post` | Delete a post |
| `wp_list_pages` | List WordPress pages |
| `wp_create_page` | Create new page |
| `wp_update_page` | Update existing page |
| `wp_upload_media` | Upload media from base64 |
| `wp_upload_media_from_url` | Upload media from URL |
| `wp_list_drafts` | List all drafts |
| `wp_delete_all_drafts` | Bulk delete drafts |

### SEO Tools

| Tool | Description |
|------|-------------|
| `wp_get_seo` | Get SEO metadata for page/post |
| `wp_set_seo` | Set SEO metadata |
| `wp_analyze_seo` | Analyze SEO quality |
| `wp_bulk_seo` | Bulk update SEO |
| `wp_get_seo_plugin` | Detect active SEO plugin |

### Forms Tools

| Tool | Description |
|------|-------------|
| `wp_list_forms` | List all forms |
| `wp_get_form` | Get form details |
| `wp_create_form` | Create new form |
| `wp_update_form` | Update existing form |
| `wp_delete_form` | Delete a form |
| `wp_get_form_submissions` | Get form submissions |
| `wp_submit_form` | Submit form data |
| `wp_get_form_plugin` | Detect active form plugin |

### Elementor Tools

| Tool | Description |
|------|-------------|
| `wp_get_elementor` | Get Elementor page data |
| `wp_set_elementor` | Set Elementor page structure |
| `wp_list_elementor_templates` | List Elementor templates |
| `wp_apply_elementor_template` | Apply template to page |
| `wp_create_landing_page` | Create new landing page |
| `wp_add_elementor_section` | Add section to page |
| `wp_update_elementor_widget` | Update widget settings |
| `wp_get_elementor_globals` | Get global colors/fonts |
| `wp_clone_elementor_page` | Clone an Elementor page |

## Usage Examples

### Example 1: Get Site Info

In Claude Desktop:

```
Show me info about the WordPress site
```

Claude will call `wp_site_info` automatically.

### Example 2: Create Blog Post

```
Create a draft blog post titled "Summer Biking Adventures" with content about mountain trails
```

Claude will call `wp_create_post` with appropriate parameters.

### Example 3: Update SEO

```
Update SEO for page ID 123: title "Epic Biking Tours | Musical Unicorn",
description "Experience mountain biking like never before",
focus keyword "biking tours"
```

Claude will call `wp_set_seo` with the provided metadata.

### Example 4: Create Landing Page

```
Create an Elementor landing page titled "Fall Special" using template ID 456
```

Claude will call `wp_create_landing_page`.

## Testing with cURL

You can test the MCP endpoint directly with JSON-RPC requests:

### Initialize

```bash
curl -X POST https://your-worker.workers.dev/mcp \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "id": 1,
    "method": "initialize",
    "params": {
      "protocolVersion": "2024-11-05",
      "capabilities": {},
      "clientInfo": {
        "name": "test-client",
        "version": "1.0.0"
      }
    }
  }'
```

### List Tools

```bash
curl -X POST https://your-worker.workers.dev/mcp \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "id": 2,
    "method": "tools/list",
    "params": {}
  }'
```

### Call Tool

```bash
curl -X POST https://your-worker.workers.dev/mcp \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "id": 3,
    "method": "tools/call",
    "params": {
      "name": "wp_site_info",
      "arguments": {}
    }
  }'
```

### Batch Request

```bash
curl -X POST https://your-worker.workers.dev/mcp \
  -H "Content-Type: application/json" \
  -d '[
    {"jsonrpc": "2.0", "id": 1, "method": "tools/list"},
    {"jsonrpc": "2.0", "id": 2, "method": "ping"}
  ]'
```

## Error Handling

The MCP endpoint follows JSON-RPC 2.0 error codes:

| Code | Meaning |
|------|---------|
| -32700 | Parse error (invalid JSON) |
| -32600 | Invalid request |
| -32601 | Method not found |
| -32602 | Invalid params |
| -32603 | Internal error |

Example error response:

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "error": {
    "code": -32601,
    "message": "Method not found: unknown_method"
  }
}
```

## Security Notes

1. **No Bearer Auth**: The MCP endpoint does NOT require the `Authorization` header used by other routes
2. **WordPress API Key**: Security is handled by the WordPress plugin's `X-API-Key` header
3. **CORS**: Full CORS support for web-based MCP clients
4. **Session ID**: Supports `Mcp-Session-Id` header for session tracking

## Deployment

```bash
# Deploy to production
npm run deploy

# Deploy to staging
npm run deploy:staging

# View logs
npm run tail
```

## Troubleshooting

### Connection Failed

1. Check Claude Desktop config file syntax
2. Verify worker URL is correct and accessible
3. Check CloudFlare Worker logs: `npm run tail`

### Tool Calls Failing

1. Verify `SITE_CONFIGS` secret is set correctly
2. Test WordPress API directly: `curl https://your-site.com/wp-json/wp-ai-operator/v1/site-info -H "X-API-Key: your_key"`
3. Check WordPress plugin is active and REST API is enabled

### No Tools Showing

1. Check Claude Desktop console for errors
2. Verify MCP endpoint responds to `tools/list`: `curl -X POST your-worker/mcp -d '{"jsonrpc":"2.0","id":1,"method":"tools/list"}'`

## Development

```bash
# Local development with wrangler
npm run dev

# Test MCP locally
curl -X POST http://localhost:8787/mcp \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","id":1,"method":"tools/list"}'
```

## Related Documentation

- [MCP Specification](https://modelcontextprotocol.io/specification)
- [wp-ai-operator Plugin](../wordpress-plugin/)
- [REST API Documentation](../docs/API.md)
- [Cloudflare Workers Docs](https://developers.cloudflare.com/workers/)

## Support

- Issues: https://github.com/your-org/wp-ai-operator/issues
- Documentation: https://docs.example.com/wp-ai-operator
- Email: support@example.com
