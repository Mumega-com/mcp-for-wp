# Screenshot Worker Setup

Site Pilot AI can capture screenshots of any public URL using a Cloudflare Worker with Browser Rendering. This guide walks through deploying and configuring the screenshot service.

## Prerequisites

- **Cloudflare account** with Workers Paid plan (Browser Rendering is a paid feature)
- **Node.js** 18+ and npm installed
- **Wrangler CLI**: `npm install -g wrangler`

## 1. Deploy the Worker

The screenshot worker source is in `screenshot-worker/`.

```bash
cd screenshot-worker

# Install dependencies
npm install

# Authenticate with Cloudflare (if not already)
npx wrangler login

# Deploy the worker
npx wrangler deploy
```

After deployment, Wrangler will print the worker URL, e.g.:
```
https://spai-screenshot.<your-subdomain>.workers.dev
```

## 2. Set the Auth Token

Create a secret auth token that the plugin will use to authenticate:

```bash
npx wrangler secret put AUTH_TOKEN
# Enter a strong random token when prompted
```

Save this token — you'll need it for the plugin configuration.

## 3. Configure the Plugin

In your WordPress admin:

1. Go to **Site Pilot AI → Settings**
2. Find **Screenshot Worker URL** and enter the worker URL (e.g. `https://spai-screenshot.your-subdomain.workers.dev`)
3. Find **Screenshot Worker Token** and enter the auth token you set above
4. Click **Save**

## 4. Test with curl

```bash
curl -X POST https://spai-screenshot.your-subdomain.workers.dev \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_AUTH_TOKEN" \
  -d '{"url": "https://example.com", "width": 1280, "height": 900}'
```

A successful response returns:

```json
{
  "success": true,
  "screenshot": "<base64-encoded image>",
  "format": "png",
  "width": 1280,
  "height": 900,
  "full_page": false,
  "elapsed_ms": 3200,
  "url": "https://example.com"
}
```

## 5. Use via MCP

Once configured, the `wp_screenshot_url` MCP tool becomes available:

```json
{
  "tool": "wp_screenshot_url",
  "params": {
    "url": "https://yoursite.com/page",
    "width": 1280,
    "height": 900,
    "format": "png"
  }
}
```

## Worker Parameters

| Parameter | Type | Default | Range | Description |
|-----------|------|---------|-------|-------------|
| `url` | string | (required) | — | URL to screenshot |
| `width` | number | 1280 | 320–1920 | Viewport width |
| `height` | number | 900 | 240–1440 | Viewport height |
| `wait` | number | 2000 | 0–10000 | Extra wait (ms) after page load for JS rendering |
| `full_page` | boolean | false | — | Capture full scrollable page |
| `device_scale_factor` | number | 1 | 1–3 | Device pixel ratio (2 for Retina) |
| `format` | string | "png" | png, jpeg | Image format |
| `quality` | number | 80 | 1–100 | JPEG quality (ignored for PNG) |

## Authentication

The worker accepts auth tokens via either header:
- `Authorization: Bearer <token>`
- `X-Auth-Token: <token>`

If no `AUTH_TOKEN` secret is set, the worker runs without authentication (not recommended for production).

## Troubleshooting

### "Browser binding not found" error
The `[browser]` binding in `wrangler.toml` requires the Workers Paid plan with Browser Rendering enabled. Verify your plan at [Cloudflare Dashboard → Workers & Pages](https://dash.cloudflare.com/).

### 401 Unauthorized
- Verify the token matches exactly (no trailing whitespace)
- Check that the secret was set: `npx wrangler secret list`
- Re-set if needed: `npx wrangler secret put AUTH_TOKEN`

### Screenshots are blank or incomplete
- Increase the `wait` parameter (some JS-heavy sites need 3000–5000ms)
- Check that the target site doesn't block headless browsers via User-Agent or bot detection

### Cold start latency
First request after idle may take 5–10s due to browser instance cold start. Subsequent requests within the same session are faster (~2–4s).

### Internal URLs blocked
The worker blocks `localhost`, `127.0.0.1`, and private IP ranges (`10.x`, `192.168.x`, `172.x`) to prevent SSRF attacks. Only public URLs are allowed.

### CORS errors
The worker includes `Access-Control-Allow-Origin: *` headers. If you're calling from a browser, ensure your request uses the correct method (POST only).

## Architecture

```
AI Assistant → Site Pilot AI Plugin → Cloudflare Worker → Headless Chromium → Screenshot
                 (WordPress)           (spai-screenshot)    (Browser Rendering)
```

The plugin proxies the screenshot request to the Cloudflare Worker, which launches a headless Chromium browser via Cloudflare's Browser Rendering API, navigates to the URL, captures the screenshot, and returns the base64-encoded image.
