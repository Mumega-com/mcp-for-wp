/**
 * Core Extension
 * Basic WordPress operations: posts, pages, media, site info
 */

import { BaseExtension } from "./base.js";
import { ExtensionMetadata, ToolDefinition } from "../types/index.js";

export class CoreExtension extends BaseExtension {
  metadata: ExtensionMetadata = {
    name: "core",
    version: "1.0.0",
    description: "Core WordPress operations: posts, pages, media, site info",
    author: "DigID",
  };

  protected async onInitialize(): Promise<void> {
    // Register all tool handlers
    this.registerHandler("wp_site_info", this.getSiteInfo);
    this.registerHandler("wp_analytics", this.getAnalytics);
    this.registerHandler("wp_list_posts", this.listPosts);
    this.registerHandler("wp_create_post", this.createPost);
    this.registerHandler("wp_update_post", this.updatePost);
    this.registerHandler("wp_delete_post", this.deletePost);
    this.registerHandler("wp_list_pages", this.listPages);
    this.registerHandler("wp_create_page", this.createPage);
    this.registerHandler("wp_update_page", this.updatePage);
    this.registerHandler("wp_upload_media", this.uploadMedia);
    this.registerHandler("wp_upload_media_from_url", this.uploadMediaFromUrl);
    this.registerHandler("wp_list_drafts", this.listDrafts);
    this.registerHandler("wp_delete_all_drafts", this.deleteAllDrafts);
    this.registerHandler("wp_detect_plugins", this.detectPlugins);
  }

  getTools(): ToolDefinition[] {
    return [
      // Site Info
      {
        name: "wp_site_info",
        description: "Get WordPress site information (name, URL, theme, stats, active plugins)",
        inputSchema: {
          type: "object",
          properties: {
            site: { type: "string", description: "Site name (for multi-site)" },
          },
        },
      },
      {
        name: "wp_analytics",
        description: "Get site analytics and API activity logs",
        inputSchema: {
          type: "object",
          properties: {
            days: { type: "number", description: "Number of days (default: 30)" },
            site: { type: "string", description: "Site name" },
          },
        },
      },
      {
        name: "wp_detect_plugins",
        description: "Detect which WordPress plugins are installed (SEO, forms, page builders, etc.)",
        inputSchema: {
          type: "object",
          properties: {
            site: { type: "string", description: "Site name" },
          },
        },
      },

      // Posts
      {
        name: "wp_list_posts",
        description: "List published blog posts",
        inputSchema: {
          type: "object",
          properties: {
            per_page: { type: "number", description: "Posts per page (default: 10)" },
            page: { type: "number", description: "Page number" },
            status: { type: "string", enum: ["publish", "draft", "private", "any"] },
            site: { type: "string" },
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
            status: { type: "string", enum: ["draft", "publish", "private"], description: "Default: draft" },
            excerpt: { type: "string", description: "Post excerpt" },
            categories: { type: "array", items: { type: "number" }, description: "Category IDs" },
            tags: { type: "array", items: { type: "string" }, description: "Tag names" },
            featured_image: { type: "number", description: "Media ID for featured image" },
            site: { type: "string" },
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
            title: { type: "string" },
            content: { type: "string" },
            status: { type: "string", enum: ["draft", "publish", "private"] },
            excerpt: { type: "string" },
            site: { type: "string" },
          },
          required: ["id"],
        },
      },
      {
        name: "wp_delete_post",
        description: "Delete a post",
        inputSchema: {
          type: "object",
          properties: {
            id: { type: "number", description: "Post ID" },
            force: { type: "boolean", description: "Permanently delete (default: false = trash)" },
            site: { type: "string" },
          },
          required: ["id"],
        },
      },

      // Pages
      {
        name: "wp_list_pages",
        description: "List all pages",
        inputSchema: {
          type: "object",
          properties: {
            per_page: { type: "number", description: "Pages per page (default: 20)" },
            status: { type: "string", enum: ["publish", "draft", "private", "any"] },
            site: { type: "string" },
          },
        },
      },
      {
        name: "wp_create_page",
        description: "Create a new page",
        inputSchema: {
          type: "object",
          properties: {
            title: { type: "string", description: "Page title" },
            content: { type: "string", description: "Page content (HTML)" },
            status: { type: "string", enum: ["draft", "publish", "private"], description: "Default: draft" },
            template: { type: "string", description: "Page template slug" },
            parent: { type: "number", description: "Parent page ID" },
            site: { type: "string" },
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
            title: { type: "string" },
            content: { type: "string" },
            status: { type: "string", enum: ["draft", "publish", "private"] },
            template: { type: "string" },
            site: { type: "string" },
          },
          required: ["id"],
        },
      },

      // Media
      {
        name: "wp_upload_media",
        description: "Upload a file to the media library",
        inputSchema: {
          type: "object",
          properties: {
            file_path: { type: "string", description: "Local file path" },
            site: { type: "string" },
          },
          required: ["file_path"],
        },
      },
      {
        name: "wp_upload_media_from_url",
        description: "Download an image from URL and upload to media library",
        inputSchema: {
          type: "object",
          properties: {
            url: { type: "string", description: "Image URL" },
            filename: { type: "string", description: "Filename to save as" },
            site: { type: "string" },
          },
          required: ["url", "filename"],
        },
      },

      // Drafts
      {
        name: "wp_list_drafts",
        description: "List all draft posts",
        inputSchema: {
          type: "object",
          properties: {
            site: { type: "string" },
          },
        },
      },
      {
        name: "wp_delete_all_drafts",
        description: "Delete all draft posts (bulk cleanup)",
        inputSchema: {
          type: "object",
          properties: {
            site: { type: "string" },
          },
        },
      },
    ];
  }

