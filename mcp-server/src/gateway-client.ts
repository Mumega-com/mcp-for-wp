/**
 * Cloudflare Gateway Client
 *
 * Routes WordPress API requests through the Cloudflare Worker
 * for caching, logging, and edge optimization.
 */

export interface GatewayConfig {
  url: string;
  token: string;
  enabled: boolean;
}

export interface GatewayResponse<T = any> {
  data: T;
  cached: boolean;
  cacheAge?: number;
  responseTime?: number;
}

export interface BatchOperation {
  site: string;
  endpoint: string;
  method?: string;
  body?: any;
}

export interface BatchResult {
  index: number;
  site: string;
  endpoint?: string;
  success: boolean;
  status?: number;
  data?: any;
  error?: string;
}

export class GatewayClient {
  private config: GatewayConfig;

  constructor(config?: Partial<GatewayConfig>) {
    this.config = {
      url: config?.url || process.env.GATEWAY_URL || 'https://wp-ai-gateway.digid.workers.dev',
      token: config?.token || process.env.GATEWAY_TOKEN || '',
      enabled: config?.enabled ?? (process.env.USE_GATEWAY === 'true'),
    };
  }

  /**
   * Check if gateway is enabled and configured
   */
  isEnabled(): boolean {
    return this.config.enabled && !!this.config.token;
  }

  /**
   * Fetch from WordPress through the gateway
   */
  async fetch<T = any>(
    siteId: string,
    endpoint: string,
    options?: {
      method?: string;
      body?: any;
      bypassCache?: boolean;
    }
  ): Promise<GatewayResponse<T>> {
    if (!this.isEnabled()) {
      throw new Error('Gateway not enabled. Set USE_GATEWAY=true and GATEWAY_TOKEN');
    }

    const url = `${this.config.url}/proxy/${siteId}${endpoint}`;

    const headers: Record<string, string> = {
      'Authorization': `Bearer ${this.config.token}`,
      'Content-Type': 'application/json',
    };

    if (options?.bypassCache) {
      headers['Cache-Control'] = 'no-cache';
    }

    const startTime = Date.now();

    const response = await fetch(url, {
      method: options?.method || 'GET',
      headers,
      body: options?.body ? JSON.stringify(options.body) : undefined,
    });

    const responseTime = Date.now() - startTime;

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      throw new Error(
        `Gateway error: ${response.status} - ${JSON.stringify(errorData)}`
      );
    }

    const data = await response.json();
    const cached = response.headers.get('X-Cache') === 'HIT';
    const cacheAge = parseInt(response.headers.get('X-Cache-Age') || '0');

    return {
      data,
      cached,
      cacheAge: cached ? cacheAge : undefined,
      responseTime,
    };
  }

  /**
   * Execute multiple operations in parallel
   */
  async batch(operations: BatchOperation[]): Promise<BatchResult[]> {
    if (!this.isEnabled()) {
      throw new Error('Gateway not enabled');
    }

    const response = await fetch(`${this.config.url}/batch`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${this.config.token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ operations }),
    });

    if (!response.ok) {
      throw new Error(`Batch request failed: ${response.status}`);
    }

    const { results } = await response.json();
    return results;
  }

  /**
   * Get list of configured sites
   */
  async listSites(): Promise<Array<{ id: string; name: string; url: string }>> {
    const response = await fetch(`${this.config.url}/sites`, {
      headers: {
        'Authorization': `Bearer ${this.config.token}`,
      },
    });

    if (!response.ok) {
      throw new Error(`Failed to list sites: ${response.status}`);
    }

    const { sites } = await response.json();
    return sites;
  }

  /**
   * Get gateway analytics
   */
  async getAnalytics(options?: {
    days?: number;
    siteId?: string;
  }): Promise<any> {
    const params = new URLSearchParams();
    if (options?.days) params.set('days', String(options.days));
    if (options?.siteId) params.set('site', options.siteId);

    const response = await fetch(
      `${this.config.url}/analytics?${params.toString()}`,
      {
        headers: {
          'Authorization': `Bearer ${this.config.token}`,
        },
      }
    );

    if (!response.ok) {
      throw new Error(`Failed to get analytics: ${response.status}`);
    }

    return response.json();
  }

  /**
   * Get cache statistics
   */
  async getCacheStats(): Promise<any> {
    const response = await fetch(`${this.config.url}/cache/stats`, {
      headers: {
        'Authorization': `Bearer ${this.config.token}`,
      },
    });

    if (!response.ok) {
      throw new Error(`Failed to get cache stats: ${response.status}`);
    }

    return response.json();
  }

  /**
   * Health check
   */
  async health(): Promise<{ status: string; timestamp: number }> {
    const response = await fetch(`${this.config.url}/health`, {
      headers: {
        'Authorization': `Bearer ${this.config.token}`,
      },
    });

    return response.json();
  }
}

/**
 * Create gateway client from environment
 */
export function createGatewayClient(): GatewayClient | null {
  if (process.env.USE_GATEWAY === 'true' && process.env.GATEWAY_TOKEN) {
    return new GatewayClient({
      url: process.env.GATEWAY_URL,
      token: process.env.GATEWAY_TOKEN,
      enabled: true,
    });
  }
  return null;
}
