/**
 * Site Pilot AI - MCP Proxy
 * Forwards JSON-RPC requests from stdio to the PHP MCP endpoint over HTTP.
 */

import type { SiteConfig } from "./config.js";

export interface JsonRpcRequest {
  jsonrpc: "2.0";
  id?: string | number;
  method: string;
  params?: Record<string, any>;
}

export interface JsonRpcResponse {
  jsonrpc: "2.0";
  id?: string | number;
  result?: any;
  error?: { code: number; message: string; data?: any };
}

export class McpProxy {
  private endpoint: string;
  private apiKey: string;

  constructor(site: SiteConfig) {
    const base = site.url.replace(/\/+$/, "");
    this.endpoint = `${base}/wp-json/site-pilot-ai/v1/mcp`;
    this.apiKey = site.apiKey;
  }

  /**
   * Forward a JSON-RPC request to the PHP MCP endpoint.
   */
  async forward(request: JsonRpcRequest): Promise<JsonRpcResponse> {
    const response = await fetch(this.endpoint, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-API-Key": this.apiKey,
      },
      body: JSON.stringify(request),
    });

    if (!response.ok) {
      const text = await response.text().catch(() => "");
      throw new Error(
        `HTTP ${response.status} from WordPress MCP endpoint: ${text.slice(0, 200)}`
      );
    }

    return (await response.json()) as JsonRpcResponse;
  }

  /**
   * Convenience: send a JSON-RPC call and return just the result.
   * Throws on JSON-RPC errors.
   */
  async call(method: string, params?: Record<string, any>): Promise<any> {
    const rpcRequest: JsonRpcRequest = {
      jsonrpc: "2.0",
      id: 1,
      method,
      params: params ?? {},
    };

    const rpcResponse = await this.forward(rpcRequest);

    if (rpcResponse.error) {
      const err = new Error(rpcResponse.error.message);
      (err as any).code = rpcResponse.error.code;
      (err as any).data = rpcResponse.error.data;
      throw err;
    }

    return rpcResponse.result;
  }
}