  // ==================== Tool Handlers ====================

  private async getSiteInfo(args: { site?: string }) {
    return this.request("GET", "site-info", null, { site: args.site });
  }

  private async getAnalytics(args: { days?: number; site?: string }) {
    return this.request("GET", `analytics?days=${args.days || 30}`, null, { site: args.site });
  }

  private async detectPlugins(args: { site?: string }) {
    return this.request("GET", "detect-plugins", null, { site: args.site });
  }

  private async listPosts(args: { per_page?: number; page?: number; status?: string; site?: string }) {
    const params = new URLSearchParams();
    if (args.per_page) params.set("per_page", String(args.per_page));
    if (args.page) params.set("page", String(args.page));
    if (args.status) params.set("status", args.status);
    return this.request("GET", `posts?${params}`, null, { site: args.site });
  }

  private async createPost(args: {
    title: string;
    content: string;
    status?: string;
    excerpt?: string;
    categories?: number[];
    tags?: string[];
    featured_image?: number;
    site?: string;
  }) {
    const { site, ...data } = args;
    return this.request("POST", "posts", data, { site });
  }

  private async updatePost(args: {
    id: number;
    title?: string;
    content?: string;
    status?: string;
    excerpt?: string;
    site?: string;
  }) {
    const { id, site, ...data } = args;
    return this.request("PUT", `posts/${id}`, data, { site });
  }

  private async deletePost(args: { id: number; force?: boolean; site?: string }) {
    return this.request("DELETE", `posts/${args.id}?force=${args.force || false}`, null, { site: args.site });
  }

  private async listPages(args: { per_page?: number; status?: string; site?: string }) {
    const params = new URLSearchParams();
    if (args.per_page) params.set("per_page", String(args.per_page));
    if (args.status) params.set("status", args.status);
    return this.request("GET", `pages?${params}`, null, { site: args.site });
  }

  private async createPage(args: {
    title: string;
    content?: string;
    status?: string;
    template?: string;
    parent?: number;
    site?: string;
  }) {
    const { site, ...data } = args;
    return this.request("POST", "pages", data, { site });
  }

  private async updatePage(args: {
    id: number;
    title?: string;
    content?: string;
    status?: string;
    template?: string;
    site?: string;
  }) {
    const { id, site, ...data } = args;
    return this.request("PUT", `pages/${id}`, data, { site });
  }

  private async uploadMedia(args: { file_path: string; site?: string }) {
    return this.kernel.uploadFile(args.file_path, { site: args.site });
  }

  private async uploadMediaFromUrl(args: { url: string; filename: string; site?: string }) {
    // Download and upload via kernel
    const fetch = (await import("node-fetch")).default;
    const fs = await import("fs");

    const response = await fetch(args.url);
    const buffer = await response.buffer();

    const tempPath = `/tmp/${args.filename}`;
    fs.writeFileSync(tempPath, buffer);

    const result = await this.kernel.uploadFile(tempPath, { site: args.site });
    fs.unlinkSync(tempPath);

    return result;
  }

  private async listDrafts(args: { site?: string }) {
    return this.request("GET", "drafts", null, { site: args.site });
  }

  private async deleteAllDrafts(args: { site?: string }) {
    return this.request("DELETE", "drafts/delete-all", null, { site: args.site });
  }
}

export default CoreExtension;
