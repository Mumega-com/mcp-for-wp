# Demo GIF Script

Record this with asciinema or screen recorder. Target: 60 seconds.

## Setup
```bash
# Use the Docker test site
export KEY="spai_YOUR_TEST_KEY"
export MCP="http://localhost:8080/wp-json/site-pilot-ai/v1/mcp"
```

## Scene 1: One command builds a full page (30 sec)
```bash
# Show the command
echo "Building a landing page with one MCP call..."

curl -s -X POST $MCP \
  -H "X-API-Key: $KEY" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","id":1,"method":"tools/call","params":{"name":"wp_build_page","arguments":{"title":"AI Services Landing","sections":[{"type":"hero","heading":"AI-Powered Solutions","subheading":"Transform your business","button_text":"Get Started","background":"#0F172A"},{"type":"features","columns":3,"heading":"What We Offer","items":[{"icon":"fas fa-robot","title":"AI Automation","desc":"Automate repetitive tasks"},{"icon":"fas fa-chart-line","title":"Analytics","desc":"Data-driven insights"},{"icon":"fas fa-shield-alt","title":"Security","desc":"Enterprise-grade protection"}]},{"type":"cta","heading":"Ready to Start?","button_text":"Contact Us","background":"#1E40AF"}]}}}' | python3 -m json.tool

# Show: Page created with 5 sections, meta_verified: true
```

## Scene 2: Edit one widget (15 sec)
```bash
echo "Changing a heading with a surgical edit..."

curl -s -X POST $MCP \
  -H "X-API-Key: $KEY" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","id":1,"method":"tools/call","params":{"name":"wp_edit_widget","arguments":{"id":PAGE_ID,"widget_id":"WIDGET_ID","settings":{"title_text":"AI-Powered Solutions for 2026"}}}}' | python3 -m json.tool

# Show: success: true, changes: ["set title_text"]
```

## Scene 3: Quick stat (15 sec)
```bash
echo "239 MCP tools. 24 blueprints. Free."
echo ""
echo "Install: wp plugin install https://mumega.com/mcp-updates/mumega-mcp-latest.zip --activate"
echo "GitHub:  https://github.com/Mumega-com/mcp-for-wp"
```

## Recording tips
- Use a dark terminal theme
- Font size 16+
- Terminal width ~100 chars
- Add typing animation if using asciinema
- Cut pauses between scenes
