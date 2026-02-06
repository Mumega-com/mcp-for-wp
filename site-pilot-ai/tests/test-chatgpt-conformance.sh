#!/bin/bash
#
# ChatGPT + MCP conformance checks for Site Pilot AI.
#
# Usage:
#   ./test-chatgpt-conformance.sh <site-url> <api-key>
#   DIGID_API_KEY=spai_xxx ./test-chatgpt-conformance.sh <site-url>
#

set -uo pipefail

SITE_URL="${1:-https://example.com}"
API_KEY="${2:-${DIGID_API_KEY:-}}"
MCP_ENDPOINT="${SITE_URL%/}/wp-json/site-pilot-ai/v1/mcp"

PASS_COUNT=0
FAIL_COUNT=0

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

if ! command -v curl >/dev/null 2>&1; then
	echo "curl is required."
	exit 1
fi

if ! command -v jq >/dev/null 2>&1; then
	echo "jq is required."
	exit 1
fi

if [ -z "${API_KEY}" ]; then
	echo "API key is required."
	echo "Usage: $0 <site-url> <api-key>"
	echo "   or: DIGID_API_KEY=spai_xxx $0 <site-url>"
	exit 1
fi

print_pass() {
	PASS_COUNT=$(( PASS_COUNT + 1 ))
	echo -e "${GREEN}PASS${NC} - $1"
}

print_fail() {
	FAIL_COUNT=$(( FAIL_COUNT + 1 ))
	echo -e "${RED}FAIL${NC} - $1"
	if [ -n "${2:-}" ]; then
		echo "  details: $2"
	fi
}

run_request() {
	local key="$1"
	local payload="$2"

	RESP_HEADERS="$(mktemp)"
	RESP_BODY="$(mktemp)"
	RESP_CODE="$(
		curl -sS -o "${RESP_BODY}" -D "${RESP_HEADERS}" -w "%{http_code}" \
			-X POST "${MCP_ENDPOINT}" \
			-H "Content-Type: application/json" \
			-H "X-API-Key: ${key}" \
			-d "${payload}"
	)"
}

cleanup_response() {
	rm -f "${RESP_HEADERS}" "${RESP_BODY}"
}

echo "=============================================="
echo "Site Pilot AI ChatGPT Conformance Checks"
echo "=============================================="
echo "Endpoint: ${MCP_ENDPOINT}"
echo

# 1) initialize
run_request "${API_KEY}" '{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "initialize",
  "params": {
    "clientInfo": {
      "name": "site-pilot-ai-conformance",
      "version": "1.0.0"
    }
  }
}'
if [ "${RESP_CODE}" = "200" ] && jq -e '.result.protocolVersion and .result.capabilities.tools != null' "${RESP_BODY}" >/dev/null 2>&1; then
	print_pass "initialize returns protocolVersion + capabilities"
else
	print_fail "initialize returns protocolVersion + capabilities" "$(cat "${RESP_BODY}")"
fi
cleanup_response

# 2) tools/list
run_request "${API_KEY}" '{
  "jsonrpc": "2.0",
  "id": 2,
  "method": "tools/list"
}'
if [ "${RESP_CODE}" = "200" ] && jq -e '.result.tools | length > 0' "${RESP_BODY}" >/dev/null 2>&1; then
	print_pass "tools/list returns tools"
else
	print_fail "tools/list returns tools" "$(cat "${RESP_BODY}")"
fi

if jq -e '.result.tools | all(.[]; has("annotations") and (.annotations | has("readOnlyHint")) and (.annotations | has("openWorldHint")) and (.annotations | has("destructiveHint")))' "${RESP_BODY}" >/dev/null 2>&1; then
	print_pass "all tools expose annotation hints"
else
	print_fail "all tools expose annotation hints" "$(cat "${RESP_BODY}")"
fi

if jq -e '.result.tools | map(.name) | index("wp_search") != null and index("wp_fetch") != null' "${RESP_BODY}" >/dev/null 2>&1; then
	print_pass "tools/list includes wp_search and wp_fetch"
else
	print_fail "tools/list includes wp_search and wp_fetch" "$(cat "${RESP_BODY}")"
fi
cleanup_response

# 3) notifications/initialized (no response body expected)
run_request "${API_KEY}" '{
  "jsonrpc": "2.0",
  "method": "notifications/initialized"
}'
if [ "${RESP_CODE}" = "204" ]; then
	print_pass "notifications/initialized returns 204"
else
	print_fail "notifications/initialized returns 204" "status=${RESP_CODE}, body=$(cat "${RESP_BODY}")"
fi
cleanup_response

# 4) Tool error behavior
run_request "${API_KEY}" '{
  "jsonrpc": "2.0",
  "id": 4,
  "method": "tools/call",
  "params": {
    "name": "wp_nonexistent_tool",
    "arguments": {}
  }
}'
if [ "${RESP_CODE}" = "200" ] && jq -e '.error.code == -32602' "${RESP_BODY}" >/dev/null 2>&1; then
	print_pass "unknown tool returns JSON-RPC error -32602"
else
	print_fail "unknown tool returns JSON-RPC error -32602" "$(cat "${RESP_BODY}")"
fi
cleanup_response

# 5) Auth failure behavior
run_request "spai_invalid_key_for_conformance" '{
  "jsonrpc": "2.0",
  "id": 5,
  "method": "ping"
}'
if [ "${RESP_CODE}" = "401" ] || [ "${RESP_CODE}" = "403" ]; then
	print_pass "invalid API key is rejected"
else
	print_fail "invalid API key is rejected" "status=${RESP_CODE}, body=$(cat "${RESP_BODY}")"
fi
cleanup_response

# 6) Rate-limit headers should be present on normal MCP responses
run_request "${API_KEY}" '{
  "jsonrpc": "2.0",
  "id": 6,
  "method": "ping"
}'
if grep -iq '^x-ratelimit-limit:' "${RESP_HEADERS}" && grep -iq '^x-ratelimit-remaining:' "${RESP_HEADERS}"; then
	print_pass "rate-limit headers are present"
else
	print_fail "rate-limit headers are present" "$(cat "${RESP_HEADERS}")"
fi
cleanup_response

echo
echo "----------------------------------------------"
echo "Summary: ${PASS_COUNT} passed, ${FAIL_COUNT} failed"
echo "----------------------------------------------"

if [ "${FAIL_COUNT}" -gt 0 ]; then
	exit 1
fi

exit 0
