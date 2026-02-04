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
    this.registerHandler("wp_create_form", this.createForm);
    this.registerHandler("wp_update_form", this.updateForm);
    this.registerHandler("wp_delete_form", this.deleteForm);
    this.registerHandler("wp_get_form_submissions", this.getSubmissions);
    this.registerHandler("wp_submit_form", this.submitForm);
    this.registerHandler("wp_get_form_plugin", this.getFormPlugin);
  }

  getTools(): ToolDefinition[] {
    return [
      {
        name: "wp_list_forms",
        description: "List all forms (works with CF7, WPForms, Gravity Forms, Elementor Forms, Ninja Forms)",
        inputSchema: {
          type: "object",
          properties: {
            plugin: {
              type: "string",
              enum: ["cf7", "wpforms", "gravityforms", "elementor", "ninjaforms", "all"],
              description: "Filter by form plugin (default: all)",
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
            form_id: { type: "string", description: "Form ID" },
            plugin: {
              type: "string",
              enum: ["cf7", "wpforms", "gravityforms", "elementor", "ninjaforms"],
              description: "Form plugin (auto-detected if not specified)",
            },
            site: { type: "string" },
          },
          required: ["form_id"],
        },
      },
      {
        name: "wp_create_form",
        description: "Create a new form with specified fields",
        inputSchema: {
          type: "object",
          properties: {
            title: { type: "string", description: "Form title" },
            plugin: {
              type: "string",
              enum: ["cf7", "wpforms", "gravityforms", "elementor", "ninjaforms"],
              description: "Which plugin to create the form in",
            },
            fields: {
              type: "array",
              items: {
                type: "object",
                properties: {
                  type: {
                    type: "string",
                    enum: ["text", "email", "textarea", "select", "checkbox", "radio", "file", "number", "phone", "url", "date"],
                  },
                  label: { type: "string" },
                  name: { type: "string" },
                  required: { type: "boolean" },
                  placeholder: { type: "string" },
                  options: { type: "array", items: { type: "string" }, description: "For select/checkbox/radio" },
                },
                required: ["type", "label"],
              },
              description: "Form fields",
            },
            settings: {
              type: "object",
              properties: {
                submit_button_text: { type: "string" },
                success_message: { type: "string" },
                email_to: { type: "string" },
                email_subject: { type: "string" },
              },
            },
            site: { type: "string" },
          },
          required: ["title", "plugin", "fields"],
        },
      },
      {
        name: "wp_update_form",
        description: "Update an existing form",
        inputSchema: {
          type: "object",
          properties: {
            form_id: { type: "string", description: "Form ID" },
            plugin: { type: "string", enum: ["cf7", "wpforms", "gravityforms", "elementor", "ninjaforms"] },
            title: { type: "string" },
            fields: {
              type: "array",
              items: {
                type: "object",
                properties: {
                  id: { type: "string", description: "Existing field ID (for update) or null (for new)" },
                  type: { type: "string" },
                  label: { type: "string" },
                  name: { type: "string" },
                  required: { type: "boolean" },
                },
              },
            },
            settings: { type: "object" },
            site: { type: "string" },
          },
          required: ["form_id"],
        },
      },
      {
        name: "wp_delete_form",
        description: "Delete a form",
        inputSchema: {
          type: "object",
          properties: {
            form_id: { type: "string", description: "Form ID" },
            plugin: { type: "string", enum: ["cf7", "wpforms", "gravityforms", "elementor", "ninjaforms"] },
            site: { type: "string" },
          },
          required: ["form_id"],
        },
      },
      {
        name: "wp_get_form_submissions",
        description: "Get form submissions/entries",
        inputSchema: {
          type: "object",
          properties: {
            form_id: { type: "string", description: "Form ID" },
            plugin: { type: "string", enum: ["cf7", "wpforms", "gravityforms", "elementor", "ninjaforms"] },
            per_page: { type: "number", description: "Entries per page (default: 20)" },
            page: { type: "number", description: "Page number" },
            status: { type: "string", enum: ["all", "read", "unread", "starred", "trash"] },
            site: { type: "string" },
          },
          required: ["form_id"],
        },
      },
      {
        name: "wp_submit_form",
        description: "Programmatically submit a form (useful for testing or automation)",
        inputSchema: {
          type: "object",
          properties: {
            form_id: { type: "string", description: "Form ID" },
            plugin: { type: "string", enum: ["cf7", "wpforms", "gravityforms", "elementor", "ninjaforms"] },
            data: {
              type: "object",
              description: "Form field values as key-value pairs",
              additionalProperties: true,
            },
            site: { type: "string" },
          },
          required: ["form_id", "data"],
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
    const params = new URLSearchParams();
    if (args.plugin && args.plugin !== "all") params.set("plugin", args.plugin);
    return this.request("GET", `forms?${params}`, null, { site: args.site });
  }

  private async getForm(args: { form_id: string; plugin?: string; site?: string }) {
    const params = new URLSearchParams();
    if (args.plugin) params.set("plugin", args.plugin);
    return this.request("GET", `forms/${args.form_id}?${params}`, null, { site: args.site });
  }

  private async createForm(args: {
    title: string;
    plugin: string;
    fields: Array<{
      type: string;
      label: string;
      name?: string;
      required?: boolean;
      placeholder?: string;
      options?: string[];
    }>;
    settings?: Record<string, any>;
    site?: string;
  }) {
    const { site, ...data } = args;
    return this.request("POST", "forms", data, { site });
  }

  private async updateForm(args: {
    form_id: string;
    plugin?: string;
    title?: string;
    fields?: Array<any>;
    settings?: Record<string, any>;
    site?: string;
  }) {
    const { form_id, site, ...data } = args;
    return this.request("PUT", `forms/${form_id}`, data, { site });
  }

  private async deleteForm(args: { form_id: string; plugin?: string; site?: string }) {
    const params = new URLSearchParams();
    if (args.plugin) params.set("plugin", args.plugin);
    return this.request("DELETE", `forms/${args.form_id}?${params}`, null, { site: args.site });
  }

  private async getSubmissions(args: {
    form_id: string;
    plugin?: string;
    per_page?: number;
    page?: number;
    status?: string;
    site?: string;
  }) {
    const params = new URLSearchParams();
    if (args.plugin) params.set("plugin", args.plugin);
    if (args.per_page) params.set("per_page", String(args.per_page));
    if (args.page) params.set("page", String(args.page));
    if (args.status) params.set("status", args.status);
    return this.request("GET", `forms/${args.form_id}/submissions?${params}`, null, { site: args.site });
  }

  private async submitForm(args: {
    form_id: string;
    plugin?: string;
    data: Record<string, any>;
    site?: string;
  }) {
    const { form_id, site, ...rest } = args;
    return this.request("POST", `forms/${form_id}/submit`, rest, { site });
  }

  private async getFormPlugin(args: { site?: string }) {
    return this.request("GET", "forms/plugins", null, { site: args.site });
  }
}

export default FormsExtension;
