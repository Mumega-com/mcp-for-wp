/**
 * WP AI Gateway - Cloudflare Worker
 *
 * Smart proxy + cache layer for WordPress API requests
 * Part of wp-ai-operator by DigID Inc
 */

import { handleMcp } from './mcp-handler.js';

interface Env {
  WP_CACHE: KVNamespace;
  WP_DB: D1Database;
  AUTHORIZED_TOKENS: string;
  SITE_CONFIGS: string;
  ENVIRONMENT: string;
}

interface SiteConfig {
  id: string;
  name: string;
  url: string;
  apiKey: string;
}

interface CacheEntry {
  data: any;
  timestamp: number;
  ttl: number;
}

interface ActivityLog {
  site_id: string;
  endpoint: string;
  method: string;
  status: number;
  cached: boolean;
  duration_ms: number;
}

// Cache TTL configuration (seconds)
const CACHE_TTL: Record<string, number> = {
  'site-info': 3600,        // 1 hour
  'plugins': 3600,          // 1 hour
  'templates': 86400,       // 24 hours
  'globals': 43200,         // 12 hours
  'seo/plugin': 3600,       // 1 hour
  'forms/plugins': 3600,    // 1 hour
  'posts': 300,             // 5 minutes
  'pages': 300,             // 5 minutes
  'default': 60,            // 1 minute
};

export default {
  async fetch(request: Request, env: Env, ctx: ExecutionContext): Promise<Response> {
    const url = new URL(request.url);

    // CORS headers
    const corsHeaders = {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
      'Access-Control-Allow-Headers': 'Authorization, Content-Type, X-Site-ID, Mcp-Session-Id',
    };

    // Handle preflight
    if (request.method === 'OPTIONS') {
      return new Response(null, { headers: corsHeaders });
    }

    // MCP endpoint - no auth required (handles its own protocol)
    if (url.pathname === '/mcp') {
      // Get default site config for MCP
      const siteConfigs = getSiteConfigs(env);
      const defaultSite = siteConfigs['default'] || Object.values(siteConfigs)[0];

      if (!defaultSite) {
        return jsonResponse(
          { error: 'No site configured. Set SITE_CONFIGS environment variable.' },
          503,
          corsHeaders
        );
      }

      return handleMcp(request, {
        url: defaultSite.url,
        apiKey: defaultSite.apiKey,
      });
    }

    // Auth check for all other routes
    const authHeader = request.headers.get('Authorization');
    const authorizedTokens = (env.AUTHORIZED_TOKENS || '').split(',').map(t => t.trim());

    if (!authHeader || !authorizedTokens.includes(authHeader.replace('Bearer ', ''))) {
      return jsonResponse({ error: 'Unauthorized' }, 401, corsHeaders);
    }

    try {
      // Route handling
      if (url.pathname === '/health') {
        return jsonResponse({ status: 'ok', timestamp: Date.now() }, 200, corsHeaders);
      }

      if (url.pathname === '/sites') {
        return handleListSites(env, corsHeaders);
      }

      if (url.pathname.startsWith('/proxy/')) {
        return handleProxy(request, env, ctx, corsHeaders);
      }

      if (url.pathname === '/batch') {
        return handleBatch(request, env, ctx, corsHeaders);
      }

      if (url.pathname.startsWith('/cache/')) {
        return handleCache(request, env, corsHeaders);
      }

      if (url.pathname === '/analytics') {
        return handleAnalytics(request, env, corsHeaders);
      }

      return jsonResponse({ error: 'Not found', path: url.pathname }, 404, corsHeaders);
    } catch (error) {
      console.error('Worker error:', error);
      return jsonResponse({
        error: 'Internal server error',
        message: error instanceof Error ? error.message : 'Unknown error'
      }, 500, corsHeaders);
    }
  },
};

// Parse site configurations
function getSiteConfigs(env: Env): Record<string, SiteConfig> {
  try {
    return JSON.parse(env.SITE_CONFIGS || '{}');
  } catch {
    return {};
  }
}

// Get specific site config
function getSiteConfig(env: Env, siteId: string): SiteConfig | null {
  const configs = getSiteConfigs(env);
  return configs[siteId] || null;
}

// JSON response helper
function jsonResponse(data: any, status: number = 200, headers: Record<string, string> = {}): Response {
  return new Response(JSON.stringify(data), {
    status,
    headers: {
      'Content-Type': 'application/json',
      ...headers,
    },
  });
}

