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

const VERSION = "3.0.0";

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

  const baseUrl = site.url.replace(/\/+$/, "");
  console.log(`🔍 Testing connection to ${baseUrl}...`);
  try {
    const response = await fetch(
      `${baseUrl}/wp-json/site-pilot-ai/v1/site-info`,
      { headers: { "X-API-Key": site.apiKey } }
    );
    if (response.ok) {
      const data = (await response.json()) as any;
      const cap = data.capabilities || {};
      console.log(`✅ Connected! ${data.name} (WordPress ${data.wp_version})`);
      console.log(`   Plugin:      Site Pilot AI v${data.plugin?.version}`);
      console.log(`   Theme:       ${data.theme?.name || "unknown"} ${data.theme?.version || ""}`);
      console.log(`   Plan:        ${cap.plan || "free"}${cap.pro_active ? " (Pro active)" : ""}`);
      console.log(`   Elementor:   ${cap.elementor ? "yes" : "no"}${cap.elementor_pro ? " + Pro" : ""}${cap.elementor_layout_mode ? ` (${cap.elementor_layout_mode})` : ""}`);
      const extras: string[] = [];
      if (cap.woocommerce) extras.push("WooCommerce");
      if (cap.learnpress) extras.push("LearnPress");
      if (cap.rankmath) extras.push("RankMath");
      if (cap.yoast) extras.push("Yoast");
      if (cap.aioseo) extras.push("AIOSEO");
      if (cap.cf7) extras.push("CF7");
      if (cap.wpforms) extras.push("WPForms");
      if (extras.length) console.log(`   Integrations: ${extras.join(", ")}`);
      // Count tools
      try {
        const toolsRes = await fetch(`${baseUrl}/wp-json/site-pilot-ai/v1/mcp`, {
          method: "POST",
          headers: { "Content-Type": "application/json", "X-API-Key": site.apiKey },
          body: JSON.stringify({ jsonrpc: "2.0", method: "tools/list", id: 1 }),
        });
        if (toolsRes.ok) {
          const toolsData = (await toolsRes.json()) as any;
          const tools = toolsData?.result?.tools ?? [];
          console.log(`   MCP Tools:   ${tools.length} available`);
        }
      } catch {}
    } else {
      console.log(`❌ HTTP ${response.status}: Check your API key`);
      if (response.status === 401) console.log("   Regenerate your API key in WP Admin > Site Pilot AI");
      if (response.status === 404) console.log("   Make sure the Site Pilot AI plugin is activated");
    }
  } catch (e: any) {
    console.log(`❌ Connection failed: ${e.message}`);
    console.log("   Check that your WordPress site is accessible");
  }
  process.exit(0);
}

// ─── MCP Server (proxy mode) ────────────────────────────────────────

const config = loadConfig();
const site = getActiveSite(config);
const proxy = new McpProxy(site);

// Derive server name: "sitepilotai-<sitename>" (slug from site name, key, or URL hostname).
function deriveServerName(site: { name?: string; _key: string; url: string }): string {
  const raw = site.name || (site._key !== "default" ? site._key : "");
  if (raw) {
    const slug = raw.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, "");
    if (slug) return `sitepilotai-${slug}`;
  }
  // Fallback: use hostname from URL.
  try {
    const hostname = new URL(site.url).hostname.replace(/\./g, "-");
    return `sitepilotai-${hostname}`;
  } catch {
    return "sitepilotai";
  }
}

const serverName = deriveServerName(site);

const server = new Server(
  { name: serverName, version: VERSION },
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
    log("info", `Site Pilot AI MCP Server v${VERSION} running as "${serverName}" (proxy mode)`);
    log("info", `Proxying to: ${site.url}`);
  } catch (error: any) {
    console.error("Failed to start server:", error.message);
    process.exit(1);
  }
}

main();
