/**
 * Forms Extension
 * Supports: Contact Form 7, WPForms, Gravity Forms, Elementor Forms, Ninja Forms
 * Provides unified interface for form management and submissions
 */

import { BaseExtension } from "./base.js";
import { ExtensionMetadata, ToolDefinition, FormDefinition, FormSubmission } from "../types/index.js";

export class FormsExtension extends BaseExtension {
  metadata: ExtensionMetadata = {
    name: "forms",
    version: "1.0.0",
    description: "Form management for CF7, WPForms, Gravity Forms, Elementor Forms, Ninja Forms",
    author: "DigID",
    wpPlugins: ["cf7", "wpforms", "gravityforms", "elementor-pro", "ninjaforms"],
  };

  protected async onInitialize(): Promise<void> {
    this.registerHandler("wp_list_forms", this.listForms);
    this.registerHandler("wp_get_form", this.getForm);
    this.registerHandler("wp_get_form_submissions", this.getSubmissions);
    this.registerHandler("wp_get_form_plugin", this.getFormPlugin);
  }

  getTools(): ToolDefinition[] {
    return [
      {
        name: "wp_list_forms",
        description: "List all forms (works with CF7, WPForms, Gravity Forms, Ninja Forms)",
        inputSchema: {
          type: "object",
          properties: {
            plugin: {
              type: "string",
              enum: ["cf7", "wpforms", "gravityforms", "ninjaforms"],
              description: "Filter by specific form plugin (optional)",
            },
            site: { type: "string" },
          },
        },
      },
      {
        name: "wp_get_form",
        description: "Get a specific form with all its fields and settings",
        inputSchema: {
          type: "object",
          properties: {
            plugin: {
              type: "string",
              enum: ["cf7", "wpforms", "gravityforms", "ninjaforms"],
              description: "Form plugin",
            },
            id: { type: "number", description: "Form ID" },
            site: { type: "string" },
          },
          required: ["plugin", "id"],
        },
      },
      {
        name: "wp_get_form_submissions",
        description: "Get form submissions/entries",
        inputSchema: {
          type: "object",
          properties: {
            plugin: {
              type: "string",
              enum: ["cf7", "wpforms", "gravityforms", "ninjaforms"],
              description: "Form plugin",
            },
            id: { type: "number", description: "Form ID" },
            per_page: { type: "number", description: "Entries per page (default: 20)" },
            page: { type: "number", description: "Page number" },
            status: { type: "string", enum: ["all", "read", "unread", "starred", "trash"] },
            site: { type: "string" },
          },
          required: ["plugin", "id"],
        },
      },
      {
        name: "wp_get_form_plugin",
        description: "Detect which form plugins are installed and active",
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

  private async listForms(args: { plugin?: string; site?: string }) {
    if (args.plugin) {
      return this.request("GET", `forms/${args.plugin}`, null, { site: args.site });
    }
    return this.request("GET", "forms", null, { site: args.site });
  }

  private async getForm(args: { plugin: string; id: number; site?: string }) {
    return this.request("GET", `forms/${args.plugin}/${args.id}`, null, { site: args.site });
  }

  private async getSubmissions(args: {
    plugin: string;
    id: number;
    per_page?: number;
    page?: number;
    status?: string;
    site?: string;
  }) {
    const params = new URLSearchParams();
    if (args.per_page) params.set("per_page", String(args.per_page));
    if (args.page) params.set("page", String(args.page));
    if (args.status) params.set("status", args.status);
    return this.request("GET", `forms/${args.plugin}/${args.id}/entries?${params}`, null, { site: args.site });
  }

  private async getFormPlugin(args: { site?: string }) {
    return this.request("GET", "forms/status", null, { site: args.site });
  }
}

export default FormsExtension;
