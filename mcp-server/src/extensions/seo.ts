/**
 * SEO Extension
 * Supports: Yoast SEO, RankMath, All-in-One SEO, SEOPress
 * Provides unified interface across all SEO plugins
 */

import { BaseExtension } from "./base.js";
import { ExtensionMetadata, ToolDefinition, SEOData } from "../types/index.js";

export class SEOExtension extends BaseExtension {
  metadata: ExtensionMetadata = {
    name: "seo",
    version: "1.0.0",
    description: "SEO management for Yoast, RankMath, AIOSEO, SEOPress",
    author: "DigID",
    wpPlugins: ["yoast", "rankmath", "aioseo", "seopress"],
  };

  protected async onInitialize(): Promise<void> {
    this.registerHandler("wp_get_seo", this.getSEO);
    this.registerHandler("wp_set_seo", this.setSEO);
    this.registerHandler("wp_analyze_seo", this.analyzeSEO);
    this.registerHandler("wp_bulk_seo", this.bulkSEO);
    this.registerHandler("wp_get_seo_plugin", this.getSEOPlugin);
  }

  getTools(): ToolDefinition[] {
    return [
      {
        name: "wp_get_seo",
        description: "Get SEO metadata for a post or page (works with Yoast, RankMath, AIOSEO, SEOPress)",
        inputSchema: {
          type: "object",
          properties: {
            id: { type: "number", description: "Post or page ID" },
            site: { type: "string" },
          },
          required: ["id"],
        },
      },
      {
        name: "wp_set_seo",
        description: "Set SEO metadata (title, description, keywords, Open Graph, etc.)",
        inputSchema: {
          type: "object",
          properties: {
            id: { type: "number", description: "Post or page ID" },
            title: { type: "string", description: "SEO title (appears in search results)" },
            description: { type: "string", description: "Meta description (155 chars recommended)" },
            focus_keyword: { type: "string", description: "Primary keyword to optimize for" },
            canonical: { type: "string", description: "Canonical URL" },
            og_title: { type: "string", description: "Open Graph title (for social sharing)" },
            og_description: { type: "string", description: "Open Graph description" },
            og_image: { type: "number", description: "Open Graph image (media ID)" },
            robots_noindex: { type: "boolean", description: "Prevent search engines from indexing" },
            robots_nofollow: { type: "boolean", description: "Prevent following links" },
            site: { type: "string" },
          },
          required: ["id"],
        },
      },
      {
        name: "wp_analyze_seo",
        description: "Analyze SEO quality of a post/page and get improvement suggestions",
        inputSchema: {
          type: "object",
          properties: {
            id: { type: "number", description: "Post or page ID" },
            keyword: { type: "string", description: "Focus keyword to analyze against" },
            site: { type: "string" },
          },
          required: ["id"],
        },
      },
      {
        name: "wp_bulk_seo",
        description: "Update SEO for multiple posts/pages at once",
        inputSchema: {
          type: "object",
          properties: {
            updates: {
              type: "array",
              items: {
                type: "object",
                properties: {
                  post_id: { type: "number" },
                  title: { type: "string" },
                  description: { type: "string" },
                  focus_keyword: { type: "string" },
                },
                required: ["post_id"],
              },
              description: "Array of SEO updates",
            },
            site: { type: "string" },
          },
          required: ["updates"],
        },
      },
      {
        name: "wp_get_seo_plugin",
        description: "Detect which SEO plugin is active and its version",
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

  private async getSEO(args: { id: number; site?: string }) {
    return this.request("GET", `seo/${args.id}`, null, { site: args.site });
  }

  private async setSEO(args: {
    id: number;
    title?: string;
    description?: string;
    focus_keyword?: string;
    canonical?: string;
    og_title?: string;
    og_description?: string;
    og_image?: number;
    robots_noindex?: boolean;
    robots_nofollow?: boolean;
    site?: string;
  }) {
    const { id, site, ...data } = args;
    return this.request("POST", `seo/${id}`, data, { site });
  }

  private async analyzeSEO(args: { id: number; keyword?: string; site?: string }) {
    const params = new URLSearchParams();
    if (args.keyword) params.set("keyword", args.keyword);
    return this.request("GET", `seo/${args.id}/analyze?${params}`, null, { site: args.site });
  }

  private async bulkSEO(args: {
    updates: Array<{
      id: number;
      title?: string;
      description?: string;
      focus_keyword?: string;
    }>;
    site?: string;
  }) {
    return this.request("POST", "seo/bulk", { updates: args.updates }, { site: args.site });
  }

  private async getSEOPlugin(args: { site?: string }) {
    return this.request("GET", "seo/status", null, { site: args.site });
  }
}

export default SEOExtension;
