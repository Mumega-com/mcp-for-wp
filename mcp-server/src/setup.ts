import { createInterface } from 'readline';
import { writeFileSync, mkdirSync, existsSync, readFileSync } from 'fs';
import { homedir } from 'os';
import { join } from 'path';

const rl = createInterface({ input: process.stdin, output: process.stdout });

function ask(question: string): Promise<string> {
  return new Promise(resolve => rl.question(question, resolve));
}

export async function runSetup(): Promise<void> {
  console.log('\n🔧 Site Pilot AI - Setup Wizard\n');

  const url = await ask('WordPress site URL: ');
  const apiKey = await ask('API Key (from WP Admin > Site Pilot AI > Settings): ');
  const name = await ask('Site name (optional, press Enter to skip): ');

  if (!url || !apiKey) {
    console.log('\n❌ URL and API key are required.');
    rl.close();
    process.exit(1);
  }

  // Clean URL
  const cleanUrl = url.replace(/\/+$/, '');
  const siteName = name || 'default';

  const configDir = join(homedir(), '.wp-ai-operator');
  const configPath = join(configDir, 'config.json');

  // Load existing config or create new
  let config: any = {
    sites: {},
    defaultSite: siteName,
    enabledExtensions: ['core', 'seo', 'forms', 'elementor']
  };

  if (existsSync(configPath)) {
    try {
      config = JSON.parse(readFileSync(configPath, 'utf-8'));
    } catch {
      // Invalid config, start fresh
    }
  }

  config.sites[siteName] = {
    url: cleanUrl,
    apiKey: apiKey,
    name: name || cleanUrl.replace(/https?:\/\//, ''),
  };
  config.defaultSite = config.defaultSite || siteName;

  // Save
  if (!existsSync(configDir)) {
    mkdirSync(configDir, { recursive: true });
  }
  writeFileSync(configPath, JSON.stringify(config, null, 2));
  console.log(`\n✅ Config saved to ${configPath}`);

  // Test connection
  console.log('\n🔍 Testing connection...');
  try {
    const response = await fetch(`${cleanUrl}/wp-json/site-pilot-ai/v1/site-info`, {
      headers: { 'X-API-Key': apiKey },
    });
    if (response.ok) {
      const data = await response.json() as any;
      console.log(`✅ Connected! ${data.site_name || 'WordPress'} (v${data.wordpress_version || 'unknown'})`);
      console.log(`   Theme: ${data.theme || 'unknown'}`);
      console.log(`   Plugin: Site Pilot AI v${data.plugin_version || 'unknown'}`);
    } else {
      console.log(`⚠️  Connection failed (HTTP ${response.status}). Check your URL and API key.`);
    }
  } catch (e: any) {
    console.log(`⚠️  Could not connect: ${e.message}`);
    console.log('   Config saved anyway. You can test later with: site-pilot-ai --test');
  }

  console.log('\n📋 Claude Desktop config (add to claude_desktop_config.json):\n');
  console.log(JSON.stringify({
    mcpServers: {
      wordpress: {
        command: 'npx',
        args: ['-y', 'site-pilot-ai'],
        env: {
          WP_URL: cleanUrl,
          WP_API_KEY: apiKey,
        },
      },
    },
  }, null, 2));

  console.log('');
  rl.close();
}
