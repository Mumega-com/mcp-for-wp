#!/bin/bash
#
# Test script for Site Pilot AI MCP endpoint
#
# Usage: ./test-mcp-endpoint.sh <site-url> <api-key>
#

set -e

# Configuration
SITE_URL="${1:-https://musicalunicornfarm.com}"
API_KEY="${2:-${DIGID_API_KEY}}"
MCP_ENDPOINT="${SITE_URL}/wp-json/site-pilot-ai/v1/mcp"

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Helper function
test_request() {
    local test_name="$1"
    local json_data="$2"

    echo -e "${YELLOW}Testing: ${test_name}${NC}"

    response=$(curl -s -X POST "${MCP_ENDPOINT}" \
        -H "Content-Type: application/json" \
        -H "X-API-Key: ${API_KEY}" \
        -d "${json_data}")

    if echo "$response" | jq -e . >/dev/null 2>&1; then
        if echo "$response" | jq -e '.error' >/dev/null 2>&1; then
            echo -e "${RED}✗ FAILED${NC}"
            echo "$response" | jq '.'
            return 1
        else
            echo -e "${GREEN}✓ PASSED${NC}"
            echo "$response" | jq '.' | head -20
            echo ""
            return 0
        fi
    else
        echo -e "${RED}✗ INVALID JSON${NC}"
        echo "$response"
        return 1
    fi
}

# Check dependencies
if ! command -v curl &> /dev/null; then
    echo "curl is required but not installed. Aborting."
    exit 1
fi

if ! command -v jq &> /dev/null; then
    echo "jq is required but not installed. Aborting."
    exit 1
fi

if [ -z "$API_KEY" ]; then
    echo "Error: API key not provided"
    echo "Usage: $0 <site-url> <api-key>"
    echo "   or: DIGID_API_KEY=xxx $0 <site-url>"
    exit 1
fi

echo "=========================================="
echo "Site Pilot AI MCP Endpoint Tests"
echo "=========================================="
echo "Endpoint: ${MCP_ENDPOINT}"
echo ""

# Test 1: Ping
test_request "Ping" '{
  "jsonrpc": "2.0",
  "method": "ping",
  "id": 1
}'

# Test 2: Initialize
test_request "Initialize" '{
  "jsonrpc": "2.0",
  "method": "initialize",
  "id": 2,
  "params": {
    "clientInfo": {
      "name": "test-script",
      "version": "1.0.0"
    }
  }
}'

# Test 3: Tools List
test_request "Tools List" '{
  "jsonrpc": "2.0",
  "method": "tools/list",
  "id": 3
}'

# Test 4: Site Info Tool
test_request "Tool Call: wp_site_info" '{
  "jsonrpc": "2.0",
  "method": "tools/call",
  "id": 4,
  "params": {
    "name": "wp_site_info",
    "arguments": {}
  }
}'

# Test 5: Analytics Tool
test_request "Tool Call: wp_analytics" '{
  "jsonrpc": "2.0",
  "method": "tools/call",
  "id": 5,
  "params": {
    "name": "wp_analytics",
    "arguments": {
      "days": 7
    }
  }
}'

# Test 6: Detect Plugins Tool
test_request "Tool Call: wp_detect_plugins" '{
  "jsonrpc": "2.0",
  "method": "tools/call",
  "id": 6,
  "params": {
    "name": "wp_detect_plugins",
    "arguments": {}
  }
}'

# Test 7: List Posts Tool
test_request "Tool Call: wp_list_posts" '{
  "jsonrpc": "2.0",
  "method": "tools/call",
  "id": 7,
  "params": {
    "name": "wp_list_posts",
    "arguments": {
      "per_page": 3,
      "status": "publish"
    }
  }
}'

# Test 8: Batch Request
test_request "Batch Request (ping + site_info)" '[
  {
    "jsonrpc": "2.0",
    "method": "ping",
    "id": 8
  },
  {
    "jsonrpc": "2.0",
    "method": "tools/call",
    "id": 9,
    "params": {
      "name": "wp_site_info",
      "arguments": {}
    }
  }
]'

# Test 9: Invalid Method (should error)
test_request "Invalid Method (expect error)" '{
  "jsonrpc": "2.0",
  "method": "nonexistent_method",
  "id": 10
}'

# Test 10: Invalid Tool (should error)
test_request "Invalid Tool (expect error)" '{
  "jsonrpc": "2.0",
  "method": "tools/call",
  "id": 11,
  "params": {
    "name": "wp_nonexistent_tool",
    "arguments": {}
  }
}'

# Test 11: Notification (no response expected)
echo -e "${YELLOW}Testing: Notification (no response)${NC}"
response=$(curl -s -X POST "${MCP_ENDPOINT}" \
    -H "Content-Type: application/json" \
    -H "X-API-Key: ${API_KEY}" \
    -d '{
      "jsonrpc": "2.0",
      "method": "notifications/initialized"
    }')

if [ -z "$response" ] || [ "$response" == "null" ]; then
    echo -e "${GREEN}✓ PASSED (no response as expected)${NC}"
else
    echo -e "${RED}✗ FAILED (received response for notification)${NC}"
    echo "$response"
fi
echo ""

# Test 12: OPTIONS (CORS preflight)
echo -e "${YELLOW}Testing: OPTIONS (CORS preflight)${NC}"
options_response=$(curl -s -X OPTIONS "${MCP_ENDPOINT}" \
    -H "Access-Control-Request-Method: POST" \
    -H "Access-Control-Request-Headers: content-type,x-api-key" \
    -i | grep -i "access-control")

if [ -n "$options_response" ]; then
    echo -e "${GREEN}✓ PASSED${NC}"
    echo "$options_response"
else
    echo -e "${RED}✗ FAILED (no CORS headers)${NC}"
fi
echo ""

echo "=========================================="
echo "Tests Complete"
echo "=========================================="
