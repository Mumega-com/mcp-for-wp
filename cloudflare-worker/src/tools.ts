/**
 * MCP Tool Registry for WordPress REST API
 *
 * Maps MCP tool names to WordPress REST API endpoints
 * Updated to match actual WordPress Site Pilot AI plugin routes
 * Total: 30 tools across 4 extensions
 */

export interface ToolDefinition {
  name: string;
  description: string;
  inputSchema: {
    type: "object";
    properties: Record<string, any>;
    required?: string[];
  };
}

export interface ToolMapping {
  method: string;
  endpoint: string; // Template with {id} placeholders
  bodyParams?: string[]; // Parameters that go in request body (vs URL params)
}

// Tool definitions for MCP clients
export const TOOLS: ToolDefinition[] = [
  // ============================================================================
  // CORE TOOLS (14)
  // ============================================================================
  {
    name: "wp_site_info",
    description: "Get WordPress site information including title, URL, description, and installed plugins/themes.",
    inputSchema: {
      type: "object",
      properties: {},
      required: [],
    },
  },
  {
    name: "wp_analytics",
    description: "Get site analytics including post count, page count, user count, and recent activity.",
    inputSchema: {
      type: "object",
      properties: {
        days: {
          type: "number",
          description: "Number of days to look back for analytics (default: 30)",
        },
      },
    },
  },
  {
    name: "wp_detect_plugins",
    description: "Detect which SEO and form plugins are installed and active on the site.",
    inputSchema: {
      type: "object",
      properties: {},
      required: [],
    },
  },
  {
    name: "wp_list_posts",
    description: "List blog posts with optional filtering by status, category, or search term.",
    inputSchema: {
      type: "object",
      properties: {
        status: {
          type: "string",
          description: "Filter by status: publish, draft, pending, private, or all",
        },
        per_page: {
          type: "number",
          description: "Number of posts per page (default: 10, max: 100)",
        },
        page: {
          type: "number",
          description: "Page number for pagination",
        },
        search: {
          type: "string",
          description: "Search term to filter posts",
        },
      },
    },
  },
  {
    name: "wp_create_post",
    description: "Create a new blog post with title, content, status, and optional featured image.",
    inputSchema: {
      type: "object",
      properties: {
        title: {
          type: "string",
          description: "Post title",
        },
        content: {
          type: "string",
          description: "Post content (HTML)",
        },
        status: {
          type: "string",
          description: "Post status: draft, publish, pending, private",
        },
        featured_image_id: {
          type: "number",
          description: "ID of featured image from media library",
        },
        categories: {
          type: "array",
          items: { type: "number" },
          description: "Array of category IDs",
        },
        tags: {
          type: "array",
          items: { type: "number" },
          description: "Array of tag IDs",
        },
      },
      required: ["title", "content"],
    },
  },
  {
    name: "wp_update_post",
    description: "Update an existing blog post by ID.",
    inputSchema: {
      type: "object",
      properties: {
        id: {
          type: "number",
          description: "Post ID",
        },
        title: {
          type: "string",
          description: "Post title",
        },
        content: {
          type: "string",
          description: "Post content (HTML)",
        },
        status: {
          type: "string",
          description: "Post status: draft, publish, pending, private",
        },
        featured_image_id: {
          type: "number",
          description: "ID of featured image",
        },
      },
      required: ["id"],
    },
  },
  {
    name: "wp_delete_post",
    description: "Delete a post by ID (moves to trash by default).",
    inputSchema: {
      type: "object",
      properties: {
        id: {
          type: "number",
          description: "Post ID to delete",
        },
        force: {
          type: "boolean",
          description: "Permanently delete instead of trash (default: false)",
        },
      },
      required: ["id"],
    },
  },
  {
    name: "wp_list_pages",
    description: "List WordPress pages with optional filtering.",
    inputSchema: {
      type: "object",
      properties: {
        status: {
          type: "string",
          description: "Filter by status: publish, draft, pending, private, or all",
        },
        per_page: {
          type: "number",
          description: "Number of pages per page (default: 10, max: 100)",
        },
        page: {
          type: "number",
          description: "Page number for pagination",
        },
      },
    },
  },
  {
    name: "wp_create_page",
    description: "Create a new WordPress page.",
    inputSchema: {
      type: "object",
      properties: {
        title: {
          type: "string",
          description: "Page title",
        },
        content: {
          type: "string",
          description: "Page content (HTML)",
        },
        status: {
          type: "string",
          description: "Page status: draft, publish, pending, private",
        },
        parent_id: {
          type: "number",
          description: "Parent page ID for hierarchical pages",
        },
      },
      required: ["title"],
    },
  },
  {
    name: "wp_update_page",
    description: "Update an existing WordPress page by ID.",
    inputSchema: {
      type: "object",
      properties: {
        id: {
          type: "number",
          description: "Page ID",
        },
        title: {
          type: "string",
          description: "Page title",
        },
        content: {
          type: "string",
          description: "Page content (HTML)",
        },
        status: {
          type: "string",
          description: "Page status: draft, publish, pending, private",
        },
      },
      required: ["id"],
    },
  },
  {
    name: "wp_upload_media",
    description: "Upload media file (image, video, document) to WordPress media library. Accepts base64 file data.",
    inputSchema: {
      type: "object",
      properties: {
        filename: {
          type: "string",
          description: "File name including extension",
        },
        file_data: {
          type: "string",
          description: "Base64-encoded file content",
        },
        title: {
          type: "string",
          description: "Media title",
        },
        alt: {
          type: "string",
          description: "Alt text for images",
        },
      },
      required: ["filename", "file_data"],
    },
  },
  {
    name: "wp_upload_media_from_url",
    description: "Upload media to WordPress by providing a URL to fetch from.",
    inputSchema: {
      type: "object",
      properties: {
        url: {
          type: "string",
          description: "URL of the media file to upload",
        },
        filename: {
          type: "string",
          description: "Optional filename override",
        },
        title: {
          type: "string",
          description: "Media title",
        },
        alt: {
          type: "string",
          description: "Alt text for images",
        },
      },
      required: ["url"],
    },
  },
  {
    name: "wp_list_drafts",
    description: "List all draft posts and pages on the site.",
    inputSchema: {
      type: "object",
      properties: {
        per_page: {
          type: "number",
          description: "Number of drafts per page (default: 20)",
        },
        page: {
          type: "number",
          description: "Page number for pagination",
        },
      },
    },
  },
  {
    name: "wp_delete_all_drafts",
    description: "Bulk delete all draft posts and pages (CAUTION: destructive operation).",
    inputSchema: {
      type: "object",
      properties: {
        confirm: {
          type: "boolean",
          description: "Must be true to confirm deletion",
        },
      },
      required: ["confirm"],
    },
  },

  // ============================================================================
  // SEO TOOLS (5)
  // ============================================================================
  {
    name: "wp_get_seo",
    description: "Get SEO metadata for a post or page (title, description, keywords, etc.).",
    inputSchema: {
      type: "object",
      properties: {
        id: {
          type: "number",
          description: "Post or page ID",
        },
      },
      required: ["id"],
    },
  },
  {
    name: "wp_set_seo",
    description: "Set SEO metadata for a post or page.",
    inputSchema: {
      type: "object",
      properties: {
        id: {
          type: "number",
          description: "Post or page ID",
        },
        title: {
          type: "string",
          description: "SEO title (meta title)",
        },
        description: {
          type: "string",
          description: "SEO meta description",
        },
        focus_keyword: {
          type: "string",
          description: "Primary focus keyword",
        },
        canonical: {
          type: "string",
          description: "Canonical URL",
        },
        og_title: {
          type: "string",
          description: "Open Graph title",
        },
        og_description: {
          type: "string",
          description: "Open Graph description",
        },
        og_image: {
          type: "string",
          description: "Open Graph image URL",
        },
        robots_noindex: {
          type: "boolean",
          description: "Prevent search engines from indexing",
        },
        robots_nofollow: {
          type: "boolean",
          description: "Prevent following links",
        },
      },
      required: ["id"],
    },
  },
  {
    name: "wp_analyze_seo",
    description: "Analyze SEO quality of a post/page and get recommendations.",
    inputSchema: {
      type: "object",
      properties: {
        id: {
          type: "number",
          description: "Post or page ID to analyze",
        },
      },
      required: ["id"],
    },
  },
  {
    name: "wp_bulk_seo",
    description: "Bulk update SEO metadata for multiple posts/pages.",
    inputSchema: {
      type: "object",
      properties: {
        updates: {
          type: "array",
          items: {
            type: "object",
            properties: {
              id: { type: "number" },
              title: { type: "string" },
              description: { type: "string" },
              focus_keyword: { type: "string" },
            },
            required: ["id"],
          },
          description: "Array of SEO updates",
        },
      },
      required: ["updates"],
    },
  },
  {
    name: "wp_get_seo_plugin",
    description: "Detect which SEO plugin is active (Yoast, Rank Math, All in One SEO, etc.).",
    inputSchema: {
      type: "object",
      properties: {},
      required: [],
    },
  },

  // ============================================================================
  // FORMS TOOLS (4)
  // ============================================================================
  {
    name: "wp_list_forms",
    description: "List all forms on the site (Contact Form 7, Gravity Forms, WPForms, Ninja Forms).",
    inputSchema: {
      type: "object",
      properties: {
        plugin: {
          type: "string",
          description: "Filter by specific plugin: cf7, wpforms, gravityforms, ninjaforms",
        },
      },
    },
  },
  {
    name: "wp_get_form",
    description: "Get details of a specific form by plugin and ID.",
    inputSchema: {
      type: "object",
      properties: {
        plugin: {
          type: "string",
          description: "Form plugin: cf7, wpforms, gravityforms, ninjaforms",
        },
        id: {
          type: "number",
          description: "Form ID",
        },
      },
      required: ["plugin", "id"],
    },
  },
  {
    name: "wp_get_form_submissions",
    description: "Get submissions/entries for a specific form.",
    inputSchema: {
      type: "object",
      properties: {
        plugin: {
          type: "string",
          description: "Form plugin: cf7, wpforms, gravityforms, ninjaforms",
        },
        id: {
          type: "number",
          description: "Form ID",
        },
        per_page: {
          type: "number",
          description: "Number of submissions per page",
        },
        page: {
          type: "number",
          description: "Page number for pagination",
        },
      },
      required: ["plugin", "id"],
    },
  },
  {
    name: "wp_get_form_plugin",
    description: "Detect which form plugin is active on the site.",
    inputSchema: {
      type: "object",
      properties: {},
      required: [],
    },
  },

  // ============================================================================
  // ELEMENTOR TOOLS (7)
  // ============================================================================
  {
    name: "wp_get_elementor",
    description: "Get Elementor data/structure for a page or post.",
    inputSchema: {
      type: "object",
      properties: {
        id: {
          type: "number",
          description: "Page or post ID",
        },
      },
      required: ["id"],
    },
  },
  {
    name: "wp_set_elementor",
    description: "Set/update Elementor data for a page (full structure replacement).",
    inputSchema: {
      type: "object",
      properties: {
        id: {
          type: "number",
          description: "Page or post ID",
        },
        elementor_data: {
          type: "string",
          description: "Elementor JSON structure",
        },
      },
      required: ["id", "elementor_data"],
    },
  },
  {
    name: "wp_list_elementor_templates",
    description: "List all Elementor templates (page templates, blocks, etc.).",
    inputSchema: {
      type: "object",
      properties: {
        type: {
          type: "string",
          description: "Template type: page, section, widget, or all",
        },
      },
    },
  },
  {
    name: "wp_apply_elementor_template",
    description: "Apply an Elementor template to a page.",
    inputSchema: {
      type: "object",
      properties: {
        template_id: {
          type: "number",
          description: "Template ID to apply",
        },
        page_id: {
          type: "number",
          description: "Target page ID",
        },
      },
      required: ["template_id", "page_id"],
    },
  },
  {
    name: "wp_create_landing_page",
    description: "Create a new Elementor landing page from scratch or template.",
    inputSchema: {
      type: "object",
      properties: {
        title: {
          type: "string",
          description: "Page title",
        },
        status: {
          type: "string",
          description: "Page status: draft, publish, pending",
        },
        template_id: {
          type: "number",
          description: "Optional template ID to start from",
        },
        sections: {
          type: "array",
          description: "Section configurations",
        },
        elementor_data: {
          type: "string",
          description: "Optional Elementor structure if not using template",
        },
      },
      required: ["title"],
    },
  },
  {
    name: "wp_get_elementor_globals",
    description: "Get Elementor global colors, fonts, and settings.",
    inputSchema: {
      type: "object",
      properties: {},
      required: [],
    },
  },
  {
    name: "wp_clone_elementor_page",
    description: "Clone an Elementor page to a new page.",
    inputSchema: {
      type: "object",
      properties: {
        source_id: {
          type: "number",
          description: "Source page ID to clone from",
        },
        title: {
          type: "string",
          description: "Title for the new cloned page",
        },
        status: {
          type: "string",
          description: "Status for new page: draft, publish, pending",
        },
      },
      required: ["source_id", "title"],
    },
  },
];

