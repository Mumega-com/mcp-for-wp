#!/usr/bin/env node
/**
 * WP AI Operator - MCP Server for WordPress
 * Control your WordPress site with AI (Claude Code / Claude Desktop)
 */

import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
  ListResourcesRequestSchema,
  ReadResourceRequestSchema,
} from "@modelcontextprotocol/sdk/types.js";
import fetch from "node-fetch";
import FormData from "form-data";
import * as fs from "fs";
import * as path from "path";

// Configuration from environment
const WP_URL = process.env.WP_URL || "";
const WP_API_KEY = process.env.WP_API_KEY || "";

interface WPResponse {
  success?: boolean;
  error?: string;
  [key: string]: any;
}

// WordPress API Client
class WordPressClient {
  private baseUrl: string;
  private apiKey: string;

  constructor(url: string, apiKey: string) {
    this.baseUrl = url.replace(/\/$/, "");
    this.apiKey = apiKey;
  }

  private async request(
    method: string,
    endpoint: string,
    data?: any,
    isFormData?: boolean
  ): Promise<WPResponse> {
    const url = `${this.baseUrl}/wp-json/digid/v1/${endpoint}`;
    const headers: Record<string, string> = {
      "X-API-Key": this.apiKey,
    };

    let body: any;
    if (isFormData) {
      body = data;
    } else if (data) {
      headers["Content-Type"] = "application/json";
      body = JSON.stringify(data);
    }

    const response = await fetch(url, {
      method,
      headers,
      body,
    });

    return response.json() as Promise<WPResponse>;
  }

  // Site Info
  async getSiteInfo(): Promise<WPResponse> {
    return this.request("GET", "site-info");
  }

  async getAnalytics(days: number = 30): Promise<WPResponse> {
    return this.request("GET", `analytics?days=${days}`);
  }

  // Posts
  async getPosts(perPage: number = 10, page: number = 1): Promise<WPResponse> {
    return this.request("GET", `posts?per_page=${perPage}&page=${page}`);
  }

  async createPost(
    title: string,
    content: string,
    status: string = "draft",
    options?: {
      excerpt?: string;
      categories?: number[];
      tags?: string[];
      featured_image?: number;
    }
  ): Promise<WPResponse> {
    return this.request("POST", "posts", {
      title,
      content,
      status,
      ...options,
    });
  }

  async updatePost(
    id: number,
    data: { title?: string; content?: string; status?: string; excerpt?: string }
  ): Promise<WPResponse> {
    return this.request("PUT", `posts/${id}`, data);
  }

  async deletePost(id: number, force: boolean = false): Promise<WPResponse> {
    return this.request("DELETE", `posts/${id}?force=${force}`);
  }

  // Pages
  async getPages(perPage: number = 10): Promise<WPResponse> {
    return this.request("GET", `pages?per_page=${perPage}`);
  }

  async createPage(
    title: string,
    content: string = "",
    status: string = "draft",
    options?: {
      template?: string;
      elementor_data?: string;
    }
  ): Promise<WPResponse> {
    return this.request("POST", "pages", {
      title,
      content,
      status,
      ...options,
    });
  }

  async updatePage(
    id: number,
    data: {
      title?: string;
      content?: string;
      status?: string;
      elementor_data?: string;
    }
  ): Promise<WPResponse> {
    return this.request("PUT", `pages/${id}`, data);
  }

  // Elementor
  async getElementor(pageId: number): Promise<WPResponse> {
    return this.request("GET", `elementor/${pageId}`);
  }

  async updateElementor(
    pageId: number,
    elementorData: string
  ): Promise<WPResponse> {
    return this.request("POST", `elementor/${pageId}`, {
      elementor_data: elementorData,
    });
  }

  // Media
  async uploadMedia(filePath: string): Promise<WPResponse> {
    const form = new FormData();
    form.append("file", fs.createReadStream(filePath));

    return this.request("POST", "media", form, true);
  }

  async uploadMediaFromUrl(imageUrl: string, filename: string): Promise<WPResponse> {
    // Download image first
    const response = await fetch(imageUrl);
    const buffer = await response.buffer();

    const tempPath = `/tmp/${filename}`;
    fs.writeFileSync(tempPath, buffer);

    const result = await this.uploadMedia(tempPath);
    fs.unlinkSync(tempPath);

    return result;
  }

