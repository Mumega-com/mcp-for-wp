# ChatGPT Conformance Suite

This document defines the repeatable checks required before each Site Pilot AI release intended for ChatGPT Developer Mode and public MCP app submission.

## Automated Checks

Run the script:

```bash
cd site-pilot-ai/tests
./test-chatgpt-conformance.sh https://your-site.com spai_your_api_key
```

You can also pass the key through env:

```bash
DIGID_API_KEY=spai_your_api_key ./test-chatgpt-conformance.sh https://your-site.com
```

The script validates:

- `initialize` handshake and capabilities payload
- `tools/list` shape, required annotation hints, and expected tool availability
- notification behavior (`notifications/initialized` should return `204`)
- tool error handling for unknown tools (`-32602`)
- invalid API key rejection (auth failure scenario)
- rate-limit headers on successful MCP responses

## Pass/Fail Output Format

The script prints one line per test case:

- `PASS - <case-name>`
- `FAIL - <case-name>`

Summary line:

- `Summary: <N> passed, <M> failed`

The command must exit non-zero if any checks fail.

## MCP Inspector Manual Matrix

Use MCP Inspector against:

- `https://your-site.com/wp-json/site-pilot-ai/v1/mcp`
- Header: `X-API-Key: spai_...`

Manual checks:

1. Run `initialize`; verify protocol version and `tools` capability.
2. Run `tools/list`; inspect `annotations` on each tool.
3. Run `tools/call` for a read-only tool (`wp_site_info`, `wp_search`, `wp_fetch`).
4. Run `tools/call` for a write tool in a safe sandbox (`wp_create_post` as draft).
5. Run `tools/call` with invalid tool name and verify JSON-RPC error.
6. Send `notifications/initialized` and verify no body response.

## ChatGPT Developer Mode Manual Matrix

In ChatGPT Developer Mode:

1. Add connector using MCP URL and API key.
2. Confirm successful connection and tool discovery.
3. Execute a read-only query (`wp_site_info`).
4. Execute a search + fetch workflow (`wp_search` then `wp_fetch`).
5. Trigger one deliberate tool error (unknown or invalid arguments) and verify safe failure.
6. Confirm response quality: concise, structured payloads and stable fields.

## Release Gate

Before release, all of the following must be true:

- Automated conformance script passes with zero failures.
- MCP Inspector manual matrix is completed.
- ChatGPT Developer Mode manual matrix is completed.
- Any discovered failures are tracked as GitHub issues before publish.
