/**
 * Base Extension Class
 * All extensions should extend this class
 */

import {
  Extension,
  ExtensionMetadata,
  Kernel,
  ToolDefinition,
  WPResponse,
} from "../types/index.js";

export abstract class BaseExtension implements Extension {
  abstract metadata: ExtensionMetadata;

  protected kernel!: Kernel;
  protected tools: Map<string, (args: any) => Promise<any>> = new Map();

  async initialize(kernel: Kernel): Promise<void> {
    this.kernel = kernel;
    await this.onInitialize();
  }

  // Override in subclass for custom initialization
  protected async onInitialize(): Promise<void> {}

  abstract getTools(): ToolDefinition[];

  async handleTool(name: string, args: Record<string, any>): Promise<any> {
    const handler = this.tools.get(name);
    if (!handler) {
      throw new Error(`Tool handler not found: ${name}`);
    }
    return handler(args);
  }

  canHandle(toolName: string): boolean {
    return this.tools.has(toolName);
  }

  async destroy(): Promise<void> {
    // Override in subclass for cleanup
  }

  // Helper methods for subclasses

  protected async request(
    method: string,
    endpoint: string,
    data?: any,
    options?: { site?: string; isFormData?: boolean }
  ): Promise<WPResponse> {
    return this.kernel.request(method, endpoint, data, options);
  }

  protected log(level: "debug" | "info" | "warn" | "error", message: string, data?: any): void {
    this.kernel.log(level, `[${this.metadata.name}] ${message}`, data);
  }

  // Register a tool handler
  protected registerHandler(name: string, handler: (args: any) => Promise<any>): void {
    this.tools.set(name, handler.bind(this));
  }
}

export default BaseExtension;
