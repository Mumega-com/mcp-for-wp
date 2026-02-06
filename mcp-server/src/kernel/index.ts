/**
 * WP AI Operator - Microkernel
 * Core kernel that manages extensions, tools, and WordPress communication
 */

import fetch from "node-fetch";
import FormData from "form-data";
import * as fs from "fs";
import {
  Kernel,
  KernelConfig,
  SiteConfig,
  Extension,
  ToolDefinition,
  ToolHandler,
  WPResponse,
  EventType,
  EventHandler,
  Event,
} from "../types/index.js";

interface RegisteredTool {
  definition: ToolDefinition;
  handler: ToolHandler;
  extensionName: string;
}

export class WPKernel implements Kernel {
  private config: KernelConfig;
  private extensions: Map<string, Extension> = new Map();
  private tools: Map<string, RegisteredTool> = new Map();
  private eventHandlers: Map<EventType, Set<EventHandler>> = new Map();
  private currentSite: string;

  constructor(config: KernelConfig) {
    this.config = config;
    this.currentSite = config.defaultSite || Object.keys(config.sites)[0];

    if (!this.currentSite || !config.sites[this.currentSite]) {
      throw new Error("No valid site configuration found");
    }
  }

  // ==================== Configuration ====================

  getConfig(): KernelConfig {
    return this.config;
  }

  getSite(name?: string): SiteConfig {
    const siteName = name || this.currentSite;
    const site = this.config.sites[siteName];
    if (!site) {
      throw new Error(`Site not found: ${siteName}`);
    }
    return site;
  }

  setCurrentSite(name: string): void {
    if (!this.config.sites[name]) {
      throw new Error(`Site not found: ${name}`);
    }
    this.currentSite = name;
    this.emit("site:changed", { site: name });
  }

  // ==================== HTTP Client ====================

  async request(
    method: string,
    endpoint: string,
    data?: any,
    options?: { site?: string; isFormData?: boolean }
  ): Promise<WPResponse> {
    const site = this.getSite(options?.site);
    const url = `${site.url.replace(/\/$/, "")}/wp-json/site-pilot-ai/v1/${endpoint.replace(/^\//, "")}`;

    this.emit("request:start", { method, endpoint, site: options?.site || this.currentSite });

    const headers: Record<string, string> = {
      "X-API-Key": site.apiKey,
    };

    let body: any;
    if (options?.isFormData) {
      body = data;
    } else if (data && method !== "GET") {
      headers["Content-Type"] = "application/json";
      body = JSON.stringify(data);
    }

    try {
      const response = await fetch(url, {
        method,
        headers,
        body,
      });

      const result = (await response.json()) as WPResponse;

      this.emit("request:end", {
        method,
        endpoint,
        status: response.status,
        success: result.success !== false,
      });

      return result;
    } catch (error: any) {
      this.emit("request:end", {
        method,
        endpoint,
        status: 0,
        success: false,
        error: error.message,
      });
      throw error;
    }
  }

  // Helper for file uploads
  async uploadFile(filePath: string, options?: { site?: string }): Promise<WPResponse> {
    const form = new FormData();
    form.append("file", fs.createReadStream(filePath));
    return this.request("POST", "media", form, { ...options, isFormData: true });
  }

  // ==================== Extension Management ====================

  async loadExtension(extension: Extension): Promise<void> {
    const name = extension.metadata.name;

    // Check dependencies
    if (extension.metadata.dependencies) {
      for (const dep of extension.metadata.dependencies) {
        if (!this.extensions.has(dep)) {
          throw new Error(`Extension "${name}" requires "${dep}" to be loaded first`);
        }
      }
    }

    // Check if already loaded
    if (this.extensions.has(name)) {
      this.log("warn", `Extension "${name}" is already loaded, skipping`);
      return;
    }

    // Initialize extension
    await extension.initialize(this);

    // Register tools
    const tools = extension.getTools();
    for (const tool of tools) {
      this.registerTool(name, tool, (args) => extension.handleTool(tool.name, args));
    }

    this.extensions.set(name, extension);
    this.emit("extension:loaded", { name, tools: tools.length });
    this.log("info", `Extension loaded: ${name} (${tools.length} tools)`);
  }

