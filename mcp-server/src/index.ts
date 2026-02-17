#!/usr/bin/env node
/**
 * Site Pilot AI - MCP Server (Proxy Mode)
 *
 * Thin stdio-to-HTTP proxy: forwards all MCP requests to the PHP plugin's
 * /wp-json/site-pilot-ai/v1/mcp endpoint. Tools are always in sync with
 * the WordPress plugin — zero local tool definitions needed.
 */

import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
  ListResourcesRequestSchema,
  ReadResourceRequestSchema,
} from "@modelcontextprotocol/sdk/types.js";

import { loadConfig, getActiveSite } from "./config.js";
import { McpProxy } from "./proxy.js";
import { runSetup } from "./setup.js";

const VERSION = "2.1.0";

function log(level: string, message: string, data?: any): void {
  const ts = new Date().toISOString();
  if (data !== undefined) {
    console.error(`[${ts}] [${level}] ${message}`, data);
  } else {
    console.error(`[${ts}] [${level}] ${message}`);
  }
}

// ─── CLI argument handling ───────────────────────────────────────────

const args = process.argv.slice(2);

if (args.includes("--version") || args.includes("-v")) {
  console.log(`site-pilot-ai v${VERSION}`);
  process.exit(0);
}

if (args.includes("--help") || args.includes("-h")) {
  console.log(`
site-pilot-ai - MCP Server for WordPress (proxy mode)

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
  https://github.com/Digidinc/wp-ai-operator
`);
  process.exit(0);
}

if (args.includes("--setup")) {
  await runSetup();
  process.exit(0);
}

if (args.includes("--test")) {
  // Load config (env vars + file)
  const config = loadConfig();
  let site;
  try {
    site = getActiveSite(config);
  } catch {
    console.log("❌ No configuration found. Run: site-pilot-ai --setup");
    process.exit(1);
  }

  console.log(`🔍 Testing connection to ${site.url}...`);
  try {
    const response = await fetch(
      `${site.url.replace(/\/+$/, "")}/wp-json/site-pilot-ai/v1/site-info`,
      { headers: { "X-API-Key": site.apiKey } }
    );
    if (response.ok) {
      const data = (await response.json()) as any;
      console.log(
        `✅ Connected! ${data.name} (WordPress ${data.wp_version})`
      );
      console.log(`   Plugin: Site Pilot AI v${data.plugin?.version}`);
      console.log(`   Elementor: ${data.capabilities?.elementor ? "yes" : "no"}, Pro: ${data.capabilities?.elementor_pro ? "yes" : "no"}`);
    } else {
      console.log(`❌ HTTP ${response.status}: Check your API key`);
    }
  } catch (e: any) {
    console.log(`❌ Connection failed: ${e.message}`);
  }
  process.exit(0);
}

// ─── MCP Server (proxy mode) ────────────────────────────────────────

const config = loadConfig();
const site = getActiveSite(config);
const proxy = new McpProxy(site);

const server = new Server(
  { name: "site-pilot-ai", version: VERSION },
  { capabilities: { tools: {}, resources: {} } }
);

// tools/list → proxy
server.setRequestHandler(ListToolsRequestSchema, async () => {
  try {
    const result = await proxy.call("tools/list");
    return { tools: result?.tools ?? [] };
  } catch (error: any) {
    log("error", "tools/list failed", error.message);
    return { tools: [] };
  }
});

// tools/call → proxy
server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name, arguments: toolArgs } = request.params;

  try {
    const result = await proxy.call("tools/call", { name, arguments: toolArgs ?? {} });
    // The PHP endpoint returns { content: [...] } or a raw result
    if (result?.content) {
      return result;
    }
    return {
      content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
    };
  } catch (error: any) {
    log("error", `tools/call ${name} failed`, error.message);
    return {
      content: [{ type: "text", text: `Error: ${error.message}` }],
      isError: true,
    };
  }
});

// resources/list → proxy
server.setRequestHandler(ListResourcesRequestSchema, async () => {
  try {
    const result = await proxy.call("resources/list");
    return { resources: result?.resources ?? [] };
  } catch (error: any) {
    log("error", "resources/list failed", error.message);
    return { resources: [] };
  }
});

// resources/read → proxy
server.setRequestHandler(ReadResourceRequestSchema, async (request) => {
  const { uri } = request.params;

  try {
    const result = await proxy.call("resources/read", { uri });
    if (result?.contents) {
      return result;
    }
    return {
      contents: [
        { uri, mimeType: "application/json", text: JSON.stringify(result, null, 2) },
      ],
    };
  } catch (error: any) {
    throw new Error(`Failed to read resource ${uri}: ${error.message}`);
  }
});

// ─── Start ───────────────────────────────────────────────────────────

async function main() {
  try {
    const transport = new StdioServerTransport();
    await server.connect(transport);
    log("info", `Site Pilot AI MCP Server v${VERSION} running (proxy mode)`);
    log("info", `Proxying to: ${site.url}`);
  } catch (error: any) {
    console.error("Failed to start server:", error.message);
    process.exit(1);
  }
}

main();
