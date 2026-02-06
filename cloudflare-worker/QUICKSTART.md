# MCP Quickstart Guide

Get the WordPress MCP server running in Claude Desktop in 5 minutes.

## Prerequisites

- Cloudflare account with Workers enabled
- WordPress site with wp-ai-operator plugin installed
- Claude Desktop app

## Step 1: Deploy Worker (2 minutes)

```bash
cd /home/mumega/projects/themusicalunicorn/wp-ai-operator/cloudflare-worker

# Install dependencies
npm install

# Configure site
wrangler secret put SITE_CONFIGS
# Paste this JSON when prompted:
{
  "default": {
    "id": "default",
    "name": "Musical Unicorn Farm",
    "url": "https://musicalunicornfarm.com",
    "apiKey": "digid_YOUR_KEY_HERE"
  }
}

# Deploy
npm run deploy
```

Copy the worker URL from the output, e.g., `https://wp-ai-gateway.your-subdomain.workers.dev`

## Step 2: Test MCP Endpoint (1 minute)

```bash
# Set worker URL
export WORKER_URL="https://wp-ai-gateway.your-subdomain.workers.dev"

# Run test suite
./test-mcp.sh
```

You should see:
```
✓ Initialize successful
✓ Tools list retrieved. Total: 36 tools
✓ Ping successful
✓ Error handling works
✓ Batch request successful
✓ CORS preflight successful
```

## Step 3: Configure Claude Desktop (2 minutes)

### macOS
```bash
# Open config file
nano ~/Library/Application\ Support/Claude/claude_desktop_config.json
```

### Windows
```bash
# Open config file
notepad %APPDATA%\Claude\claude_desktop_config.json
```

### Linux
```bash
# Open config file
nano ~/.config/Claude/claude_desktop_config.json
```

Add this configuration:

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

**Important:** Replace `your-subdomain` with your actual worker subdomain.

Save and restart Claude Desktop.

## Step 4: Verify in Claude Desktop

1. Open Claude Desktop
2. Look for the MCP icon (🔌) in the bottom right
3. Click it - you should see "wordpress-unicorn" connected
4. Click "wordpress-unicorn" to see 36 available tools

## Step 5: Test It

Try these prompts in Claude Desktop:

### Basic Info
```
Show me information about the WordPress site
```

Claude will call `wp_site_info` and display your site details.

### Create Content
```
Create a draft blog post titled "Test Post" with content "This is a test"
```

Claude will call `wp_create_post` and return the new post ID.

### List Content
```
List the 5 most recent blog posts
```

Claude will call `wp_list_posts` with appropriate parameters.

## Troubleshooting

### Issue: "Connection failed" in Claude Desktop

**Check 1:** Worker URL is correct
```bash
curl https://your-worker.workers.dev/mcp -X POST \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","id":1,"method":"ping"}'

# Should return: {"jsonrpc":"2.0","id":1,"result":{"pong":true,...}}
```

**Check 2:** Claude Desktop config syntax
```bash
# Validate JSON
cat ~/Library/Application\ Support/Claude/claude_desktop_config.json | jq .
```

**Check 3:** Restart Claude Desktop completely (quit and reopen)

### Issue: Tools showing but calls failing

**Check 1:** WordPress API is accessible
```bash
curl https://musicalunicornfarm.com/wp-json/wp-ai-operator/v1/site-info \
  -H "X-API-Key: YOUR_KEY"

# Should return site info JSON
```

**Check 2:** API key is correct in SITE_CONFIGS
```bash
wrangler secret list
# Should show SITE_CONFIGS

# Re-set if needed
wrangler secret put SITE_CONFIGS
```

**Check 3:** Check worker logs
```bash
npm run tail
# Then make a tool call in Claude Desktop
# Watch for errors
```

### Issue: No tools showing in Claude Desktop

**Check 1:** MCP endpoint returns tools
```bash
curl https://your-worker.workers.dev/mcp -X POST \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","id":1,"method":"tools/list"}'

# Should return 36 tools
```

**Check 2:** Check Claude Desktop console (Help → Toggle Developer Tools)
Look for MCP connection errors.

## Common Prompts

### Site Management
- "Show site analytics for the last 30 days"
- "What plugins are installed?"
- "Detect which SEO plugin is active"

### Content Creation
- "Create a draft page titled 'About Us'"
- "Update post 123 with new content..."
- "List all draft posts"

### SEO Optimization
- "Get SEO data for page 45"
- "Set SEO title and description for post 67"
- "Analyze SEO quality of page 89"

### Forms
- "List all forms on the site"
- "Get submissions for form 12"

### Elementor
- "Get Elementor structure for page 34"
- "List all Elementor templates"
- "Create a landing page titled 'Special Offer'"

## What's Next?

### For Developers

1. **Add More Sites:** Update SITE_CONFIGS with multiple sites
2. **Customize Tools:** Edit `src/tools.ts` to add/modify tools
3. **Add Logging:** Integrate MCP calls with D1 activity log
4. **Add Caching:** Cache GET tool calls in KV namespace

### For Content Creators

1. **Create Workflows:** Build repeatable content creation prompts
2. **Batch Operations:** Use multiple tool calls in one conversation
3. **SEO Optimization:** Automate SEO metadata updates
4. **Landing Pages:** Generate Elementor pages from prompts

## Advanced Configuration

### Multiple Sites

```json
{
  "default": {
    "id": "default",
    "name": "Main Site",
    "url": "https://site1.com",
    "apiKey": "digid_key1"
  },
  "blog": {
    "id": "blog",
    "name": "Blog Site",
    "url": "https://blog.site.com",
    "apiKey": "digid_key2"
  }
}
```

Note: Currently only "default" site is used by MCP endpoint. Multi-site support planned for future release.

### Development Mode

```bash
# Run worker locally
npm run dev

# Test against local worker
export WORKER_URL="http://localhost:8787"
./test-mcp.sh

# Update Claude Desktop to use local worker (for testing)
{
  "mcpServers": {
    "wordpress-local": {
      "transport": {
        "type": "streamableHttp",
        "url": "http://localhost:8787/mcp"
      }
    }
  }
}
```

## Resources

- **Full Documentation:** `MCP_README.md`
- **Implementation Details:** `IMPLEMENTATION_SUMMARY.md`
- **WordPress Plugin:** `../wordpress-plugin/`
- **MCP Specification:** https://modelcontextprotocol.io

## Support

Questions? Issues?

1. Check worker logs: `npm run tail`
2. Run test suite: `./test-mcp.sh`
3. Check WordPress plugin is active
4. Verify API key is correct

Happy WordPress automation with Claude!
