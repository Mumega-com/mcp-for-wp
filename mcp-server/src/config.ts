/**
 * Site Pilot AI - Configuration Loader
 * Loads site config from environment variables and/or config file
 */

import { existsSync, readFileSync } from "fs";
import { homedir } from "os";
import { join } from "path";

export interface SiteConfig {
  url: string;
  apiKey: string;
  name?: string;
}

export interface Config {
  sites: Record<string, SiteConfig>;
  defaultSite?: string;
}

/**
 * Load configuration from environment variables and config file.
 * Priority: env vars > config file values for the "default" site.
 */
export function loadConfig(): Config {
  const config: Config = { sites: {} };

  // 1. Load from config file
  const configPath =
    process.env.WP_CONFIG_PATH ||
    join(homedir(), ".wp-ai-operator", "config.json");

  if (existsSync(configPath)) {
    try {
      const fileConfig = JSON.parse(readFileSync(configPath, "utf-8"));
      if (fileConfig.sites) {
        Object.assign(config.sites, fileConfig.sites);
      }
      if (fileConfig.defaultSite) {
        config.defaultSite = fileConfig.defaultSite;
      }
    } catch (error) {
      // Ignore invalid config file
    }
  }

  // 2. Environment variables override (highest priority)
  if (process.env.WP_URL && process.env.WP_API_KEY) {
    config.sites["default"] = {
      url: process.env.WP_URL,
      apiKey: process.env.WP_API_KEY,
      name: process.env.WP_SITE_NAME || "Default Site",
    };
    config.defaultSite = "default";
  }

  return config;
}

/**
 * Get the active site config (first match: defaultSite > first site).
 * Throws if no sites configured.
 */
export function getActiveSite(config: Config): SiteConfig & { _key: string } {
  const siteKey =
    config.defaultSite || Object.keys(config.sites)[0];

  if (!siteKey || !config.sites[siteKey]) {
    throw new Error(
      "No WordPress sites configured. Set WP_URL and WP_API_KEY environment variables, run site-pilot-ai --setup, or create ~/.wp-ai-operator/config.json"
    );
  }

  return { ...config.sites[siteKey], _key: siteKey };
}
