/**
 * WP AI Operator - Type Definitions
 * Microkernel architecture types
 */

// Tool definition for MCP
export interface ToolDefinition {
  name: string;
  description: string;
  inputSchema: {
    type: "object";
    properties: Record<string, any>;
    required?: string[];
  };
}

// Tool handler function
export type ToolHandler = (args: Record<string, any>) => Promise<any>;

// Extension metadata
export interface ExtensionMetadata {
  name: string;
  version: string;
  description: string;
  author?: string;
  dependencies?: string[]; // Other extensions this depends on
  wpPlugins?: string[]; // WordPress plugins this extension supports
}

// Extension interface - all extensions must implement this
export interface Extension {
  metadata: ExtensionMetadata;

  // Initialize the extension with kernel reference
  initialize(kernel: Kernel): Promise<void>;

  // Return tools this extension provides
  getTools(): ToolDefinition[];

  // Handle a tool call
  handleTool(name: string, args: Record<string, any>): Promise<any>;

  // Check if this extension can handle a tool
  canHandle(toolName: string): boolean;

  // Cleanup when extension is unloaded
  destroy?(): Promise<void>;
}

// WordPress API response
export interface WPResponse {
  success?: boolean;
  error?: string;
  code?: string;
  message?: string;
  [key: string]: any;
}

// Site configuration
export interface SiteConfig {
  url: string;
  apiKey: string;
  name?: string;
  extensions?: string[]; // Enabled extensions for this site
}

// Kernel configuration
export interface KernelConfig {
  sites: Record<string, SiteConfig>;
  defaultSite?: string;
  extensionsPath?: string;
  enabledExtensions?: string[];
}

// Event types for the event bus
export type EventType =
  | "tool:before"
  | "tool:after"
  | "tool:error"
  | "extension:loaded"
  | "extension:unloaded"
  | "site:changed"
  | "request:start"
  | "request:end";

export interface Event {
  type: EventType;
  data: any;
  timestamp: number;
}

export type EventHandler = (event: Event) => void | Promise<void>;

// Kernel interface
export interface Kernel {
  // Configuration
  getConfig(): KernelConfig;
  getSite(name?: string): SiteConfig;

  // HTTP client for WordPress API
  request(
    method: string,
    endpoint: string,
    data?: any,
    options?: { site?: string; isFormData?: boolean }
  ): Promise<WPResponse>;

  // File upload helper
  uploadFile(filePath: string, options?: { site?: string }): Promise<WPResponse>;

  // Extension management
  loadExtension(extension: Extension): Promise<void>;
  unloadExtension(name: string): Promise<void>;
  getExtension(name: string): Extension | undefined;
  getLoadedExtensions(): Extension[];

  // Tool registry
  registerTool(extensionName: string, tool: ToolDefinition, handler: ToolHandler): void;
  unregisterTool(toolName: string): void;
  getAllTools(): ToolDefinition[];
  callTool(name: string, args: Record<string, any>): Promise<any>;

  // Event bus
  on(event: EventType, handler: EventHandler): void;
  off(event: EventType, handler: EventHandler): void;
  emit(event: EventType, data: any): void;

  // Logging
  log(level: "debug" | "info" | "warn" | "error", message: string, data?: any): void;
}

// WordPress plugin detection response
export interface PluginDetection {
  slug: string;
  name: string;
  version: string | null;
  active: boolean;
}

// Common WordPress content types
export interface WPPost {
  id: number;
  title: string;
  content: string;
  excerpt?: string;
  status: "publish" | "draft" | "private" | "pending" | "trash";
  date: string;
  modified: string;
  slug: string;
  url: string;
  author?: number;
  categories?: number[];
  tags?: string[];
  featured_image?: string;
}

export interface WPPage extends WPPost {
  template?: string;
  parent?: number;
  menu_order?: number;
  elementor_data?: string;
  elementor_edit_mode?: string;
}

export interface WPMedia {
  id: number;
  url: string;
  filename: string;
  mime_type: string;
  width?: number;
  height?: number;
  alt?: string;
  caption?: string;
}

// SEO data structure (normalized across plugins)
export interface SEOData {
  post_id: number;
  title?: string;
  description?: string;
  focus_keyword?: string;
  canonical_url?: string;
  og_title?: string;
  og_description?: string;
  og_image?: string;
  twitter_title?: string;
  twitter_description?: string;
  robots?: {
    index?: boolean;
    follow?: boolean;
  };
  schema?: any;
  // Plugin-specific data stored here
  _raw?: Record<string, any>;
}

// Form data structure (normalized across plugins)
export interface FormDefinition {
  id: number | string;
  title: string;
  plugin: string; // cf7, wpforms, gravity, elementor
  fields: FormField[];
  settings?: Record<string, any>;
  _raw?: any;
}

export interface FormField {
  id: string;
  type: string; // text, email, textarea, select, checkbox, radio, file, etc.
  label: string;
  name: string;
  required?: boolean;
  placeholder?: string;
  options?: string[]; // For select, checkbox, radio
  validation?: Record<string, any>;
}

export interface FormSubmission {
  form_id: number | string;
  fields: Record<string, any>;
  metadata?: {
    ip?: string;
    user_agent?: string;
    referrer?: string;
    submitted_at?: string;
  };
}

// Elementor structures
export interface ElementorElement {
  id: string;
  elType: "section" | "column" | "widget";
  widgetType?: string;
  settings: Record<string, any>;
  elements?: ElementorElement[];
}

export interface ElementorTemplate {
  id: number;
  title: string;
  type: string; // page, section, popup, header, footer, etc.
  data: ElementorElement[];
}
