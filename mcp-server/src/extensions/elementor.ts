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
    this.registerHandler("wp_add_elementor_section", this.addSection);
    this.registerHandler("wp_update_elementor_widget", this.updateWidget);
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
            page_id: { type: "number", description: "Page ID" },
            site: { type: "string" },
          },
          required: ["page_id"],
        },
      },
      {
        name: "wp_set_elementor",
        description: "Set/replace Elementor page data",
        inputSchema: {
          type: "object",
          properties: {
            page_id: { type: "number", description: "Page ID" },
            elementor_data: {
              type: "string",
              description: "Elementor JSON data (array of sections)",
            },
            site: { type: "string" },
          },
          required: ["page_id", "elementor_data"],
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
            page_id: { type: "number", description: "Target page ID" },
            template_id: { type: "number", description: "Template ID to apply" },
            mode: {
              type: "string",
              enum: ["replace", "append", "prepend"],
              description: "How to apply (default: replace)",
            },
            site: { type: "string" },
          },
          required: ["page_id", "template_id"],
        },
      },
      {
        name: "wp_create_landing_page",
        description: "Create a complete landing page with hero, features, testimonials, and CTA sections",
        inputSchema: {
          type: "object",
          properties: {
            title: { type: "string", description: "Page title" },
            headline: { type: "string", description: "Hero headline" },
            subheadline: { type: "string", description: "Hero subheadline" },
            cta_text: { type: "string", description: "CTA button text" },
            cta_url: { type: "string", description: "CTA button URL" },
            hero_image_id: { type: "number", description: "Hero background image (media ID)" },
            features: {
              type: "array",
              items: {
                type: "object",
                properties: {
                  title: { type: "string" },
                  description: { type: "string" },
                  icon: { type: "string", description: "FontAwesome icon class (e.g., fas fa-star)" },
                },
              },
              description: "Feature cards (up to 4)",
            },
            testimonials: {
              type: "array",
              items: {
                type: "object",
                properties: {
                  quote: { type: "string" },
                  author: { type: "string" },
                  role: { type: "string" },
                  image_id: { type: "number" },
                },
              },
              description: "Testimonials (up to 3)",
            },
            colors: {
              type: "object",
              properties: {
                primary: { type: "string", description: "Primary color (hex)" },
                secondary: { type: "string", description: "Secondary color (hex)" },
                background: { type: "string", description: "Background color (hex)" },
                text: { type: "string", description: "Text color (hex)" },
              },
            },
            site: { type: "string" },
          },
          required: ["title", "headline", "cta_text", "cta_url"],
        },
      },
      {
        name: "wp_add_elementor_section",
        description: "Add a new section to an existing Elementor page",
        inputSchema: {
          type: "object",
          properties: {
            page_id: { type: "number", description: "Page ID" },
            position: {
              type: "string",
              enum: ["start", "end", "after"],
              description: "Where to add (default: end)",
            },
            after_section_id: { type: "string", description: "Insert after this section ID (if position=after)" },
            section_type: {
              type: "string",
              enum: ["hero", "features", "testimonials", "cta", "text", "image", "gallery", "contact", "custom"],
              description: "Type of section to add",
            },
            content: {
              type: "object",
              description: "Section content (varies by type)",
              additionalProperties: true,
            },
            site: { type: "string" },
          },
          required: ["page_id", "section_type"],
        },
      },
      {
        name: "wp_update_elementor_widget",
        description: "Update a specific widget in an Elementor page",
        inputSchema: {
          type: "object",
          properties: {
            page_id: { type: "number", description: "Page ID" },
            widget_id: { type: "string", description: "Widget element ID" },
            settings: {
              type: "object",
              description: "Widget settings to update",
              additionalProperties: true,
            },
            site: { type: "string" },
          },
          required: ["page_id", "widget_id", "settings"],
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
            source_page_id: { type: "number", description: "Page to clone" },
            new_title: { type: "string", description: "Title for the new page" },
            target_site: { type: "string", description: "Target site (for cross-site cloning)" },
            site: { type: "string", description: "Source site" },
          },
          required: ["source_page_id", "new_title"],
        },
      },
    ];
  }

  // ==================== Tool Handlers ====================

  private async getElementor(args: { page_id: number; site?: string }) {
    return this.request("GET", `elementor/${args.page_id}`, null, { site: args.site });
  }

  private async setElementor(args: { page_id: number; elementor_data: string; site?: string }) {
    return this.request("POST", `elementor/${args.page_id}`, {
      elementor_data: args.elementor_data,
    }, { site: args.site });
  }

  private async listTemplates(args: { type?: string; site?: string }) {
    const params = new URLSearchParams();
    if (args.type && args.type !== "all") params.set("type", args.type);
    return this.request("GET", `elementor/templates?${params}`, null, { site: args.site });
  }

  private async applyTemplate(args: {
    page_id: number;
    template_id: number;
    mode?: string;
    site?: string;
  }) {
    return this.request("POST", `elementor/${args.page_id}/apply-template`, {
      template_id: args.template_id,
      mode: args.mode || "replace",
    }, { site: args.site });
  }

  private async createLandingPage(args: {
    title: string;
    headline: string;
    subheadline?: string;
    cta_text: string;
    cta_url: string;
    hero_image_id?: number;
    features?: Array<{ title: string; description: string; icon?: string }>;
    testimonials?: Array<{ quote: string; author: string; role?: string; image_id?: number }>;
    colors?: { primary?: string; secondary?: string; background?: string; text?: string };
    site?: string;
  }) {
    const { site, ...data } = args;
    return this.request("POST", "elementor/landing-page", data, { site });
  }

  private async addSection(args: {
    page_id: number;
    position?: string;
    after_section_id?: string;
    section_type: string;
    content?: Record<string, any>;
    site?: string;
  }) {
    const { page_id, site, ...data } = args;
    return this.request("POST", `elementor/${page_id}/sections`, data, { site });
  }

  private async updateWidget(args: {
    page_id: number;
    widget_id: string;
    settings: Record<string, any>;
    site?: string;
  }) {
    const { page_id, site, ...data } = args;
    return this.request("PUT", `elementor/${page_id}/widgets/${args.widget_id}`, data, { site });
  }

  private async getGlobals(args: { site?: string }) {
    return this.request("GET", "elementor/globals", null, { site: args.site });
  }

  private async clonePage(args: {
    source_page_id: number;
    new_title: string;
    target_site?: string;
    site?: string;
  }) {
    return this.request("POST", `elementor/${args.source_page_id}/clone`, {
      new_title: args.new_title,
      target_site: args.target_site,
    }, { site: args.site });
  }
}

export default ElementorExtension;
