# Claude Desktop Setup for Site Pilot AI

## Quick Start

Connect Claude Desktop directly to your WordPress site using the native MCP endpoint.

## Prerequisites

1. Site Pilot AI plugin installed and activated on WordPress
2. API key generated (go to WordPress Admin → Tools → Site Pilot AI)
3. Claude Desktop installed on your computer

## Setup Steps

### 1. Get Your API Key

In WordPress admin:
1. Go to **Tools → Site Pilot AI**
2. Copy your API key (starts with `spai_`)
3. If no key exists, click "Generate New Key"

### 2. Configure Claude Desktop

Open your Claude Desktop MCP configuration file:

**macOS:**
```bash
~/Library/Application Support/Claude/claude_desktop_config.json
```

**Windows:**
```
%APPDATA%\Claude\claude_desktop_config.json
```

**Linux:**
```
~/.config/Claude/claude_desktop_config.json
```

### 3. Add Your WordPress Site

Add this configuration:

```json
{
  "mcpServers": {
    "wordpress-musical-unicorn": {
      "transport": {
        "type": "http",
        "url": "https://musicalunicornfarm.com/wp-json/site-pilot-ai/v1/mcp",
        "headers": {
          "X-API-Key": "spai_your_actual_api_key_here"
        }
      }
    }
  }
}
```

**Replace:**
- `wordpress-musical-unicorn` - Any unique name for this site
- `musicalunicornfarm.com` - Your actual domain
- `spai_your_actual_api_key_here` - Your actual API key

### 4. Restart Claude Desktop

Close and reopen Claude Desktop to load the new configuration.

### 5. Verify Connection

In Claude Desktop, start a new conversation and ask:

> "What tools do you have available for WordPress?"

Claude should list 17+ WordPress tools including:
- wp_site_info
- wp_list_posts
- wp_create_post
- wp_list_pages
- etc.

## Multiple Sites

You can connect multiple WordPress sites:

```json
{
  "mcpServers": {
    "wordpress-musical-unicorn": {
      "transport": {
        "type": "http",
        "url": "https://musicalunicornfarm.com/wp-json/site-pilot-ai/v1/mcp",
        "headers": {
          "X-API-Key": "spai_key_for_musical_unicorn"
        }
      }
    },
    "wordpress-my-other-site": {
      "transport": {
        "type": "http",
        "url": "https://myothersite.com/wp-json/site-pilot-ai/v1/mcp",
        "headers": {
          "X-API-Key": "spai_key_for_other_site"
        }
      }
    }
  }
}
```

## Example Prompts

Once connected, you can ask Claude:

### Get Site Information
> "Show me information about my WordPress site"

### List Recent Posts
> "What are my 5 most recent published blog posts?"

### Create a Draft Post
> "Create a new blog post titled 'My Summer Adventures' with a brief introduction. Keep it as a draft."

### Update SEO (PRO only)
> "Update the SEO title and description for page ID 42"

### Manage Elementor Pages
> "Get the Elementor data for page 123"

### Check What's Possible
> "What can you help me do with my WordPress site?"

## Available Capabilities

### FREE (17 tools)

**Site Management:**
- Get site info (name, URL, version, theme, plugins)
- View analytics (post counts, page views, etc.)
- Detect installed plugins and capabilities

**Content Creation:**
- List/create/update/delete blog posts
- List/create/update pages
- Manage drafts

**Media:**
- Upload files
- Upload from URL

**Elementor:**
- Get/set page data
- Check Elementor status

### PRO (13 additional tools)

**SEO:**
- Read/write SEO metadata (Yoast, Rank Math)
- Analyze SEO
- Bulk SEO updates

**Forms:**
- List forms (Contact Form 7, WPForms, Gravity Forms)
- Read form details
- View submissions

**Elementor Pro:**
- List/apply templates
- Create landing pages
- Clone pages
- Access global settings

## Troubleshooting

### Claude doesn't see any tools

