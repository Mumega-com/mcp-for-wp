# WP AI Operator

Control your WordPress site with AI. Works with **Claude Code** and **Claude Desktop**.

```
You: "Create a landing page for summer biking tours with hero image,
      3 features, and a 'Book Now' button"

Claude: *Creates page, uploads image, builds Elementor layout, sets SEO*
        "Done! Here's your page: https://yoursite.com/summer-tours"
```

## Features

- **Full WordPress Control** - Create/edit posts, pages, media
- **Elementor Integration** - Build and modify page layouts via API
- **SEO Management** - Set titles, descriptions, focus keywords
- **Landing Page Builder** - High-level tool for quick page creation
- **Multi-site Ready** - Manage multiple WordPress sites

## Quick Start

### 1. Install WordPress Plugin

Download `plugin/wp-ai-operator.php` and upload to your WordPress site:

```bash
# Via WP-CLI
wp plugin install /path/to/wp-ai-operator.php --activate

# Or upload via WP Admin > Plugins > Add New > Upload
```

### 2. Get Your API Key

Go to **WP Admin > Tools > AI Operator** and copy your API key.

### 3. Configure Claude Code

Add to `~/.claude/mcp.json`:

```json
{
  "mcpServers": {
    "wordpress": {
      "command": "npx",
      "args": ["-y", "wp-ai-operator"],
      "env": {
        "WP_URL": "https://yoursite.com",
        "WP_API_KEY": "digid_your_api_key_here"
      }
    }
  }
}
```

### 4. Start Using

```
You: "List all pages on my site"
Claude: *calls wp_list_pages tool*

You: "Create a blog post about mountain biking safety tips"
Claude: *calls wp_create_post tool*

You: "Upload this image and set it as the featured image for page 45"
Claude: *calls wp_upload_media, then wp_update_page*
```

## Available Tools

### Site Management
| Tool | Description |
|------|-------------|
| `wp_site_info` | Get site name, URL, theme, stats |
| `wp_analytics` | Get activity logs and metrics |

### Content
| Tool | Description |
|------|-------------|
| `wp_list_posts` | List published posts |
| `wp_create_post` | Create new blog post |
| `wp_update_post` | Edit existing post |
| `wp_delete_post` | Delete post |
| `wp_list_drafts` | List all drafts |
| `wp_delete_all_drafts` | Bulk delete drafts |

### Pages
| Tool | Description |
|------|-------------|
| `wp_list_pages` | List all pages |
| `wp_create_page` | Create new page (with Elementor support) |
| `wp_update_page` | Edit existing page |

### Elementor
| Tool | Description |
|------|-------------|
| `wp_get_elementor` | Get Elementor JSON for a page |
| `wp_set_elementor` | Update Elementor layout |

### Media
| Tool | Description |
|------|-------------|
| `wp_upload_media` | Upload local file |
| `wp_upload_media_from_url` | Download and upload from URL |

### SEO
| Tool | Description |
|------|-------------|
| `wp_get_seo` | Get SEO metadata |
| `wp_set_seo` | Set title, description, keyword |

### High-Level
| Tool | Description |
|------|-------------|
| `wp_create_landing_page` | Create complete landing page with hero, features, CTA |

## Example Workflows

### Create a Landing Page

```
You: Create a landing page with:
- Title: "Summer Adventure Tours"
- Headline: "Ride Beyond Limits"
- Subheadline: "Epic mountain biking experiences"
- Features: Expert Guides, Mountain Trails, All Skill Levels
- CTA: "Book Your Adventure" linking to /contact
```

Claude will:
1. Create the page
2. Build Elementor layout with hero section
3. Add feature cards
4. Set up CTA button
5. Configure SEO
6. Return the page URL

### Upload Images and Create Post

```
You: Upload the image at /path/to/hero.jpg and create a blog post
titled "Top 10 Biking Trails" using it as the featured image
```

Claude will:
1. Upload image to media library
2. Create draft post
3. Set featured image
4. Return post URL for review

### Bulk SEO Update

```
You: Update SEO for pages 45, 67, and 89:
- Add "| Mountain Bikes" to all titles
- Set focus keyword to "mountain biking tours"
```

Claude will call `wp_set_seo` for each page.

## API Reference

All endpoints use the `/wp-json/digid/v1/` namespace.

### Authentication

Include API key in request header:
```
X-API-Key: digid_your_key_here
```

Or as query parameter:
```
?api_key=digid_your_key_here
```

### Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/site-info` | Site information |
| GET | `/analytics` | Activity metrics |
| GET | `/posts` | List posts |
| POST | `/posts` | Create post |
| PUT | `/posts/{id}` | Update post |
| DELETE | `/posts/{id}` | Delete post |
| GET | `/pages` | List pages |
| POST | `/pages` | Create page |
| PUT | `/pages/{id}` | Update page |
| GET | `/elementor/{id}` | Get Elementor data |
| POST | `/elementor/{id}` | Set Elementor data |
| POST | `/media` | Upload file |
| GET | `/seo/{id}` | Get SEO metadata |
| POST | `/seo/{id}` | Set SEO metadata |
| GET | `/drafts` | List drafts |
| DELETE | `/drafts/delete-all` | Delete all drafts |

## Development

### Build MCP Server

```bash
cd mcp-server
npm install
npm run build
```

### Test Locally

```bash
# Set environment variables
export WP_URL="https://yoursite.com"
export WP_API_KEY="digid_xxx"

# Run server
npm run dev
```

### Project Structure

```
wp-ai-operator/
├── mcp-server/
│   ├── src/
│   │   └── index.ts      # MCP server implementation
│   ├── package.json
│   └── tsconfig.json
├── plugin/
│   └── wp-ai-operator.php  # WordPress plugin
├── templates/
│   └── landing-page.json   # Elementor templates
├── docs/
│   └── API.md
└── README.md
```

## Multi-Site Configuration

For managing multiple WordPress sites, create a config file:

```json
// ~/.wp-ai-operator/sites.json
{
  "sites": {
    "main": {
      "url": "https://site1.com",
      "api_key": "digid_xxx"
    },
    "blog": {
      "url": "https://blog.site1.com",
      "api_key": "digid_yyy"
    }
  }
}
```

Then specify site in prompts:
```
You: "On the 'blog' site, create a post about..."
```

## Troubleshooting

### "WordPress credentials not configured"

Ensure `WP_URL` and `WP_API_KEY` environment variables are set in your MCP config.

### "Permission denied" errors

1. Check API key is correct (WP Admin > Tools > AI Operator)
2. Regenerate key if needed
3. Verify plugin is activated

### Elementor layouts not applying

1. Ensure Elementor Pro is installed and active
2. Page template should be "Elementor Full Width" or "Elementor Canvas"
3. Check `_elementor_edit_mode` meta is set to "builder"

## Security

- API keys are stored in WordPress options table
- All inputs are sanitized via WordPress functions
- Activity logging tracks all API requests
- Supports Yoast SEO and RankMath integration

## License

GPL v2 or later

## Credits

Built by [DigID](https://digid.ca) for the Mumega ecosystem.
