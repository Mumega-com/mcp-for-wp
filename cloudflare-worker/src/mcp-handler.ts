/**
 * MCP Streamable HTTP Handler
 *
 * Implements MCP (Model Context Protocol) Streamable HTTP transport
 * for direct connection from Claude Desktop and other MCP clients.
 *
 * Protocol: JSON-RPC 2.0
 * Methods: initialize, tools/list, tools/call, notifications/initialized, ping
 */

import { TOOLS, TOOL_MAP, type ToolDefinition } from './tools.js';

// JSON-RPC 2.0 request structure
interface JsonRpcRequest {
  jsonrpc: '2.0';
  id?: string | number | null;
  method: string;
  params?: any;
}

// JSON-RPC 2.0 response structure
interface JsonRpcResponse {
  jsonrpc: '2.0';
  id?: string | number | null;
  result?: any;
  error?: {
    code: number;
    message: string;
    data?: any;
  };
}

// Site configuration
interface SiteConfig {
  url: string;
  apiKey: string;
}

// JSON-RPC error codes
const ErrorCode = {
  PARSE_ERROR: -32700,
  INVALID_REQUEST: -32600,
  METHOD_NOT_FOUND: -32601,
  INVALID_PARAMS: -32602,
  INTERNAL_ERROR: -32603,
};

/**
 * Main MCP request handler
 */
export async function handleMcp(
  request: Request,
  siteConfig: SiteConfig,
): Promise<Response> {
  // CORS headers for all responses
  const corsHeaders = {
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Methods': 'POST, GET, OPTIONS',
    'Access-Control-Allow-Headers': 'Content-Type, Authorization, Mcp-Session-Id',
  };

  // Handle preflight OPTIONS
  if (request.method === 'OPTIONS') {
    return new Response(null, { status: 204, headers: corsHeaders });
  }

  // Only accept POST for MCP
  if (request.method !== 'POST') {
    return jsonRpcResponse(
      null,
      undefined,
      {
        code: ErrorCode.INVALID_REQUEST,
        message: 'MCP requires POST method',
      },
      corsHeaders
    );
  }

  // Parse request body
  let body: any;
  try {
    const text = await request.text();
    body = JSON.parse(text);
  } catch (error) {
    return jsonRpcResponse(
      null,
      undefined,
      {
        code: ErrorCode.PARSE_ERROR,
        message: 'Invalid JSON',
      },
      corsHeaders
    );
  }

  // Handle batched requests (array of JSON-RPC requests)
  if (Array.isArray(body)) {
    const responses = await Promise.all(
      body.map((req) => handleSingleRequest(req, siteConfig))
    );
    return new Response(JSON.stringify(responses), {
      status: 200,
      headers: {
        'Content-Type': 'application/json',
        ...corsHeaders,
      },
    });
  }

  // Handle single request
  const response = await handleSingleRequest(body, siteConfig);
  return new Response(JSON.stringify(response), {
    status: 200,
    headers: {
      'Content-Type': 'application/json',
      ...corsHeaders,
    },
  });
}

/**
 * Handle a single JSON-RPC request
 */
async function handleSingleRequest(
  req: JsonRpcRequest,
  siteConfig: SiteConfig
): Promise<JsonRpcResponse> {
  // Validate JSON-RPC structure
  if (!req || req.jsonrpc !== '2.0' || typeof req.method !== 'string') {
    return {
      jsonrpc: '2.0',
      id: req?.id ?? null,
      error: {
        code: ErrorCode.INVALID_REQUEST,
        message: 'Invalid JSON-RPC request',
      },
    };
  }

  const { method, params, id } = req;

  try {
    // Route to method handlers
    switch (method) {
      case 'initialize':
        return {
          jsonrpc: '2.0',
          id,
          result: handleInitialize(params),
        };

      case 'notifications/initialized':
        // Client confirms initialization, we just acknowledge
        return {
          jsonrpc: '2.0',
          id,
          result: {},
        };

      case 'tools/list':
        return {
          jsonrpc: '2.0',
          id,
          result: handleToolsList(),
        };

      case 'tools/call':
        const toolResult = await handleToolsCall(params, siteConfig);
        return {
          jsonrpc: '2.0',
          id,
          result: toolResult,
        };

      case 'ping':
        return {
          jsonrpc: '2.0',
          id,
          result: { pong: true, timestamp: Date.now() },
        };

      default:
        return {
          jsonrpc: '2.0',
          id,
          error: {
            code: ErrorCode.METHOD_NOT_FOUND,
            message: `Method not found: ${method}`,
          },
        };
    }
  } catch (error) {
    console.error('MCP handler error:', error);
    return {
      jsonrpc: '2.0',
      id,
      error: {
        code: ErrorCode.INTERNAL_ERROR,
        message: error instanceof Error ? error.message : 'Internal error',
        data: error instanceof Error ? error.stack : undefined,
      },
    };
  }
}