// Mapping from tool names to REST API endpoints
export const TOOL_MAP: Record<string, ToolMapping> = {
  // Core (14 tools)
  wp_site_info: { method: "GET", endpoint: "site-info" },
  wp_analytics: { method: "GET", endpoint: "analytics" },
  wp_detect_plugins: { method: "GET", endpoint: "plugins" },
  wp_list_posts: { method: "GET", endpoint: "posts" },
  wp_create_post: { method: "POST", endpoint: "posts", bodyParams: ["title", "content", "status", "featured_image_id", "categories", "tags"] },
  wp_update_post: { method: "PUT", endpoint: "posts/{id}", bodyParams: ["title", "content", "status", "featured_image_id"] },
  wp_delete_post: { method: "DELETE", endpoint: "posts/{id}" },
  wp_list_pages: { method: "GET", endpoint: "pages" },
  wp_create_page: { method: "POST", endpoint: "pages", bodyParams: ["title", "content", "status", "parent_id"] },
  wp_update_page: { method: "PUT", endpoint: "pages/{id}", bodyParams: ["title", "content", "status"] },
  wp_upload_media: { method: "POST", endpoint: "media", bodyParams: ["filename", "file_data", "title", "alt", "alt_text"] },
  wp_upload_media_from_url: { method: "POST", endpoint: "media/from-url", bodyParams: ["url", "filename", "title", "alt", "alt_text"] },
  wp_list_drafts: { method: "GET", endpoint: "drafts" },
  wp_delete_all_drafts: { method: "DELETE", endpoint: "drafts/delete-all" },

  // SEO (5 tools)
  wp_get_seo: { method: "GET", endpoint: "seo/{id}" },
  wp_set_seo: { method: "POST", endpoint: "seo/{id}", bodyParams: ["title", "description", "focus_keyword", "canonical", "og_title", "og_description", "og_image", "robots_noindex", "robots_nofollow"] },
  wp_analyze_seo: { method: "GET", endpoint: "seo/{id}/analyze" },
  wp_bulk_seo: { method: "POST", endpoint: "seo/bulk", bodyParams: ["updates"] },
  wp_get_seo_plugin: { method: "GET", endpoint: "seo/status" },

  // Forms (4 tools)
  wp_list_forms: { method: "GET", endpoint: "forms/{plugin}" }, // when plugin is provided, use forms/{plugin}, otherwise use forms
  wp_get_form: { method: "GET", endpoint: "forms/{plugin}/{id}" },
  wp_get_form_submissions: { method: "GET", endpoint: "forms/{plugin}/{id}/entries" },
  wp_get_form_plugin: { method: "GET", endpoint: "forms/status" },

  // Elementor (7 tools)
  wp_get_elementor: { method: "GET", endpoint: "elementor/{id}" },
  wp_set_elementor: { method: "POST", endpoint: "elementor/{id}", bodyParams: ["elementor_data"] },
  wp_list_elementor_templates: { method: "GET", endpoint: "elementor/templates" },
  wp_apply_elementor_template: { method: "POST", endpoint: "elementor/templates/{template_id}/apply", bodyParams: ["page_id"] },
  wp_create_landing_page: { method: "POST", endpoint: "elementor/landing-page", bodyParams: ["title", "status", "template_id", "sections", "elementor_data"] },
  wp_get_elementor_globals: { method: "GET", endpoint: "elementor/globals" },
  wp_clone_elementor_page: { method: "POST", endpoint: "elementor/clone", bodyParams: ["source_id", "title", "status"] },
};

// Tools that require Site Pilot AI Pro (license must be active on the WordPress site).
export const PRO_TOOL_NAMES = new Set<string>([
  // SEO
  "wp_get_seo",
  "wp_set_seo",
  "wp_analyze_seo",
  "wp_bulk_seo",
  "wp_get_seo_plugin",

  // Forms
  "wp_list_forms",
  "wp_get_form",
  "wp_create_form",
  "wp_update_form",
  "wp_delete_form",
  "wp_get_form_submissions",
  "wp_submit_form",
  "wp_get_form_plugin",

  // Elementor Pro
  "wp_list_elementor_templates",
  "wp_apply_elementor_template",
  "wp_create_landing_page",
  "wp_clone_elementor_page",
  "wp_get_elementor_globals",
  "wp_get_elementor_widgets",

  // WooCommerce
  "wp_list_products",
  "wp_get_product",
  "wp_create_product",
  "wp_update_product",
  "wp_delete_product",
  "wp_list_orders",
  "wp_get_order",
  "wp_update_order_status",
]);