// List configured sites
async function handleListSites(env: Env, corsHeaders: Record<string, string>): Promise<Response> {
  const configs = getSiteConfigs(env);
  const sites = Object.entries(configs).map(([id, config]) => ({
    id,
    name: config.name,
    url: config.url,
  }));
  return jsonResponse({ sites }, 200, corsHeaders);
}

// Proxy requests to WordPress
async function handleProxy(
  request: Request,
  env: Env,
  ctx: ExecutionContext,
  corsHeaders: Record<string, string>
): Promise<Response> {
  const url = new URL(request.url);
  const pathParts = url.pathname.split('/').filter(Boolean);

  // /proxy/{siteId}/{endpoint...}
  if (pathParts.length < 2) {
    return jsonResponse({ error: 'Invalid path. Use /proxy/{siteId}/{endpoint}' }, 400, corsHeaders);
  }

  const siteId = pathParts[1];
  const wpEndpoint = '/' + pathParts.slice(2).join('/');

  const siteConfig = getSiteConfig(env, siteId);
  if (!siteConfig) {
    return jsonResponse({ error: `Site not found: ${siteId}` }, 404, corsHeaders);
  }

  const startTime = Date.now();

  // Cache key
  const cacheKey = `${siteId}:${wpEndpoint}:${url.search}`;

  // Check cache for GET requests
  if (request.method === 'GET') {
    const cached = await env.WP_CACHE.get<CacheEntry>(cacheKey, 'json');
    if (cached && (Date.now() - cached.timestamp < cached.ttl * 1000)) {
      const duration = Date.now() - startTime;

      // Log cache hit (fire and forget)
      ctx.waitUntil(logActivity(env, {
        site_id: siteId,
        endpoint: wpEndpoint,
        method: 'GET',
        status: 200,
        cached: true,
        duration_ms: duration,
      }));

      return jsonResponse(cached.data, 200, {
        ...corsHeaders,
        'X-Cache': 'HIT',
        'X-Cache-Age': String(Math.floor((Date.now() - cached.timestamp) / 1000)),
      });
    }
  }

  // Build WordPress request
  const wpUrl = `${siteConfig.url}/wp-json/site-pilot-ai/v1${wpEndpoint}${url.search}`;

  const wpHeaders: HeadersInit = {
    'X-API-Key': siteConfig.apiKey,
    'Content-Type': 'application/json',
  };

  const wpOptions: RequestInit = {
    method: request.method,
    headers: wpHeaders,
  };

  // Include body for non-GET requests
  if (request.method !== 'GET' && request.method !== 'HEAD') {
    const body = await request.text();
    if (body) {
      wpOptions.body = body;
    }
  }

  // Make WordPress request
  const wpResponse = await fetch(wpUrl, wpOptions);
  const responseData = await wpResponse.json();
  const duration = Date.now() - startTime;

  // Cache successful GET responses
  if (request.method === 'GET' && wpResponse.ok) {
    const ttl = getCacheTTL(wpEndpoint);
    const cacheEntry: CacheEntry = {
      data: responseData,
      timestamp: Date.now(),
      ttl,
    };

    ctx.waitUntil(env.WP_CACHE.put(cacheKey, JSON.stringify(cacheEntry), {
      expirationTtl: ttl,
    }));
  }

  // Log activity (fire and forget)
  ctx.waitUntil(logActivity(env, {
    site_id: siteId,
    endpoint: wpEndpoint,
    method: request.method,
    status: wpResponse.status,
    cached: false,
    duration_ms: duration,
  }));

  return jsonResponse(responseData, wpResponse.status, {
    ...corsHeaders,
    'X-Cache': 'MISS',
    'X-Response-Time': `${duration}ms`,
  });
}