  async unloadExtension(name: string): Promise<void> {
    const extension = this.extensions.get(name);
    if (!extension) {
      throw new Error(`Extension not found: ${name}`);
    }

    // Check if other extensions depend on this one
    for (const [extName, ext] of this.extensions) {
      if (ext.metadata.dependencies?.includes(name)) {
        throw new Error(`Cannot unload "${name}": "${extName}" depends on it`);
      }
    }

    // Unregister tools
    const tools = extension.getTools();
    for (const tool of tools) {
      this.unregisterTool(tool.name);
    }

    // Cleanup
    if (extension.destroy) {
      await extension.destroy();
    }

    this.extensions.delete(name);
    this.emit("extension:unloaded", { name });
    this.log("info", `Extension unloaded: ${name}`);
  }

  getExtension(name: string): Extension | undefined {
    return this.extensions.get(name);
  }

  getLoadedExtensions(): Extension[] {
    return Array.from(this.extensions.values());
  }

  // ==================== Tool Registry ====================

  registerTool(extensionName: string, tool: ToolDefinition, handler: ToolHandler): void {
    if (this.tools.has(tool.name)) {
      this.log("warn", `Tool "${tool.name}" already registered, overwriting`);
    }

    this.tools.set(tool.name, {
      definition: tool,
      handler,
      extensionName,
    });

    this.log("debug", `Tool registered: ${tool.name} (from ${extensionName})`);
  }

  unregisterTool(toolName: string): void {
    this.tools.delete(toolName);
    this.log("debug", `Tool unregistered: ${toolName}`);
  }

  getAllTools(): ToolDefinition[] {
    return Array.from(this.tools.values()).map((t) => t.definition);
  }

  async callTool(name: string, args: Record<string, any>): Promise<any> {
    const tool = this.tools.get(name);
    if (!tool) {
      throw new Error(`Tool not found: ${name}`);
    }

    this.emit("tool:before", { name, args });

    try {
      const result = await tool.handler(args);
      this.emit("tool:after", { name, args, result });
      return result;
    } catch (error: any) {
      this.emit("tool:error", { name, args, error: error.message });
      throw error;
    }
  }

  // ==================== Event Bus ====================

  on(event: EventType, handler: EventHandler): void {
    if (!this.eventHandlers.has(event)) {
      this.eventHandlers.set(event, new Set());
    }
    this.eventHandlers.get(event)!.add(handler);
  }

  off(event: EventType, handler: EventHandler): void {
    this.eventHandlers.get(event)?.delete(handler);
  }

  emit(event: EventType, data: any): void {
    const handlers = this.eventHandlers.get(event);
    if (handlers) {
      const eventObj: Event = { type: event, data, timestamp: Date.now() };
      for (const handler of handlers) {
        try {
          handler(eventObj);
        } catch (error) {
          this.log("error", `Event handler error for ${event}`, error);
        }
      }
    }
  }

  // ==================== Logging ====================

  log(level: "debug" | "info" | "warn" | "error", message: string, data?: any): void {
    const timestamp = new Date().toISOString();
    const prefix = `[${timestamp}] [${level.toUpperCase()}]`;

    if (data) {
      console.error(`${prefix} ${message}`, data);
    } else {
      console.error(`${prefix} ${message}`);
    }
  }
}

// Factory function to create kernel from environment
export function createKernel(): WPKernel {
  const config: KernelConfig = {
    sites: {},
    enabledExtensions: ["core", "elementor", "seo", "forms"],
  };

  // Load from environment variables
  if (process.env.WP_URL && process.env.WP_API_KEY) {
    config.sites["default"] = {
      url: process.env.WP_URL,
      apiKey: process.env.WP_API_KEY,
      name: process.env.WP_SITE_NAME || "Default Site",
    };
    config.defaultSite = "default";
  }

  // Load from config file if exists
  const configPath = process.env.WP_CONFIG_PATH || `${process.env.HOME}/.wp-ai-operator/config.json`;
  if (fs.existsSync(configPath)) {
    try {
      const fileConfig = JSON.parse(fs.readFileSync(configPath, "utf-8"));
      Object.assign(config, fileConfig);
    } catch (error) {
      console.error(`Failed to load config from ${configPath}:`, error);
    }
  }

  if (Object.keys(config.sites).length === 0) {
    throw new Error(
      "No WordPress sites configured. Set WP_URL and WP_API_KEY environment variables or create ~/.wp-ai-operator/config.json"
    );
  }

  return new WPKernel(config);
}

export default WPKernel;
