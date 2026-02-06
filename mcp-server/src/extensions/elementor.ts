/**
 * Elementor Extension
 * Full Elementor page builder control: layouts, widgets, templates, global styles
 */

import { BaseExtension } from "./base.js";
import { ExtensionMetadata, ToolDefinition, ElementorElement } from "../types/index.js";

export class ElementorExtension extends BaseExtension {
  metadata: ExtensionMetadata = {
    name: "elementor",
    version: "1.0.0",
    description: "Elementor page builder: layouts, widgets, templates, global styles",
    author: "DigID",
    wpPlugins: ["elementor", "elementor-pro"],
  };

  protected async onInitialize(): Promise<void> {
    this.registerHandler("wp_get_elementor", this.getElementor);
    this.registerHandler("wp_set_elementor", this.setElementor);
    this.registerHandler("wp_list_elementor_templates", this.listTemplates);
    this.registerHandler("wp_apply_elementor_template", this.applyTemplate);
    this.registerHandler("wp_create_landing_page", this.createLandingPage);
    this.registerHandler("wp_get_elementor_globals", this.getGlobals);
    this.registerHandler("wp_clone_elementor_page", this.clonePage);
  }

  getTools(): ToolDefinition[] {
    return [
      {
        name: "wp_get_elementor",
        description: "Get Elementor page data (sections, columns, widgets)",
        inputSchema: {
          type: "object",
          properties: {
            id: { type: "number", description: "Page ID" },
            site: { type: "string" },
          },
          required: ["id"],
        },
      },
      {
        name: "wp_set_elementor",
        description: "Set/replace Elementor page data",
        inputSchema: {
          type: "object",
          properties: {
            id: { type: "number", description: "Page ID" },
            elementor_data: {
              type: "string",
              description: "Elementor JSON data (array of sections)",
            },
            site: { type: "string" },
          },
          required: ["id", "elementor_data"],
        },
      },
      {
        name: "wp_list_elementor_templates",
        description: "List saved Elementor templates (sections, pages, popups, headers, footers)",
        inputSchema: {
          type: "object",
          properties: {
            type: {
              type: "string",
              enum: ["page", "section", "popup", "header", "footer", "single", "archive", "all"],
              description: "Template type (default: all)",
            },
            site: { type: "string" },
          },
        },
      },
      {
        name: "wp_apply_elementor_template",
        description: "Apply a saved template to a page",
        inputSchema: {
          type: "object",
          properties: {
            template_id: { type: "number", description: "Template ID to apply" },
            page_id: { type: "number", description: "Target page ID" },
            mode: {
              type: "string",
              enum: ["replace", "append", "prepend"],
              description: "How to apply (default: replace)",
            },
            site: { type: "string" },
          },
          required: ["template_id", "page_id"],
        },
      },
      {
        name: "wp_create_landing_page",
        description: "Create a complete landing page with hero, features, testimonials, and CTA sections",
        inputSchema: {
          type: "object",
          properties: {
            title: { type: "string", description: "Page title" },
            status: { type: "string", enum: ["draft", "publish", "private"], description: "Page status (default: draft)" },
            template_id: { type: "number", description: "Optional template ID to start from" },
            sections: {
              type: "array",
              items: { type: "object" },
              description: "Section configurations",
            },
            elementor_data: {
              type: "string",
              description: "Optional Elementor JSON data if not using template",
            },
            site: { type: "string" },
          },
          required: ["title"],
        },
      },
      {
        name: "wp_get_elementor_globals",
        description: "Get Elementor global colors, fonts, and settings",
        inputSchema: {
          type: "object",
          properties: {
            site: { type: "string" },
          },
        },
      },
      {
        name: "wp_clone_elementor_page",
        description: "Clone an Elementor page to create a copy",
        inputSchema: {
          type: "object",
          properties: {
            source_id: { type: "number", description: "Page to clone" },
            title: { type: "string", description: "Title for the new page" },
            status: { type: "string", enum: ["draft", "publish", "private"], description: "Status for new page (default: draft)" },
            site: { type: "string" },
          },
          required: ["source_id", "title"],
        },
      },
    ];
  }

  // ==================== Tool Handlers ====================

  private async getElementor(args: { id: number; site?: string }) {
    return this.request("GET", `elementor/${args.id}`, null, { site: args.site });
  }

  private async setElementor(args: { id: number; elementor_data: string; site?: string }) {
    return this.request("POST", `elementor/${args.id}`, {
      elementor_data: args.elementor_data,
    }, { site: args.site });
  }

  private async listTemplates(args: { type?: string; site?: string }) {
    const params = new URLSearchParams();
    if (args.type && args.type !== "all") params.set("type", args.type);
    return this.request("GET", `elementor/templates?${params}`, null, { site: args.site });
  }

  private async applyTemplate(args: {
    template_id: number;
    page_id: number;
    mode?: string;
    site?: string;
  }) {
    return this.request("POST", `elementor/templates/${args.template_id}/apply`, {
      page_id: args.page_id,
      mode: args.mode || "replace",
    }, { site: args.site });
  }

  private async createLandingPage(args: {
    title: string;
    status?: string;
    template_id?: number;
    sections?: Array<any>;
    elementor_data?: string;
    site?: string;
  }) {
    const { site, ...data } = args;
    return this.request("POST", "elementor/landing-page", data, { site });
  }

  private async getGlobals(args: { site?: string }) {
    return this.request("GET", "elementor/globals", null, { site: args.site });
  }

  private async clonePage(args: {
    source_id: number;
    title: string;
    status?: string;
    site?: string;
  }) {
    return this.request("POST", "elementor/clone", {
      source_id: args.source_id,
      title: args.title,
      status: args.status,
    }, { site: args.site });
  }
}

export default ElementorExtension;