// Batch operations
async function handleBatch(
  request: Request,
  env: Env,
  ctx: ExecutionContext,
  corsHeaders: Record<string, string>
): Promise<Response> {
  if (request.method !== 'POST') {
    return jsonResponse({ error: 'Method not allowed' }, 405, corsHeaders);
  }

  const { operations } = await request.json() as {
    operations: Array<{
      site: string;
      endpoint: string;
      method?: string;
      body?: any;
    }>
  };

  if (!Array.isArray(operations) || operations.length === 0) {
    return jsonResponse({ error: 'Invalid operations array' }, 400, corsHeaders);
  }

  // Execute all operations in parallel
  const results = await Promise.all(
    operations.map(async (op, index) => {
      try {
        const siteConfig = getSiteConfig(env, op.site);
        if (!siteConfig) {
          return { index, success: false, error: `Site not found: ${op.site}` };
        }

        const wpUrl = `${siteConfig.url}/wp-json/site-pilot-ai/v1${op.endpoint}`;
        const response = await fetch(wpUrl, {
          method: op.method || 'GET',
          headers: {
            'X-API-Key': siteConfig.apiKey,
            'Content-Type': 'application/json',
          },
          body: op.body ? JSON.stringify(op.body) : undefined,
        });

        const data = await response.json();
        return {
          index,
          site: op.site,
          endpoint: op.endpoint,
          success: response.ok,
          status: response.status,
          data
        };
      } catch (error) {
        return {
          index,
          site: op.site,
          success: false,
          error: error instanceof Error ? error.message : 'Unknown error'
        };
      }
    })
  );

  return jsonResponse({ results }, 200, corsHeaders);
}

// Cache management
async function handleCache(
  request: Request,
  env: Env,
  corsHeaders: Record<string, string>
): Promise<Response> {
  const url = new URL(request.url);
  const pathParts = url.pathname.split('/').filter(Boolean);

  // DELETE /cache/{siteId} - Clear site cache
  // DELETE /cache/{siteId}/{pattern} - Clear matching keys
  if (request.method === 'DELETE') {
    const siteId = pathParts[1];
    const pattern = pathParts.slice(2).join('/') || '*';

    // Note: KV doesn't support pattern deletion, but we can track keys in D1
    // For now, just acknowledge the request
    return jsonResponse({
      message: `Cache invalidation requested for ${siteId}:${pattern}`,
      note: 'Entries will expire based on TTL'
    }, 200, corsHeaders);
  }

  // GET /cache/stats
  if (request.method === 'GET' && pathParts[1] === 'stats') {
    // Get cache stats from D1
    const stats = await env.WP_DB.prepare(`
      SELECT
        site_id,
        COUNT(*) as total_requests,
        SUM(CASE WHEN cached = 1 THEN 1 ELSE 0 END) as cache_hits,
        AVG(duration_ms) as avg_duration_ms
      FROM activity_log
      WHERE created_at > unixepoch() - 86400
      GROUP BY site_id
    `).all();

    return jsonResponse({ stats: stats.results }, 200, corsHeaders);
  }

  return jsonResponse({ error: 'Invalid cache operation' }, 400, corsHeaders);
}

// Analytics endpoint
async function handleAnalytics(
  request: Request,
  env: Env,
  corsHeaders: Record<string, string>
): Promise<Response> {
  const url = new URL(request.url);
  const days = parseInt(url.searchParams.get('days') || '7');
  const siteId = url.searchParams.get('site');

  let query = `
    SELECT
      site_id,
      endpoint,
      method,
      COUNT(*) as request_count,
      SUM(CASE WHEN cached = 1 THEN 1 ELSE 0 END) as cache_hits,
      AVG(duration_ms) as avg_duration_ms,
      MIN(duration_ms) as min_duration_ms,
      MAX(duration_ms) as max_duration_ms
    FROM activity_log
    WHERE created_at > unixepoch() - ?
  `;

  const params: any[] = [days * 86400];

  if (siteId) {
    query += ' AND site_id = ?';
    params.push(siteId);
  }

  query += ' GROUP BY site_id, endpoint, method ORDER BY request_count DESC LIMIT 100';

  const results = await env.WP_DB.prepare(query).bind(...params).all();

  return jsonResponse({
    period_days: days,
    site_filter: siteId || 'all',
    analytics: results.results
  }, 200, corsHeaders);
}

// Determine cache TTL based on endpoint
function getCacheTTL(endpoint: string): number {
  const normalized = endpoint.replace(/^\//, '');

  for (const [pattern, ttl] of Object.entries(CACHE_TTL)) {
    if (normalized.startsWith(pattern) || normalized === pattern) {
      return ttl;
    }
  }

  return CACHE_TTL.default;
}

// Log activity to D1
async function logActivity(env: Env, log: ActivityLog): Promise<void> {
  try {
    await env.WP_DB.prepare(`
      INSERT INTO activity_log (site_id, endpoint, method, status, cached, duration_ms, created_at)
      VALUES (?, ?, ?, ?, ?, ?, unixepoch())
    `).bind(
      log.site_id,
      log.endpoint,
      log.method,
      log.status,
      log.cached ? 1 : 0,
      log.duration_ms
    ).run();
  } catch (error) {
    console.error('Failed to log activity:', error);
  }
}