  // SEO
  async getSeo(postId: number): Promise<WPResponse> {
    return this.request("GET", `seo/${postId}`);
  }

  async updateSeo(
    postId: number,
    data: {
      seo_title?: string;
      seo_description?: string;
      focus_keyword?: string;
    }
  ): Promise<WPResponse> {
    return this.request("POST", `seo/${postId}`, data);
  }

  // Drafts
  async getDrafts(): Promise<WPResponse> {
    return this.request("GET", "drafts");
  }

  async deleteAllDrafts(): Promise<WPResponse> {
    return this.request("DELETE", "drafts/delete-all");
  }
}

// Initialize server
const server = new Server(
  {
    name: "wp-ai-operator",
    version: "1.0.0",
  },
  {
    capabilities: {
      tools: {},
      resources: {},
    },
  }
);

// WordPress client instance
let wp: WordPressClient | null = null;

function getClient(): WordPressClient {
  if (!wp) {
    if (!WP_URL || !WP_API_KEY) {
      throw new Error(
        "WordPress credentials not configured. Set WP_URL and WP_API_KEY environment variables."
      );
    }
    wp = new WordPressClient(WP_URL, WP_API_KEY);
  }
  return wp;
}

// Define available tools
server.setRequestHandler(ListToolsRequestSchema, async () => {
  return {
    tools: [
      // Site Info
      {
        name: "wp_site_info",
        description: "Get WordPress site information (name, URL, theme, post/page counts, plugin version)",
        inputSchema: {
          type: "object",
          properties: {},
        },
      },
      {
        name: "wp_analytics",
        description: "Get site analytics and activity logs",
        inputSchema: {
          type: "object",
          properties: {
            days: {
              type: "number",
              description: "Number of days to analyze (default: 30)",
            },
          },
        },
      },

      // Posts
      {
        name: "wp_list_posts",
        description: "List published posts from the WordPress site",
        inputSchema: {
          type: "object",
          properties: {
            per_page: { type: "number", description: "Posts per page (default: 10)" },
            page: { type: "number", description: "Page number (default: 1)" },
          },
        },
      },
      {
        name: "wp_create_post",
        description: "Create a new blog post",
        inputSchema: {
          type: "object",
          properties: {
            title: { type: "string", description: "Post title" },
            content: { type: "string", description: "Post content (HTML)" },
            status: {
              type: "string",
              enum: ["draft", "publish", "private"],
              description: "Post status (default: draft)",
            },
            excerpt: { type: "string", description: "Post excerpt/summary" },
          },
          required: ["title", "content"],
        },
      },
      {
        name: "wp_update_post",
        description: "Update an existing post",
        inputSchema: {
          type: "object",
          properties: {
            id: { type: "number", description: "Post ID" },
            title: { type: "string", description: "New title" },
            content: { type: "string", description: "New content (HTML)" },
            status: { type: "string", enum: ["draft", "publish", "private"] },
          },
          required: ["id"],
        },
      },
      {
        name: "wp_delete_post",
        description: "Delete a post (move to trash or permanently delete)",
        inputSchema: {
          type: "object",
          properties: {
            id: { type: "number", description: "Post ID" },
            force: { type: "boolean", description: "Permanently delete (default: false)" },
          },
          required: ["id"],
        },
      },

      // Pages
      {
        name: "wp_list_pages",
        description: "List all pages from the WordPress site",
        inputSchema: {
          type: "object",
          properties: {
            per_page: { type: "number", description: "Pages per page (default: 10)" },
          },
        },
      },
      {
        name: "wp_create_page",
        description: "Create a new page (supports Elementor)",
        inputSchema: {
          type: "object",
          properties: {
            title: { type: "string", description: "Page title" },
            content: { type: "string", description: "Page content (HTML, or empty for Elementor)" },
            status: {
              type: "string",
              enum: ["draft", "publish", "private"],
              description: "Page status (default: draft)",
            },
            template: { type: "string", description: "Page template slug" },
            elementor_data: {
              type: "string",
              description: "Elementor JSON data for page layout",
            },
          },
          required: ["title"],
        },
      },
      {
        name: "wp_update_page",
        description: "Update an existing page",
        inputSchema: {
          type: "object",
          properties: {
            id: { type: "number", description: "Page ID" },
            title: { type: "string", description: "New title" },
            content: { type: "string", description: "New content" },
            status: { type: "string", enum: ["draft", "publish", "private"] },
          },
          required: ["id"],
        },
      },

      // Elementor
      {
        name: "wp_get_elementor",
        description: "Get Elementor page builder data for a page",
        inputSchema: {
          type: "object",
          properties: {
            page_id: { type: "number", description: "Page ID" },
          },
          required: ["page_id"],
        },
      },
      {
        name: "wp_set_elementor",
        description: "Set/update Elementor page builder data",
        inputSchema: {
          type: "object",
          properties: {
            page_id: { type: "number", description: "Page ID" },
            elementor_data: {
              type: "string",
              description: "Elementor JSON data (sections, columns, widgets)",
            },
          },
          required: ["page_id", "elementor_data"],
        },
      },

      // Media
      {
        name: "wp_upload_media",
        description: "Upload an image or file to WordPress media library",
        inputSchema: {
          type: "object",
          properties: {
            file_path: {
              type: "string",
              description: "Local file path to upload",
            },
          },
          required: ["file_path"],
        },
      },
      {
        name: "wp_upload_media_from_url",
        description: "Download and upload an image from URL to WordPress",
        inputSchema: {
          type: "object",
          properties: {
            url: { type: "string", description: "Image URL to download" },
            filename: { type: "string", description: "Filename to save as" },
          },
          required: ["url", "filename"],
        },
      },

      // SEO
      {
        name: "wp_get_seo",
        description: "Get SEO metadata for a post or page",
        inputSchema: {
          type: "object",
          properties: {
            post_id: { type: "number", description: "Post or page ID" },
          },
          required: ["post_id"],
        },
      },
      {
        name: "wp_set_seo",
        description: "Set SEO metadata (title, description, focus keyword)",
        inputSchema: {
          type: "object",
          properties: {
            post_id: { type: "number", description: "Post or page ID" },
            seo_title: { type: "string", description: "SEO title tag" },
            seo_description: { type: "string", description: "Meta description" },
            focus_keyword: { type: "string", description: "Focus keyword" },
          },
          required: ["post_id"],
        },
      },

      // Drafts
      {
        name: "wp_list_drafts",
        description: "List all draft posts",
        inputSchema: {
          type: "object",
          properties: {},
        },
      },
      {
        name: "wp_delete_all_drafts",
        description: "Delete all draft posts (bulk cleanup)",
        inputSchema: {
          type: "object",
          properties: {},
        },
      },

      // Landing Page Builder (High-level)
      {
        name: "wp_create_landing_page",
        description:
          "Create a complete landing page with hero section, features, and CTA. Provide content and I'll build the Elementor layout.",
        inputSchema: {
          type: "object",
          properties: {
            title: { type: "string", description: "Page title" },
            headline: { type: "string", description: "Hero headline" },
            subheadline: { type: "string", description: "Hero subheadline" },
            cta_text: { type: "string", description: "Call-to-action button text" },
            cta_url: { type: "string", description: "CTA button link" },
            features: {
              type: "array",
              items: {
                type: "object",
                properties: {
                  title: { type: "string" },
                  description: { type: "string" },
                  icon: { type: "string", description: "FontAwesome icon (e.g., 'fas fa-star')" },
                },
              },
              description: "Array of feature cards (max 4)",
            },
            hero_image_id: {
              type: "number",
              description: "Media ID for hero background image",
            },
          },
          required: ["title", "headline", "cta_text", "cta_url"],
        },
      },
    ],
  };
});

