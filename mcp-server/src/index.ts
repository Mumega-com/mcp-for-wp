#!/usr/bin/env node
/**
 * WP AI Operator - MCP Server
 * Microkernel architecture for WordPress control via AI
 *
 * Features:
 * - Pluggable extension system
 * - Multi-site support
 * - Built-in extensions: Core, SEO, Forms, Elementor
 */

import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
  ListResourcesRequestSchema,
  ReadResourceRequestSchema,
} from "@modelcontextprotocol/sdk/types.js";

import { createKernel, WPKernel } from "./kernel/index.js";
import { createAllExtensions } from "./extensions/index.js";
import { runSetup } from './setup.js';

// CLI argument handling
const args = process.argv.slice(2);

if (args.includes('--version') || args.includes('-v')) {
  console.log('site-pilot-ai v2.0.0');
  process.exit(0);
}

if (args.includes('--help') || args.includes('-h')) {
  console.log(`
site-pilot-ai - MCP Server for WordPress

Usage:
  site-pilot-ai              Start MCP server (stdio transport)
  site-pilot-ai --setup      Interactive setup wizard
  site-pilot-ai --test       Test WordPress connection
  site-pilot-ai --version    Show version

Environment Variables:
  WP_URL        WordPress site URL
  WP_API_KEY    Site Pilot AI API key
  WP_SITE_NAME  Site name (for multi-site configs)

Config File:
  ~/.wp-ai-operator/config.json

Documentation:
  https://github.com/nickalot/site-pilot-ai
`);
  process.exit(0);
}

if (args.includes('--setup')) {
  await runSetup();
  process.exit(0);
}

if (args.includes('--test')) {
  const url = process.env.WP_URL;
  const key = process.env.WP_API_KEY;

  if (!url || !key) {
    // Try loading from config
    const { readFileSync, existsSync } = await import('fs');
    const { homedir } = await import('os');
    const { join } = await import('path');
    const configPath = join(homedir(), '.wp-ai-operator', 'config.json');

    if (existsSync(configPath)) {
      const config = JSON.parse(readFileSync(configPath, 'utf-8'));
      const siteName = process.env.WP_SITE_NAME || config.defaultSite || Object.keys(config.sites)[0];
      const site = config.sites[siteName];
      if (site) {
        process.env.WP_URL = site.url;
        process.env.WP_API_KEY = site.apiKey;
      }
    }
  }

  const testUrl = process.env.WP_URL;
  const testKey = process.env.WP_API_KEY;

  if (!testUrl || !testKey) {
    console.log('❌ No configuration found. Run: site-pilot-ai --setup');
    process.exit(1);
  }

  console.log(`🔍 Testing connection to ${testUrl}...`);
  try {
    const response = await fetch(`${testUrl}/wp-json/site-pilot-ai/v1/site-info`, {
      headers: { 'X-API-Key': testKey },
    });
    if (response.ok) {
      const data = await response.json() as any;
      console.log(`✅ Connected! ${data.site_name} (WordPress ${data.wordpress_version})`);
      console.log(`   Plugin: Site Pilot AI v${data.plugin_version}`);
      console.log(`   Posts: ${data.post_count}, Pages: ${data.page_count}`);
    } else {
      console.log(`❌ HTTP ${response.status}: Check your API key`);
    }
  } catch (e: any) {
    console.log(`❌ Connection failed: ${e.message}`);
  }
  process.exit(0);
}

// Initialize MCP server
const server = new Server(
  {
    name: "wp-ai-operator",
    version: "2.0.0",
  },
  {
    capabilities: {
      tools: {},
      resources: {},
    },
  }
);

// Global kernel instance
let kernel: WPKernel;

// Initialize kernel and load extensions
async function initializeKernel(): Promise<void> {
  kernel = createKernel();

  // Load all built-in extensions
  const extensions = createAllExtensions();
  for (const ext of extensions) {
    try {
      await kernel.loadExtension(ext);
    } catch (error: any) {
      kernel.log("error", `Failed to load extension ${ext.metadata.name}: ${error.message}`);
    }
  }

  kernel.log("info", `Kernel initialized with ${kernel.getLoadedExtensions().length} extensions`);
}

// List all available tools from all extensions
server.setRequestHandler(ListToolsRequestSchema, async () => {
  const tools = kernel.getAllTools();
  return { tools };
});

// Handle tool calls - route to appropriate extension
server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name, arguments: args } = request.params;

  try {
    const result = await kernel.callTool(name, args || {});
    return {
      content: [
        {
          type: "text",
          text: JSON.stringify(result, null, 2),
        },
      ],
    };
  } catch (error: any) {
    kernel.log("error", `Tool error: ${name}`, error.message);
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

// Resources for site info and extension status
server.setRequestHandler(ListResourcesRequestSchema, async () => {
  return {
    resources: [
      {
        uri: "wp://site-info",
        name: "WordPress Site Info",
        description: "Current site configuration and stats",
        mimeType: "application/json",
      },
      {
        uri: "wp://extensions",
        name: "Loaded Extensions",
        description: "List of loaded extensions and their tools",
        mimeType: "application/json",
      },
      {
        uri: "wp://config",
        name: "Configuration",
        description: "Current kernel configuration",
        mimeType: "application/json",
      },
    ],
  };
});

server.setRequestHandler(ReadResourceRequestSchema, async (request) => {
  const { uri } = request.params;

  try {
    let content: any;

    switch (uri) {
      case "wp://site-info":
        content = await kernel.request("GET", "site-info");
        break;

      case "wp://extensions":
        content = kernel.getLoadedExtensions().map((ext) => ({
          name: ext.metadata.name,
          version: ext.metadata.version,
          description: ext.metadata.description,
          tools: ext.getTools().map((t) => t.name),
          wpPlugins: ext.metadata.wpPlugins || [],
        }));
        break;

      case "wp://config":
        const config = kernel.getConfig();
        content = {
          sites: Object.keys(config.sites).map((name) => ({
            name,
            url: config.sites[name].url,
          })),
          defaultSite: config.defaultSite,
          enabledExtensions: config.enabledExtensions,
        };
        break;

      default:
        throw new Error(`Unknown resource: ${uri}`);
    }

    return {
      contents: [
        {
          uri,
          mimeType: "application/json",
          text: JSON.stringify(content, null, 2),
        },
      ],
    };
  } catch (error: any) {
    throw new Error(`Failed to read resource ${uri}: ${error.message}`);
  }
});

// Start server
async function main() {
  try {
    await initializeKernel();

    const transport = new StdioServerTransport();
    await server.connect(transport);

    kernel.log("info", "WP AI Operator MCP Server v2.0 running");
    kernel.log("info", `Tools available: ${kernel.getAllTools().length}`);
  } catch (error: any) {
    console.error("Failed to start server:", error.message);
    process.exit(1);
  }
}

main();
