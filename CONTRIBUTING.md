# Contributing to mumcp

Thanks for your interest in contributing! mumcp is the most complete MCP server for WordPress — 239 tools and growing.

## Quick Start

1. Fork and clone the repo
2. Start the Docker test site:
   ```bash
   cd wp-test && docker compose up -d
   ```
3. The plugin source is volume-mounted at `site-pilot-ai/` — edits are live instantly
4. Generate a test API key:
   ```bash
   docker exec wp-test-wordpress-1 bash -c 'php -r "
   require_once \"/var/www/html/wp-load.php\";
   \$key = \"spai_\" . bin2hex(random_bytes(24));
   update_option(\"spai_api_key\", wp_hash_password(\$key));
   echo \$key;
   "'
   ```
5. Test via MCP:
   ```bash
   curl -s -X POST http://localhost:8080/wp-json/site-pilot-ai/v1/mcp \
     -H "X-API-Key: spai_YOUR_KEY" \
     -H "Content-Type: application/json" \
     -d '{"jsonrpc":"2.0","id":1,"method":"tools/list","params":{}}'
   ```

## What We Need Help With

- **Widget schemas** — Elementor has hundreds of widgets. Each one needs a schema in `class-spai-elementor-widgets.php` with valid control keys, types, defaults, and examples. Pick a widget, add its schema.
- **Blueprint types** — We have 14 section blueprints (hero, features, cta, etc). Add more: team, portfolio, blog-grid, services, about-us, pricing-comparison.
- **MCP client guides** — Write connection guides for new MCP clients beyond Claude and Cursor.
- **Bug reports** — File issues with steps to reproduce. Include your WordPress version, Elementor version, and the MCP tool call that failed.

## Code Style

- PHP 7.4+ compatible
- WordPress coding standards (tabs, Yoda conditions, etc.)
- All functions/classes prefixed with `spai_` / `Spai_` / `SPAI_`
- Text domain: `site-pilot-ai`
- No `console.log` or `error_log` in production code

## Adding a New MCP Tool

1. Add the REST endpoint in `includes/api/class-spai-rest-*.php`
2. Add the MCP tool definition in `includes/mcp/class-spai-mcp-free-tools.php` (or `pro-tools.php`)
3. Map it to a category in `get_tool_categories()`
4. Map it to a route in the dispatch table at the bottom of the file
5. Test with `dry_run: true` if applicable

## Pull Requests

- One feature or fix per PR
- Include the issue number if applicable
- Test on the Docker site before submitting
- We review fast — most PRs get feedback within 24 hours

## License

By contributing, you agree that your contributions will be licensed under GPL v2 or later.