// Handle tool calls
server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name, arguments: args } = request.params;
  const client = getClient();

  try {
    let result: any;

    switch (name) {
      // Site Info
      case "wp_site_info":
        result = await client.getSiteInfo();
        break;
      case "wp_analytics":
        result = await client.getAnalytics(args?.days || 30);
        break;

      // Posts
      case "wp_list_posts":
        result = await client.getPosts(args?.per_page || 10, args?.page || 1);
        break;
      case "wp_create_post":
        result = await client.createPost(
          args.title,
          args.content,
          args.status || "draft",
          { excerpt: args.excerpt }
        );
        break;
      case "wp_update_post":
        result = await client.updatePost(args.id, {
          title: args.title,
          content: args.content,
          status: args.status,
        });
        break;
      case "wp_delete_post":
        result = await client.deletePost(args.id, args.force || false);
        break;

      // Pages
      case "wp_list_pages":
        result = await client.getPages(args?.per_page || 10);
        break;
      case "wp_create_page":
        result = await client.createPage(
          args.title,
          args.content || "",
          args.status || "draft",
          {
            template: args.template,
            elementor_data: args.elementor_data,
          }
        );
        break;
      case "wp_update_page":
        result = await client.updatePage(args.id, {
          title: args.title,
          content: args.content,
          status: args.status,
        });
        break;

      // Elementor
      case "wp_get_elementor":
        result = await client.getElementor(args.page_id);
        break;
      case "wp_set_elementor":
        result = await client.updateElementor(args.page_id, args.elementor_data);
        break;

      // Media
      case "wp_upload_media":
        result = await client.uploadMedia(args.file_path);
        break;
      case "wp_upload_media_from_url":
        result = await client.uploadMediaFromUrl(args.url, args.filename);
        break;

      // SEO
      case "wp_get_seo":
        result = await client.getSeo(args.post_id);
        break;
      case "wp_set_seo":
        result = await client.updateSeo(args.post_id, {
          seo_title: args.seo_title,
          seo_description: args.seo_description,
          focus_keyword: args.focus_keyword,
        });
        break;

      // Drafts
      case "wp_list_drafts":
        result = await client.getDrafts();
        break;
      case "wp_delete_all_drafts":
        result = await client.deleteAllDrafts();
        break;

      // Landing Page Builder
      case "wp_create_landing_page":
        result = await createLandingPage(client, args);
        break;

      default:
        throw new Error(`Unknown tool: ${name}`);
    }

    return {
      content: [
        {
          type: "text",
          text: JSON.stringify(result, null, 2),
        },
      ],
    };
  } catch (error: any) {
    return {
      content: [
        {
          type: "text",
          text: `Error: ${error.message}`,
        },
      ],
      isError: true,
    };
  }
});

