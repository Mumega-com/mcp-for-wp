# Site Pilot AI - API Documentation

> Control WordPress with AI through a powerful REST API

**Base URL:** `https://your-site.com/wp-json/site-pilot-ai/v1`
**Version:** 1.0.7

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
  - [Options](#options)
  - [Favicon](#favicon)
  - [Widgets](#widgets)
  - [Themes](#themes)
  - [Theme Builder](#theme-builder)
  - [WooCommerce (Pro)](#woocommerce-pro)
  - [Webhooks](#webhooks)
- [Rate Limiting](#rate-limiting)
- [Auto-Updates](#auto-updates)
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

Rate limiting protects your WordPress site from API abuse. Limits are configurable in settings.

### Default Limits

| Window | Requests |
|--------|----------|
| Per Minute | 60 |
| Per Hour | 1,000 |

### Rate Limit Headers

All API responses include rate limit headers:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1699574400
```

### Check Rate Limit Status

```http
GET /rate-limit
```

**Response:**

```json
{
  "enabled": true,
  "limits": {
    "per_minute": 60,
    "per_hour": 1000
  },
  "usage": {
    "identifier": "192.168.1.1",
    "minute": {
      "used": 15,
      "limit": 60,
      "remaining": 45,
      "reset": 1699574400
    },
    "hour": {
      "used": 150,
      "limit": 1000,
      "remaining": 850,
      "reset": 1699577400
    }
  }
}
```

### Rate Limit Exceeded Response

When rate limited, the API returns `429 Too Many Requests`:

```json
{
  "code": "rate_limit_exceeded",
  "message": "Rate limit exceeded. 60 requests per minute allowed. Try again in 45 seconds.",
  "data": {
    "status": 429,
    "retry_after": 45,
    "limit": 60,
    "remaining": 0,
    "reset": 1699574400
  }
}
```

### IP Whitelisting

Configure trusted IPs in WordPress settings to bypass rate limiting

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

#### Delete Page

```http
DELETE /pages/{id}
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `force` | boolean | false | Permanently delete (bypass trash) |

#### List Page Templates

```http
GET /templates/page
```

**Response:**

```json
{
  "templates": [
    {"slug": "default", "name": "Default Template"},
    {"slug": "templates/full-width.php", "name": "Full Width"},
    {"slug": "elementor_header_footer", "name": "Elementor Canvas"}
  ],
  "total": 3
}
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

#### Bulk Upload from URLs

```http
POST /media/bulk
```

Upload multiple images in a single request (max 20).

**Body (simple):**

```json
{
  "urls": [
    "https://example.com/image1.jpg",
    "https://example.com/image2.jpg",
    "https://example.com/image3.jpg"
  ]
}
```

**Body (with metadata):**

```json
{
  "items": [
    {"url": "https://example.com/hero.jpg", "title": "Hero Image", "alt": "Main banner"},
    {"url": "https://example.com/logo.png", "title": "Logo", "alt": "Company logo"}
  ]
}
```

**Response:**

```json
{
  "uploaded": 2,
  "failed": 0,
  "media": [
    {"id": 123, "url": "https://site.com/wp-content/uploads/hero.jpg", "title": "Hero Image"},
    {"id": 124, "url": "https://site.com/wp-content/uploads/logo.png", "title": "Logo"}
  ],
  "errors": []
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

### Options

*Requires Pro license*

#### Get Site Options

```http
GET /options
```

Returns reading and front page settings.

**Response:**

```json
{
  "show_on_front": "page",
  "page_on_front": 2,
  "page_for_posts": 10,
  "posts_per_page": 10,
  "posts_per_rss": 10,
  "blog_public": "1"
}
```

#### Update Site Options

```http
PUT /options
```

**Body:**

```json
{
  "show_on_front": "page",
  "page_on_front": 123,
  "page_for_posts": 456,
  "posts_per_page": 12
}
```

**Allowed Options:**

| Option | Description |
|--------|-------------|
| `show_on_front` | `posts` or `page` |
| `page_on_front` | Homepage page ID |
| `page_for_posts` | Blog page ID |
| `posts_per_page` | Posts per page (1-100) |

---

### Favicon

*Requires Pro license*

Manage site icon (favicon) displayed in browser tabs.

#### Get Favicon

```http
GET /favicon
```

**Response:**

```json
{
  "has_icon": true,
  "attachment_id": 123,
  "sizes": {
    "32": "https://example.com/wp-content/uploads/cropped-icon-32x32.png",
    "180": "https://example.com/wp-content/uploads/cropped-icon-180x180.png",
    "192": "https://example.com/wp-content/uploads/cropped-icon-192x192.png",
    "270": "https://example.com/wp-content/uploads/cropped-icon-270x270.png",
    "512": "https://example.com/wp-content/uploads/icon.png"
  }
}
```

#### Set Favicon

```http
PUT /favicon
```

**Body (by Media ID):**

```json
{
  "attachment_id": 123
}
```

**Body (by URL):**

```json
{
  "url": "https://example.com/favicon.png"
}
```

The URL method will automatically download and import the image.

#### Remove Favicon

```http
DELETE /favicon
```

Removes the site icon. Returns `{"success": true}`.

---

### Widgets

*Requires Pro license*

Manage WordPress widgets and sidebars.

#### List Sidebars

```http
GET /widgets/sidebars
```

**Response:**

```json
{
  "sidebars": [
    {
      "id": "sidebar-1",
      "name": "Main Sidebar",
      "description": "Add widgets here to appear in your sidebar",
      "widgets": ["text-2", "recent-posts-3"]
    },
    {
      "id": "footer-1",
      "name": "Footer Widget Area",
      "description": "Footer column 1",
      "widgets": []
    }
  ],
  "total": 4
}
```

#### List Widget Types

```http
GET /widgets/types
```

Returns all registered widget types.

#### List Widgets

```http
GET /widgets
```

Returns all active widget instances.

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `sidebar` | string | Filter by sidebar ID |

#### Get Widget

```http
GET /widgets/{id}
```

#### Create Widget

```http
POST /widgets
```

**Body:**

```json
{
  "type": "text",
  "sidebar": "sidebar-1",
  "settings": {
    "title": "Welcome",
    "text": "<p>Welcome to our site!</p>"
  }
}
```

#### Update Widget

```http
PUT /widgets/{id}
```

#### Delete Widget

```http
DELETE /widgets/{id}
```

#### Move Widget to Sidebar

```http
POST /widgets/{id}/move
```

**Body:**

```json
{
  "sidebar": "footer-1",
  "position": 0
}
```

---

### Themes

*Requires Pro license*

Unified theme settings management for popular WordPress themes.

#### Detect Active Theme

```http
GET /themes/detect
```

**Response:**

```json
{
  "active_theme": "astra",
  "theme_name": "Astra",
  "theme_version": "4.5.0",
  "is_supported": true,
  "supported_features": ["colors", "typography", "header", "footer"]
}
```

#### List Supported Themes

```http
GET /themes/supported
```

**Response:**

```json
{
  "themes": [
    {"slug": "astra", "name": "Astra", "features": ["colors", "typography", "header", "footer", "sidebar", "buttons"]},
    {"slug": "flavor flavor flavor flavor flavore flavor", "name": "GeneratePress", "features": ["colors", "typography", "layout"]},
    {"slug": "kadence", "name": "Flavor flavor flavor flavor flavore flavor", "features": ["colors", "typography", "header", "footer"]}
  ]
}
```

#### Get Theme Settings

```http
GET /themes/settings
```

Returns settings for the currently active theme in a normalized format.

**Response (Astra example):**

```json
{
  "theme": "astra",
  "colors": {
    "primary": "#0274be",
    "secondary": "#557799",
    "text": "#3a3a3a",
    "heading": "#3a3a3a",
    "background": "#ffffff",
    "link": "#0274be",
    "link_hover": "#3a3a3a"
  },
  "typography": {
    "body_font_family": "system-ui",
    "body_font_size": "16px",
    "heading_font_family": "inherit",
    "heading_font_weight": "600"
  },
  "header": {
    "type": "header-main-layout-1",
    "width": "content",
    "sticky": false
  },
  "footer": {
    "widgets_enabled": true,
    "copyright": "Copyright © 2024"
  }
}
```

#### Update Theme Settings

```http
PUT /themes/settings
```

**Body:**

```json
{
  "colors": {
    "primary": "#ff6b35"
  },
  "typography": {
    "body_font_size": "18px"
  }
}
```

#### Astra-Specific Endpoints

```http
GET /themes/astra/colors
PUT /themes/astra/colors

GET /themes/astra/typography
PUT /themes/astra/typography

GET /themes/astra/header
PUT /themes/astra/header

GET /themes/astra/footer
PUT /themes/astra/footer
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

### WooCommerce (Pro)

Full WooCommerce integration for AI-powered e-commerce management.

> **Pro Feature:** Requires Site Pilot AI Pro with valid license.

#### WooCommerce Status

```http
GET /woocommerce/status
```

**Response:**

```json
{
  "active": true,
  "version": "8.5.1",
  "currency": "USD",
  "currency_symbol": "$",
  "weight_unit": "lbs",
  "dimension_unit": "in",
  "tax_enabled": true,
  "coupons_enabled": true,
  "products_count": 156,
  "orders_count": 1243
}
```

#### Products

##### List Products

```http
GET /woocommerce/products
```

**Query Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `per_page` | integer | 50 | Items per page (1-100) |
| `page` | integer | 1 | Page number |
| `status` | string | publish | Product status (publish, draft, pending, private, any) |
| `type` | string | - | Product type (simple, variable, grouped, external) |
| `category` | string | - | Category slug |
| `tag` | string | - | Tag slug |
| `search` | string | - | Search term |
| `sku` | string | - | Exact SKU match |
| `stock_status` | string | - | Stock status (instock, outofstock, onbackorder) |
| `orderby` | string | date | Order by (date, title, price, popularity, rating) |
| `order` | string | DESC | Sort order (ASC, DESC) |

**Response:**

```json
{
  "products": [
    {
      "id": 42,
      "name": "Premium Widget",
      "slug": "premium-widget",
      "type": "simple",
      "status": "publish",
      "sku": "WIDGET-001",
      "price": "29.99",
      "regular_price": "39.99",
      "sale_price": "29.99",
      "on_sale": true,
      "stock_status": "instock",
      "stock_quantity": 150,
      "manage_stock": true,
      "categories": ["Electronics", "Widgets"],
      "tags": ["bestseller", "featured"],
      "permalink": "https://example.com/product/premium-widget",
      "date_created": "2024-01-15T10:30:00+00:00",
      "date_modified": "2024-02-01T14:22:00+00:00"
    }
  ],
  "total": 156,
  "page": 1,
  "per_page": 50,
  "total_pages": 4
}
```

##### Get Single Product

```http
GET /woocommerce/products/{id}
```

Returns detailed product information including description, dimensions, images, and attributes.

##### Create Product

```http
POST /woocommerce/products
```

**Body:**

```json
{
  "name": "New Product",
  "type": "simple",
  "status": "publish",
  "description": "Full product description with HTML support",
  "short_description": "Brief product summary",
  "sku": "NEWPROD-001",
  "regular_price": "49.99",
  "sale_price": "39.99",
  "manage_stock": true,
  "stock_quantity": 100,
  "stock_status": "instock",
  "categories": ["Electronics"],
  "tags": ["new", "featured"],
  "image_id": 123,
  "gallery_image_ids": [124, 125, 126],
  "virtual": false,
  "downloadable": false
}
```

##### Update Product

```http
PUT /woocommerce/products/{id}
```

Send only the fields you want to update.

##### Delete Product

```http
DELETE /woocommerce/products/{id}
```

**Query Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `force` | boolean | false | Permanently delete (bypass trash) |

##### Get Product Categories

```http
GET /woocommerce/products/categories
```

**Response:**

```json
[
  {
    "id": 15,
    "name": "Electronics",
    "slug": "electronics",
    "parent": 0,
    "count": 45
  }
]
```

##### Get Product Tags

```http
GET /woocommerce/products/tags
```

#### Orders

##### List Orders

```http
GET /woocommerce/orders
```

**Query Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `per_page` | integer | 50 | Items per page (1-100) |
| `page` | integer | 1 | Page number |
| `status` | string | any | Order status (pending, processing, completed, etc.) |
| `customer` | integer | - | Customer ID |
| `after` | string | - | Orders after date (ISO 8601) |
| `before` | string | - | Orders before date (ISO 8601) |

**Response:**

```json
{
  "orders": [
    {
      "id": 1001,
      "number": "1001",
      "status": "processing",
      "currency": "USD",
      "total": "129.97",
      "subtotal": "119.97",
      "tax_total": "10.00",
      "shipping_total": "0.00",
      "discount_total": "0.00",
      "payment_method": "Credit Card (Stripe)",
      "customer_id": 42,
      "date_created": "2024-02-01T09:15:00+00:00",
      "date_completed": null,
      "items_count": 3
    }
  ],
  "total": 1243,
  "page": 1,
  "per_page": 50,
  "total_pages": 25
}
```

##### Get Single Order

```http
GET /woocommerce/orders/{id}
```

Returns full order details including billing/shipping addresses, line items, and order notes.

##### Update Order

```http
PUT /woocommerce/orders/{id}
```

**Body:**

```json
{
  "status": "completed",
  "note": "Order shipped via FedEx, tracking: 123456789",
  "note_customer": true
}
```

##### Get Order Statuses

```http
GET /woocommerce/orders/statuses
```

**Response:**

```json
[
  {"slug": "pending", "name": "Pending payment"},
  {"slug": "processing", "name": "Processing"},
  {"slug": "on-hold", "name": "On hold"},
  {"slug": "completed", "name": "Completed"},
  {"slug": "cancelled", "name": "Cancelled"},
  {"slug": "refunded", "name": "Refunded"},
  {"slug": "failed", "name": "Failed"}
]
```

#### Customers

##### List Customers

```http
GET /woocommerce/customers
```

**Query Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `per_page` | integer | 50 | Items per page (1-100) |
| `page` | integer | 1 | Page number |
| `search` | string | - | Search term |
| `orderby` | string | registered | Order by (registered, display_name, user_login, user_email) |
| `order` | string | DESC | Sort order (ASC, DESC) |

**Response:**

```json
{
  "customers": [
    {
      "id": 42,
      "email": "customer@example.com",
      "first_name": "John",
      "last_name": "Doe",
      "display_name": "John Doe",
      "date_created": "2023-06-15T10:30:00+00:00",
      "orders_count": 12,
      "total_spent": "1,245.67"
    }
  ],
  "total": 523,
  "page": 1,
  "per_page": 50,
  "total_pages": 11
}
```

##### Get Single Customer

```http
GET /woocommerce/customers/{id}
```

Returns full customer details including billing and shipping addresses.

#### Analytics

```http
GET /woocommerce/analytics
```

**Query Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `period` | string | month | Time period (day, week, month, year) |
| `date_min` | string | - | Start date (ISO 8601) |
| `date_max` | string | - | End date (ISO 8601) |

**Response:**

```json
{
  "period": "month",
  "date_range": {
    "start": "2024-01-01T00:00:00",
    "end": "2024-01-31T23:59:59"
  },
  "sales": {
    "total": "15,678.90",
    "count": 156,
    "average": "100.51"
  },
  "products": {
    "total": 156,
    "in_stock": 142,
    "out_of_stock": 14
  },
  "top_products": [
    {
      "id": 42,
      "name": "Premium Widget",
      "sku": "WIDGET-001",
      "quantity": 89,
      "price": "29.99"
    }
  ],
  "customers": {
    "total": 523,
    "new": 45
  },
  "orders_by_status": {
    "pending": 12,
    "processing": 34,
    "on-hold": 5,
    "completed": 1156,
    "cancelled": 23,
    "refunded": 8,
    "failed": 5
  }
}
```

---

### Webhooks

Webhooks allow your external systems to receive real-time notifications when events occur on your WordPress site.

#### List Available Events

```http
GET /webhooks/events
```

**Response:**

```json
{
  "events": [
    "post.created", "post.updated", "post.deleted", "post.published",
    "page.created", "page.updated", "page.deleted", "page.published",
    "media.uploaded", "media.deleted",
    "user.created", "user.updated", "user.deleted",
    "comment.created", "comment.approved", "comment.deleted"
  ],
  "grouped": {
    "post": ["post.created", "post.updated", "post.deleted", "post.published"],
    "page": ["page.created", "page.updated", "page.deleted", "page.published"],
    "media": ["media.uploaded", "media.deleted"],
    "user": ["user.created", "user.updated", "user.deleted"],
    "comment": ["comment.created", "comment.approved", "comment.deleted"]
  },
  "total": 16
}
```

#### List Webhooks

```http
GET /webhooks
```

**Parameters:**

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `status` | string | all | Filter: `active`, `disabled`, `all` |
| `per_page` | integer | 50 | Items per page |
| `page` | integer | 1 | Page number |

#### Create Webhook

```http
POST /webhooks
```

**Body:**

```json
{
  "name": "My Webhook",
  "url": "https://example.com/webhook-receiver",
  "events": ["post.published", "page.published"],
  "secret": "optional-custom-secret"
}
```

**Response:**

```json
{
  "id": 1,
  "webhook": {
    "id": 1,
    "name": "My Webhook",
    "url": "https://example.com/webhook-receiver",
    "events": ["post.published", "page.published"],
    "status": "active",
    "secret": "abc123..."
  },
  "message": "Webhook created successfully."
}
```

#### Get/Update/Delete Webhook

```http
GET /webhooks/{id}
PUT /webhooks/{id}
DELETE /webhooks/{id}
```

#### Test Webhook

```http
POST /webhooks/{id}/test
```

Sends a test payload to verify the webhook URL is reachable.

**Response:**

```json
{
  "success": true,
  "response_code": 200,
  "response_body": "OK",
  "duration": 0.245
}
```

#### View Delivery Logs

```http
GET /webhooks/{id}/logs
```

**Response:**

```json
{
  "logs": [
    {
      "id": 1,
      "webhook_id": 1,
      "event": "post.published",
      "response_code": 200,
      "duration": 0.312,
      "created_at": "2024-01-15 10:30:00"
    }
  ],
  "total": 15,
  "pages": 1,
  "page": 1
}
```

#### Webhook Payload Format

When an event triggers, Site Pilot AI sends a POST request with:

**Headers:**

```
Content-Type: application/json
X-SPAI-Event: post.published
X-SPAI-Signature: sha256-hmac-of-body
X-SPAI-Webhook-ID: 1
X-SPAI-Delivery-ID: uuid
```

**Body:**

```json
{
  "event": "post.published",
  "timestamp": "2024-01-15T10:30:00+00:00",
  "site_url": "https://example.com",
  "id": 123,
  "title": "New Blog Post",
  "type": "post",
  "permalink": "https://example.com/new-blog-post/"
}
```

#### Verifying Webhook Signatures

Verify the `X-SPAI-Signature` header using HMAC-SHA256:

```php
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_SPAI_SIGNATURE'];
$expected = hash_hmac('sha256', $payload, $your_webhook_secret);

if (hash_equals($expected, $signature)) {
    // Valid webhook
}
```

---

## Auto-Updates

Site Pilot AI supports automatic updates directly from GitHub releases, without requiring WordPress.org hosting.

### How It Works

1. The plugin periodically checks the [GitHub releases page](https://github.com/Digidinc/wp-ai-operator/releases)
2. If a newer version is found, WordPress displays an update notice
3. Clicking "Update" downloads and installs the new version automatically

### Update Process

- **Check frequency:** Every 6 hours (cached)
- **Source:** GitHub Releases API
- **Both plugins:** Site Pilot AI (free) and Site Pilot AI Pro check independently

### Manual Update Check

To force an update check:

1. Go to **Dashboard → Updates**
2. Click **Check Again**

Or clear the update transient:

```php
// Clear update cache (run in theme functions.php or plugin)
delete_transient( 'spai_github_' . md5( 'site-pilot-ai' ) );
delete_transient( 'spai_github_' . md5( 'site-pilot-ai-pro' ) );
```

### Version Numbering

We follow semantic versioning: `MAJOR.MINOR.PATCH`

- **MAJOR:** Breaking API changes
- **MINOR:** New features (backwards compatible)
- **PATCH:** Bug fixes

### Release Assets

Each GitHub release includes:

| Asset | Description |
|-------|-------------|
| `site-pilot-ai.zip` | Free plugin (core features) |
| `site-pilot-ai-pro.zip` | Pro add-on (requires free plugin) |

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
