# Site Pilot AI - API Documentation

> Control WordPress with AI through a powerful REST API

**Base URL:** `https://your-site.com/wp-json/site-pilot-ai/v1`
**Version:** 1.0.0

## Table of Contents

- [Authentication](#authentication)
- [Error Handling](#error-handling)
- [Rate Limiting](#rate-limiting)
- [Endpoints](#endpoints)
  - [Site Info](#site-info)
  - [Posts](#posts)
  - [Pages](#pages)
  - [Media](#media)
  - [Elementor (Free)](#elementor-free)
  - [Elementor Pro](#elementor-pro)
  - [SEO](#seo)
  - [Forms](#forms)
  - [Users](#users)
  - [Menus](#menus)
  - [Settings](#settings)
  - [Theme Builder](#theme-builder)
- [MCP Server Configuration](#mcp-server-configuration)
- [AI Integration Examples](#ai-integration-examples)

---

## Authentication

All API requests require authentication via API key.

### Getting Your API Key

1. Go to **WordPress Admin → Tools → Site Pilot AI**
2. Copy your API key or generate a new one

### Authentication Methods

#### Header Authentication (Recommended)

```bash
curl -H "X-API-Key: spai_your_api_key_here" \
  https://your-site.com/wp-json/site-pilot-ai/v1/site-info
```

#### Bearer Token

```bash
curl -H "Authorization: Bearer spai_your_api_key_here" \
  https://your-site.com/wp-json/site-pilot-ai/v1/site-info
```

#### Query Parameter (Not Recommended)

```bash
curl "https://your-site.com/wp-json/site-pilot-ai/v1/site-info?api_key=spai_your_api_key_here"
```

---

## Error Handling

### Error Response Format

```json
{
  "code": "error_code",
  "message": "Human-readable error message",
  "data": {
    "status": 400
  }
}
```

### Common Error Codes

| Code | Status | Description |
|------|--------|-------------|
| `missing_api_key` | 401 | API key not provided |
| `invalid_api_key` | 401 | API key is incorrect |
| `api_not_configured` | 500 | API key not set up in WordPress |
| `not_found` | 404 | Resource not found |
| `invalid_param` | 400 | Invalid parameter value |
| `missing_required` | 400 | Required parameter missing |
| `permission_denied` | 403 | Insufficient permissions |

---

## Rate Limiting

Default rate limits (configurable in settings):

| Tier | Requests/Minute | Requests/Hour |
|------|-----------------|---------------|
| Free | 60 | 1,000 |
| Pro | 300 | 10,000 |
| Agency | Unlimited | Unlimited |

Rate limit headers are included in responses:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1699574400
```

---

## Endpoints

### Site Info

#### Get Site Information

```http
GET /site-info
```

Returns WordPress site details and detected capabilities.

**Response:**

```json
{
  "name": "My Website",
  "description": "Just another WordPress site",
  "url": "https://example.com",
  "admin_url": "https://example.com/wp-admin/",
  "wp_version": "6.4.2",
  "php_version": "8.2.0",
  "theme": {
    "name": "Flavor flavor flavor flavor flavore flavor",
    "version": "1.0"
  },
  "timezone": "America/New_York",
  "language": "en_US",
  "capabilities": {
    "elementor": true,
    "elementor_pro": true,
    "woocommerce": false,
    "yoast": true,
    "rankmath": false,
    "cf7": true,
    "wpforms": false
  },
  "plugin": {
    "name": "Site Pilot AI",
    "version": "1.0.0"
  }
}
```

#### Get Plugin Detection

```http
GET /plugins
```

Returns detailed plugin detection information.

---

### Posts

#### List Posts

```http
GET /posts
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `per_page` | integer | 10 | Posts per page (max: 100) |
| `page` | integer | 1 | Page number |
| `status` | string | publish | Post status filter |
| `category` | integer | - | Category ID filter |
| `search` | string | - | Search term |

**Example:**

```bash
curl -H "X-API-Key: spai_xxx" \
  "https://example.com/wp-json/site-pilot-ai/v1/posts?per_page=5&status=publish"
```

**Response:**

```json
{
  "posts": [
    {
      "id": 123,
      "title": "Hello World",
      "slug": "hello-world",
      "status": "publish",
      "content": "<p>Welcome to WordPress...</p>",
      "excerpt": "Welcome to WordPress...",
      "author": 1,
      "date": "2024-01-15T10:30:00",
      "modified": "2024-01-15T10:30:00",
      "link": "https://example.com/hello-world/",
      "featured_image": null,
      "categories": [1],
      "tags": []
    }
  ],
  "total": 42,
  "pages": 9,
  "page": 1
}
```

#### Create Post

```http
POST /posts
```

**Body:**

```json
{
  "title": "My New Post",
  "content": "<p>This is the post content.</p>",
  "status": "publish",
  "excerpt": "A brief summary"
}
```

**Response:** Returns the created post object with `201 Created` status.

#### Get Single Post

```http
GET /posts/{id}
```

#### Update Post

```http
PUT /posts/{id}
```

**Body:**

```json
{
  "title": "Updated Title",
  "content": "Updated content"
}
```

#### Delete Post

```http
DELETE /posts/{id}
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `force` | boolean | false | Permanently delete (bypass trash) |

---

### Pages

#### List Pages

```http
GET /pages
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `per_page` | integer | 10 | Pages per page |
| `page` | integer | 1 | Page number |
| `status` | string | any | Page status |
| `parent` | integer | - | Parent page ID |

#### Create Page

```http
POST /pages
```

**Body:**

```json
{
  "title": "About Us",
  "content": "<p>About our company...</p>",
  "status": "publish",
  "parent": 0,
  "template": "templates/full-width.php"
}
```

#### Get/Update Page

```http
GET /pages/{id}
PUT /pages/{id}
```

---

### Media

#### List Media

```http
GET /media
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `per_page` | integer | 20 | Items per page |
| `page` | integer | 1 | Page number |
| `mime_type` | string | - | Filter by MIME type (image, video, etc.) |

#### Upload Media

```http
POST /media
Content-Type: multipart/form-data
```

**Form Data:**

- `file` - The file to upload
- `title` - Optional title
- `alt` - Optional alt text

**Example with curl:**

```bash
curl -H "X-API-Key: spai_xxx" \
  -F "file=@/path/to/image.jpg" \
  -F "title=My Image" \
  -F "alt=Description of image" \
  https://example.com/wp-json/site-pilot-ai/v1/media
```

#### Upload from URL

```http
POST /media/from-url
```

**Body:**

```json
{
  "url": "https://example.com/image.jpg",
  "title": "Downloaded Image",
  "alt": "Image description",
  "filename": "custom-name.jpg"
}
```

---

### Elementor (Free)

#### Get Elementor Status

```http
GET /elementor/status
```

**Response:**

```json
{
  "active": true,
  "version": "3.18.0",
  "pro": true,
  "pro_version": "3.18.0"
}
```

#### Get Page Elementor Data

```http
GET /elementor/{id}
```

Returns the Elementor JSON structure for a page.

#### Update Page Elementor Data

```http
POST /elementor/{id}
```

**Body:**

```json
{
  "elementor_data": [
    {
      "id": "abc123",
      "elType": "section",
      "settings": {},
      "elements": [
        {
          "id": "def456",
          "elType": "column",
          "settings": {"_column_size": 100},
          "elements": [
            {
              "id": "ghi789",
              "elType": "widget",
              "widgetType": "heading",
              "settings": {
                "title": "Hello World",
                "header_size": "h1"
              }
            }
          ]
        }
      ]
    }
  ]
}
```

#### Create Elementor Page

```http
POST /elementor/page
```

**Body:**

```json
{
  "title": "New Landing Page",
  "status": "draft",
  "elementor_data": []
}
```

---

### Elementor Pro

*Requires Pro license*

#### List Templates

```http
GET /elementor/templates
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `per_page` | integer | 50 | Templates per page |
| `type` | string | - | Template type filter |

#### Create Template

```http
POST /elementor/templates
```

**Body:**

```json
{
  "title": "Hero Section Template",
  "type": "section",
  "elementor_data": []
}
```

#### Apply Template to Page

```http
POST /elementor/templates/{id}/apply
```

**Body:**

```json
{
  "page_id": 123
}
```

#### Clone Page

```http
POST /elementor/clone
```

**Body:**

```json
{
  "source_id": 123,
  "title": "Page Copy",
  "status": "draft"
}
```

#### Create Landing Page

```http
POST /elementor/landing-page
```

**Body:**

```json
{
  "title": "Product Launch",
  "status": "draft",
  "sections": ["hero", "features", "testimonials", "cta"]
}
```

#### Get Available Widgets

```http
GET /elementor/widgets
```

#### Get Global Settings

```http
GET /elementor/globals
```

---

### SEO

*Requires Pro license. Supports Yoast, RankMath, AIOSEO, SEOPress*

#### Get SEO Status

```http
GET /seo/status
```

**Response:**

```json
{
  "active_plugin": "yoast",
  "plugins": {
    "yoast": true,
    "rankmath": false,
    "aioseo": false,
    "seopress": false
  }
}
```

#### Get Post SEO Data

```http
GET /seo/{id}
```

**Response:**

```json
{
  "post_id": 123,
  "title": "Custom SEO Title",
  "description": "Meta description for search engines",
  "focus_keyword": "wordpress seo",
  "canonical": "https://example.com/page/",
  "og_title": "Social Share Title",
  "og_description": "Social share description",
  "og_image": "https://example.com/image.jpg",
  "robots_noindex": false,
  "robots_nofollow": false,
  "score": 85
}
```

#### Update Post SEO

```http
PUT /seo/{id}
```

**Body:**

```json
{
  "title": "Optimized SEO Title | Brand",
  "description": "Compelling meta description under 160 characters.",
  "focus_keyword": "target keyword",
  "og_title": "Share on Social",
  "og_description": "Description for social media",
  "robots_noindex": false
}
```

#### Bulk Update SEO

```http
POST /seo/bulk
```

**Body:**

```json
{
  "updates": [
    {"id": 123, "title": "Page 1 SEO Title"},
    {"id": 124, "title": "Page 2 SEO Title"},
    {"id": 125, "title": "Page 3 SEO Title"}
  ]
}
```

#### Analyze SEO

```http
GET /seo/{id}/analyze
```

Returns SEO analysis and recommendations.

---

### Forms

*Requires Pro license. Supports CF7, WPForms, Gravity Forms, Ninja Forms*

#### Get Forms Status

```http
GET /forms/status
```

**Response:**

```json
{
  "cf7": true,
  "wpforms": false,
  "gravityforms": true,
  "ninjaforms": false
}
```

#### List All Forms

```http
GET /forms
```

#### List Forms by Plugin

```http
GET /forms/{plugin}
```

Where `{plugin}` is: `cf7`, `wpforms`, `gravityforms`, or `ninjaforms`

#### Get Form Details

```http
GET /forms/{plugin}/{id}
```

#### Get Form Entries

```http
GET /forms/{plugin}/{id}/entries
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `per_page` | integer | 50 | Entries per page |
| `offset` | integer | 0 | Offset for pagination |

---

### Users

*Requires Pro license*

#### List Users

```http
GET /users
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `per_page` | integer | 50 | Users per page |
| `page` | integer | 1 | Page number |
| `role` | string | - | Filter by role |
| `search` | string | - | Search term |

#### Create User

```http
POST /users
```

**Body:**

```json
{
  "username": "newuser",
  "email": "user@example.com",
  "password": "SecurePass123!",
  "display_name": "New User",
  "first_name": "New",
  "last_name": "User",
  "role": "editor",
  "send_notification": true
}
```

#### Get User

```http
GET /users/{id}
```

#### Update User

```http
PUT /users/{id}
```

**Body:**

```json
{
  "display_name": "Updated Name",
  "role": "author"
}
```

#### Delete User

```http
DELETE /users/{id}
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `reassign` | integer | User ID to reassign posts to |

#### Get User Stats

```http
GET /users/stats
```

#### Get All Roles

```http
GET /users/roles
```

#### Get User Capabilities

```http
GET /users/{id}/capabilities
```

---

### Menus

*Requires Pro license*

#### List Menus

```http
GET /menus
```

#### Create Menu

```http
POST /menus
```

**Body:**

```json
{
  "name": "Main Navigation",
  "location": "primary"
}
```

#### Get Menu Locations

```http
GET /menus/locations
```

#### Get/Update/Delete Menu

```http
GET /menus/{id}
PUT /menus/{id}
DELETE /menus/{id}
```

#### Add Menu Item

```http
POST /menus/{menu_id}/items
```

**Body:**

```json
{
  "title": "About Us",
  "url": "/about/",
  "type": "custom",
  "parent_id": 0,
  "position": 1
}
```

#### Update/Delete Menu Item

```http
PUT /menus/{menu_id}/items/{item_id}
DELETE /menus/{menu_id}/items/{item_id}
```

---

### Settings

*Requires Pro license*

#### Get All Settings

```http
GET /settings
```

**Response:**

```json
{
  "title": "My Website",
  "tagline": "Just another WordPress site",
  "admin_email": "admin@example.com",
  "timezone": "America/New_York",
  "date_format": "F j, Y",
  "time_format": "g:i a",
  "posts_per_page": 10,
  "show_on_front": "page",
  "page_on_front": 2,
  "page_for_posts": 10
}
```

#### Update Settings

```http
PUT /settings
```

**Body:**

```json
{
  "title": "New Site Title",
  "tagline": "A better tagline",
  "posts_per_page": 12
}
```

---

### Theme Builder

*Requires Pro license and Elementor Pro*

#### Get Status

```http
GET /theme-builder/status
```

#### Get Theme Locations

```http
GET /theme-builder/locations
```

**Response:**

```json
{
  "locations": [
    {"id": "header", "label": "Header", "templates": []},
    {"id": "footer", "label": "Footer", "templates": []},
    {"id": "single", "label": "Single Post", "templates": []},
    {"id": "archive", "label": "Archive", "templates": []}
  ]
}
```

#### Get Available Conditions

```http
GET /theme-builder/conditions
```

#### List Theme Builder Templates

```http
GET /theme-builder/templates
```

#### Get/Set Template Conditions

```http
GET /theme-builder/templates/{id}/conditions
PUT /theme-builder/templates/{id}/conditions
DELETE /theme-builder/templates/{id}/conditions
```

**Set Conditions Body:**

```json
{
  "conditions": [
    {"type": "include", "name": "general"},
    {"type": "exclude", "name": "singular", "sub_name": "page"}
  ]
}
```

#### Assign Template to Location

```http
POST /theme-builder/templates/{id}/assign
```

**Body:**

```json
{
  "scope": "entire_site"
}
```

Scope options: `entire_site`, `singular`, `archive`, `specific`, `front_page`, `404`

---

## MCP Server Configuration

Site Pilot AI includes an MCP (Model Context Protocol) server for AI integration.

### Installation

```bash
# Clone or download the MCP server
cd mcp-server
npm install
```

### Configuration

Create `config.json`:

```json
{
  "sites": {
    "my-site": {
      "url": "https://example.com",
      "api_key": "spai_your_api_key_here"
    },
    "staging": {
      "url": "https://staging.example.com",
      "api_key": "spai_staging_key_here"
    }
  },
  "default_site": "my-site"
}
```

### Claude Desktop Configuration

Add to `~/.config/claude/claude_desktop_config.json`:

```json
{
  "mcpServers": {
    "site-pilot-ai": {
      "command": "node",
      "args": ["/path/to/mcp-server/index.js"],
      "env": {
        "WORDPRESS_URL": "https://example.com",
        "WORDPRESS_API_KEY": "spai_your_api_key_here"
      }
    }
  }
}
```

### Available MCP Tools

| Tool | Description |
|------|-------------|
| `wp_get_site_info` | Get WordPress site information |
| `wp_list_posts` | List posts with filtering |
| `wp_create_post` | Create a new post |
| `wp_update_post` | Update an existing post |
| `wp_delete_post` | Delete a post |
| `wp_list_pages` | List pages |
| `wp_create_page` | Create a new page |
| `wp_upload_media` | Upload media from URL |
| `wp_get_elementor` | Get Elementor page data |
| `wp_set_elementor` | Update Elementor page data |
| `wp_get_seo` | Get SEO metadata |
| `wp_set_seo` | Update SEO metadata |
| `wp_list_menus` | List navigation menus |
| `wp_update_settings` | Update site settings |

---

## AI Integration Examples

### Claude

```
Human: Create a new blog post about AI trends in 2024

Claude: I'll create that blog post for you using the Site Pilot AI API.

[Uses wp_create_post tool with title "AI Trends Shaping 2024" and content...]

Done! I've created the post. You can view it at https://example.com/ai-trends-2024/
```

### Python Example

```python
import requests

class SitePilotAI:
    def __init__(self, url, api_key):
        self.base_url = f"{url}/wp-json/site-pilot-ai/v1"
        self.headers = {"X-API-Key": api_key}

    def create_post(self, title, content, status="draft"):
        response = requests.post(
            f"{self.base_url}/posts",
            headers=self.headers,
            json={"title": title, "content": content, "status": status}
        )
        return response.json()

    def update_seo(self, post_id, title, description):
        response = requests.put(
            f"{self.base_url}/seo/{post_id}",
            headers=self.headers,
            json={"title": title, "description": description}
        )
        return response.json()

# Usage
wp = SitePilotAI("https://example.com", "spai_your_key")
post = wp.create_post("My Post", "<p>Content here</p>", "publish")
wp.update_seo(post["id"], "SEO Title", "Meta description")
```

### JavaScript/Node.js Example

```javascript
const axios = require('axios');

class SitePilotAI {
  constructor(url, apiKey) {
    this.client = axios.create({
      baseURL: `${url}/wp-json/site-pilot-ai/v1`,
      headers: { 'X-API-Key': apiKey }
    });
  }

  async createPost(title, content, status = 'draft') {
    const { data } = await this.client.post('/posts', {
      title, content, status
    });
    return data;
  }

  async uploadFromUrl(imageUrl, title) {
    const { data } = await this.client.post('/media/from-url', {
      url: imageUrl,
      title
    });
    return data;
  }
}

// Usage
const wp = new SitePilotAI('https://example.com', 'spai_your_key');

(async () => {
  const post = await wp.createPost('Hello World', '<p>Content</p>', 'publish');
  console.log(`Created post: ${post.link}`);
})();
```

### cURL Examples

```bash
# Get site info
curl -H "X-API-Key: spai_xxx" https://example.com/wp-json/site-pilot-ai/v1/site-info

# Create a post
curl -X POST -H "X-API-Key: spai_xxx" -H "Content-Type: application/json" \
  -d '{"title":"New Post","content":"<p>Hello</p>","status":"publish"}' \
  https://example.com/wp-json/site-pilot-ai/v1/posts

# Upload image from URL
curl -X POST -H "X-API-Key: spai_xxx" -H "Content-Type: application/json" \
  -d '{"url":"https://example.com/image.jpg","title":"My Image"}' \
  https://example.com/wp-json/site-pilot-ai/v1/media/from-url

# Update SEO
curl -X PUT -H "X-API-Key: spai_xxx" -H "Content-Type: application/json" \
  -d '{"title":"SEO Title","description":"Meta description"}' \
  https://example.com/wp-json/site-pilot-ai/v1/seo/123
```

---

## Support

- **Documentation:** https://labs.digid.ca/site-pilot-ai/docs
- **GitHub Issues:** https://github.com/Digidinc/wp-ai-operator/issues
- **Email:** support@digid.ca

---

*Site Pilot AI is developed by [DigID Inc](https://digid.ca)*