1. **Check config file location** - Make sure you edited the right file
2. **Verify JSON syntax** - Use a JSON validator (jsonlint.com)
3. **Restart Claude Desktop** - Close completely and reopen
4. **Check URL** - Must be full URL including `/wp-json/site-pilot-ai/v1/mcp`

### "Connection failed" or "Unauthorized"

1. **Verify API key** - Copy exact key from WordPress admin
2. **Check URL** - Make sure site is accessible
3. **Test manually:**
   ```bash
   curl -X POST "https://yoursite.com/wp-json/site-pilot-ai/v1/mcp" \
     -H "Content-Type: application/json" \
     -H "X-API-Key: spai_xxx" \
     -d '{"jsonrpc":"2.0","method":"ping","id":1}'
   ```
   Should return: `{"jsonrpc":"2.0","id":1,"result":{"pong":true}}`

### Tools list is empty

1. **Check plugin version** - Must be v1.0.14+ with MCP support
2. **Verify REST API works** - Visit `https://yoursite.com/wp-json/`
3. **Check permalinks** - Must be enabled (not default)

### Rate limited

1. Go to WordPress Admin → Tools → Site Pilot AI
2. Check rate limit settings
3. Increase limits or disable rate limiting

## Manual Testing (without Claude Desktop)

Test the endpoint with curl:

```bash
# Set your variables
SITE_URL="https://musicalunicornfarm.com"
API_KEY="spai_your_key_here"

# Test ping
curl -X POST "${SITE_URL}/wp-json/site-pilot-ai/v1/mcp" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: ${API_KEY}" \
  -d '{"jsonrpc":"2.0","method":"ping","id":1}' | jq

# List tools
curl -X POST "${SITE_URL}/wp-json/site-pilot-ai/v1/mcp" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: ${API_KEY}" \
  -d '{"jsonrpc":"2.0","method":"tools/list","id":1}' | jq

# Get site info
curl -X POST "${SITE_URL}/wp-json/site-pilot-ai/v1/mcp" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: ${API_KEY}" \
  -d '{"jsonrpc":"2.0","method":"tools/call","id":1,"params":{"name":"wp_site_info","arguments":{}}}' | jq
```

## Security Best Practices

1. **Keep API key secret** - Don't commit to git
2. **Use environment variables** - Store key in secure location
3. **Regenerate if exposed** - Can regenerate in WordPress admin
4. **Use HTTPS** - Always use secure connection
5. **Enable rate limiting** - Prevent abuse

## Example Config File

Complete example for Musical Unicorn Farm:

```json
{
  "mcpServers": {
    "wordpress-musical-unicorn": {
      "transport": {
        "type": "http",
        "url": "https://musicalunicornfarm.com/wp-json/site-pilot-ai/v1/mcp",
        "headers": {
          "X-API-Key": "spai_ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefgh"
        }
      },
      "description": "Musical Unicorn Farm - Tours, bikes, events"
    }
  },
  "globalShortcut": "Ctrl+Shift+Space"
}
```

## Pro Tips

1. **Be specific** - Tell Claude which site to use if you have multiple
2. **Check capabilities first** - Ask what tools are available
3. **Use drafts** - Create posts as drafts first, review, then publish
4. **Batch operations** - Ask Claude to do multiple things at once
5. **Review before publishing** - Always review AI-generated content

## Support

Need help?
- Documentation: See `docs/MCP_NATIVE_ENDPOINT.md`
- GitHub: https://github.com/Digidinc/site-pilot-ai
- Email: support@digid.ca

## What's Next?

Once connected, Claude can help you:
- Write and publish blog posts
- Create landing pages
- Manage content
- Optimize SEO
- Analyze site data
- Automate workflows

Just chat naturally - Claude knows how to use all the tools!

---

**Last Updated:** 2024-02-06
**Plugin Version Required:** 1.0.14+
**MCP Protocol:** 2024-11-05
