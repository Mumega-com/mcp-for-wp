# Cloudflare Gateway Setup

## Prerequisites

- [Cloudflare account](https://dash.cloudflare.com/sign-up)
- [Wrangler CLI](https://developers.cloudflare.com/workers/wrangler/install-and-update/)
- Node.js 18+

## Quick Setup

```bash
cd cloudflare-worker

# Install dependencies
npm install

# Login to Cloudflare
wrangler login

# Create KV namespace and D1 database
npm run setup
```

This outputs IDs like:
```
Created KV namespace "WP_CACHE" with id "abc123..."
Created D1 database "wp-ai-operator" with id "xyz789..."
```

## Configure wrangler.toml

Update the IDs in `wrangler.toml`:

```toml
[[kv_namespaces]]
binding = "WP_CACHE"
id = "abc123..."  # Your KV namespace ID

[[d1_databases]]
binding = "WP_DB"
database_name = "wp-ai-operator"
database_id = "xyz789..."  # Your D1 database ID
```

## Run Database Migrations

```bash
npm run db:migrate
```

## Set Secrets

```bash
# Authorization token for the gateway
wrangler secret put AUTHORIZED_TOKENS
# Enter a comma-separated list of tokens, e.g.: token1,token2

# Site configurations (JSON)
wrangler secret put SITE_CONFIGS
# Enter JSON like:
# {"production":{"id":"production","name":"Production","url":"https://example.com","apiKey":"wpaio_xxx"}}
```

## Deploy

```bash
npm run deploy
```

Your gateway is now live at: `https://wp-ai-gateway.<your-subdomain>.workers.dev`

## Configure MCP Server

Add to your environment:

```bash
export USE_GATEWAY=true
export GATEWAY_URL=https://wp-ai-gateway.<your-subdomain>.workers.dev
export GATEWAY_TOKEN=your-token-here
```

Or add to Claude Code config:

```json
{
  "mcpServers": {
    "wp-ai-operator": {
      "command": "node",
      "args": ["/path/to/wp-ai-operator/mcp-server/dist/index.js"],
      "env": {
        "USE_GATEWAY": "true",
        "GATEWAY_URL": "https://wp-ai-gateway.yourname.workers.dev",
        "GATEWAY_TOKEN": "your-token"
      }
    }
  }
}
```

## API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/health` | GET | Health check |
| `/sites` | GET | List configured sites |
| `/proxy/{siteId}/{endpoint}` | ANY | Proxy to WordPress API |
| `/batch` | POST | Execute multiple operations |
| `/cache/stats` | GET | Cache statistics |
| `/analytics` | GET | Request analytics |

## Example Usage

```bash
# Health check
curl https://wp-ai-gateway.yourname.workers.dev/health

# Get site info (proxied through gateway)
curl -H "Authorization: Bearer your-token" \
  https://wp-ai-gateway.yourname.workers.dev/proxy/production/site-info

# Batch operations
curl -X POST -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -d '{"operations":[{"site":"production","endpoint":"/site-info"},{"site":"staging","endpoint":"/site-info"}]}' \
  https://wp-ai-gateway.yourname.workers.dev/batch

# Analytics
curl -H "Authorization: Bearer your-token" \
  "https://wp-ai-gateway.yourname.workers.dev/analytics?days=7"
```

## Local Development

```bash
npm run dev
```

This starts a local worker at `http://localhost:8787`

## Cache Behavior

| Endpoint Pattern | TTL |
|------------------|-----|
| `site-info` | 1 hour |
| `plugins` | 1 hour |
| `templates` | 24 hours |
| `globals` | 12 hours |
| `posts`, `pages` | 5 minutes |
| Default | 1 minute |

## Monitoring

View real-time logs:
```bash
npm run tail
```

View analytics in Cloudflare dashboard or via API:
```bash
curl -H "Authorization: Bearer token" \
  "https://wp-ai-gateway.yourname.workers.dev/analytics?days=7&site=production"
```

## Cost

Free tier covers:
- 100,000 Worker requests/day
- 1GB KV storage
- 5GB D1 storage
- 10GB R2 storage

For 11 sites with moderate usage, you'll stay well within free tier.