/**
 * Handle initialize method
 */
function handleInitialize(params: any) {
  return {
    protocolVersion: '2024-11-05',
    capabilities: {
      tools: {
        listChanged: false, // Tools are static
      },
    },
    serverInfo: {
      name: 'wp-ai-operator-mcp',
      version: '1.0.0',
      vendor: 'DigID Inc',
      description: 'MCP server for WordPress REST API operations',
    },
  };
}

/**
 * Handle tools/list method
 */
function handleToolsList() {
  return {
    tools: TOOLS,
  };
}

/**
 * Handle tools/call method
 * Translates MCP tool call to WordPress REST API request
 */
async function handleToolsCall(params: any, siteConfig: SiteConfig) {
  if (!params || typeof params.name !== 'string') {
    throw new Error('Invalid tool call: missing name');
  }

  const { name, arguments: args = {} } = params;

  // Look up tool mapping
  const toolMapping = TOOL_MAP[name];
  if (!toolMapping) {
    throw new Error(`Unknown tool: ${name}`);
  }

  const { method, endpoint, bodyParams = [] } = toolMapping;

  // Build REST API URL by substituting {id}, {widget_id}, etc.
  let apiEndpoint = endpoint;
  const queryParams: Record<string, string> = {};
  const bodyData: Record<string, any> = {};

  // Substitute path parameters and separate body vs query params
  for (const [key, value] of Object.entries(args)) {
    const placeholder = `{${key}}`;

    if (apiEndpoint.includes(placeholder)) {
      // Path parameter - only substitute if value is provided
      if (value !== undefined && value !== null) {
        apiEndpoint = apiEndpoint.replace(placeholder, String(value));
      } else {
        // Remove the placeholder and any trailing slash if optional parameter is not provided
        apiEndpoint = apiEndpoint.replace(`/${placeholder}`, '').replace(placeholder, '');
      }
    } else if (bodyParams.includes(key)) {
      // Body parameter
      bodyData[key] = value;
    } else {
      // Query parameter
      if (value !== undefined && value !== null) {
        queryParams[key] = String(value);
      }
    }
  }

  // Build full URL
  const queryString = Object.keys(queryParams).length > 0
    ? '?' + new URLSearchParams(queryParams).toString()
    : '';

  const wpUrl = `${siteConfig.url}/wp-json/site-pilot-ai/v1/${apiEndpoint}${queryString}`;

  // Build request options
  const wpOptions: RequestInit = {
    method,
    headers: {
      'X-API-Key': siteConfig.apiKey,
      'Content-Type': 'application/json',
    },
  };

  // Add body for non-GET requests
  if (method !== 'GET' && method !== 'HEAD' && Object.keys(bodyData).length > 0) {
    wpOptions.body = JSON.stringify(bodyData);
  }

  // Make WordPress API request
  const startTime = Date.now();
  const wpResponse = await fetch(wpUrl, wpOptions);
  const duration = Date.now() - startTime;

  // Parse response
  let responseData: any;
  const contentType = wpResponse.headers.get('content-type') || '';

  if (contentType.includes('application/json')) {
    responseData = await wpResponse.json();
  } else {
    responseData = await wpResponse.text();
  }

  // Check if WordPress returned an error
  if (!wpResponse.ok) {
    return {
      content: [
        {
          type: 'text',
          text: `WordPress API error (${wpResponse.status}): ${JSON.stringify(responseData, null, 2)}`,
        },
      ],
      isError: true,
    };
  }

  // Return successful result
  return {
    content: [
      {
        type: 'text',
        text: typeof responseData === 'string'
          ? responseData
          : JSON.stringify(responseData, null, 2),
      },
    ],
    metadata: {
      duration_ms: duration,
      status: wpResponse.status,
      endpoint: apiEndpoint,
    },
  };
}

/**
 * Helper to create JSON-RPC response
 */
function jsonRpcResponse(
  id: string | number | null | undefined,
  result?: any,
  error?: { code: number; message: string; data?: any },
  headers: Record<string, string> = {}
): Response {
  const response: JsonRpcResponse = {
    jsonrpc: '2.0',
    id: id ?? null,
  };

  if (error) {
    response.error = error;
  } else {
    response.result = result;
  }

  return new Response(JSON.stringify(response), {
    status: 200,
    headers: {
      'Content-Type': 'application/json',
      ...headers,
    },
  });
}