// High-level landing page builder
async function createLandingPage(
  client: WordPressClient,
  args: {
    title: string;
    headline: string;
    subheadline?: string;
    cta_text: string;
    cta_url: string;
    features?: Array<{ title: string; description: string; icon?: string }>;
    hero_image_id?: number;
  }
): Promise<any> {
  // Build Elementor JSON structure
  const elementorData = buildLandingPageElementor(args);

  // Create the page
  const page = await client.createPage(args.title, "", "draft", {
    template: "elementor_header_footer",
    elementor_data: JSON.stringify(elementorData),
  });

  if (!page.success) {
    return page;
  }

  // Set default SEO
  await client.updateSeo(page.page_id, {
    seo_title: `${args.headline} | ${args.title}`,
    seo_description: args.subheadline || args.headline,
  });

  return {
    success: true,
    page_id: page.page_id,
    url: page.url,
    edit_url: page.edit_url,
    message: "Landing page created! Edit in Elementor to customize further.",
  };
}

function buildLandingPageElementor(args: {
  headline: string;
  subheadline?: string;
  cta_text: string;
  cta_url: string;
  features?: Array<{ title: string; description: string; icon?: string }>;
  hero_image_id?: number;
}): any[] {
  const sections: any[] = [];

  // Hero Section
  sections.push({
    id: `hero-${Date.now()}`,
    elType: "section",
    settings: {
      layout: "full_width",
      height: "min-height",
      custom_height: { unit: "vh", size: 80 },
      background_background: "classic",
      background_color: "#1a1a2e",
    },
    elements: [
      {
        id: `col-${Date.now()}`,
        elType: "column",
        settings: {
          content_position: "center",
          _column_size: 100,
        },
        elements: [
          {
            id: `headline-${Date.now()}`,
            elType: "widget",
            widgetType: "heading",
            settings: {
              title: args.headline,
              header_size: "h1",
              align: "center",
              title_color: "#ffffff",
              typography_typography: "custom",
              typography_font_size: { unit: "px", size: 56 },
            },
          },
          ...(args.subheadline
            ? [
                {
                  id: `subheadline-${Date.now()}`,
                  elType: "widget",
                  widgetType: "heading",
                  settings: {
                    title: args.subheadline,
                    header_size: "h2",
                    align: "center",
                    title_color: "#cccccc",
                    typography_font_size: { unit: "px", size: 24 },
                  },
                },
              ]
            : []),
          {
            id: `cta-${Date.now()}`,
            elType: "widget",
            widgetType: "button",
            settings: {
              text: args.cta_text,
              link: { url: args.cta_url },
              align: "center",
              background_color: "#e94560",
              button_text_color: "#ffffff",
              border_radius: { unit: "px", size: 30 },
            },
          },
        ],
      },
    ],
  });

  // Features Section (if provided)
  if (args.features && args.features.length > 0) {
    const featureColumns = args.features.slice(0, 4).map((feature, i) => ({
      id: `feature-col-${Date.now()}-${i}`,
      elType: "column",
      settings: { _column_size: Math.floor(100 / args.features!.length) },
      elements: [
        {
          id: `feature-icon-${Date.now()}-${i}`,
          elType: "widget",
          widgetType: "icon",
          settings: {
            selected_icon: {
              value: feature.icon || "fas fa-star",
              library: "fa-solid",
            },
            align: "center",
            primary_color: "#e94560",
          },
        },
        {
          id: `feature-title-${Date.now()}-${i}`,
          elType: "widget",
          widgetType: "heading",
          settings: {
            title: feature.title,
            header_size: "h3",
            align: "center",
          },
        },
        {
          id: `feature-desc-${Date.now()}-${i}`,
          elType: "widget",
          widgetType: "text-editor",
          settings: {
            editor: feature.description,
            align: "center",
          },
        },
      ],
    }));

    sections.push({
      id: `features-${Date.now()}`,
      elType: "section",
      settings: {
        layout: "boxed",
        padding: { unit: "px", top: 80, bottom: 80 },
      },
      elements: featureColumns,
    });
  }

  // Final CTA Section
  sections.push({
    id: `final-cta-${Date.now()}`,
    elType: "section",
    settings: {
      layout: "full_width",
      background_background: "classic",
      background_color: "#16213e",
      padding: { unit: "px", top: 60, bottom: 60 },
    },
    elements: [
      {
        id: `final-col-${Date.now()}`,
        elType: "column",
        settings: { _column_size: 100 },
        elements: [
          {
            id: `final-headline-${Date.now()}`,
            elType: "widget",
            widgetType: "heading",
            settings: {
              title: "Ready to Get Started?",
              header_size: "h2",
              align: "center",
              title_color: "#ffffff",
            },
          },
          {
            id: `final-button-${Date.now()}`,
            elType: "widget",
            widgetType: "button",
            settings: {
              text: args.cta_text,
              link: { url: args.cta_url },
              align: "center",
              background_color: "#e94560",
            },
          },
        ],
      },
    ],
  });

  return sections;
}

// Resources (for site info)
server.setRequestHandler(ListResourcesRequestSchema, async () => {
  return {
    resources: [
      {
        uri: "wordpress://site-info",
        name: "WordPress Site Info",
        description: "Current site configuration and stats",
        mimeType: "application/json",
      },
    ],
  };
});

server.setRequestHandler(ReadResourceRequestSchema, async (request) => {
  const { uri } = request.params;

  if (uri === "wordpress://site-info") {
    const client = getClient();
    const info = await client.getSiteInfo();
    return {
      contents: [
        {
          uri,
          mimeType: "application/json",
          text: JSON.stringify(info, null, 2),
        },
      ],
    };
  }

  throw new Error(`Unknown resource: ${uri}`);
});

// Start server
async function main() {
  const transport = new StdioServerTransport();
  await server.connect(transport);
  console.error("WP AI Operator MCP Server running");
}

main().catch(console.error);
