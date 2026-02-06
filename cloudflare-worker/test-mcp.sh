#!/bin/bash
# Test script for MCP Streamable HTTP endpoint

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
WORKER_URL="${WORKER_URL:-http://localhost:8787}"
MCP_ENDPOINT="${WORKER_URL}/mcp"

echo -e "${YELLOW}Testing MCP endpoint: ${MCP_ENDPOINT}${NC}\n"

# Test 1: Initialize
echo -e "${YELLOW}Test 1: Initialize${NC}"
RESPONSE=$(curl -s -X POST "${MCP_ENDPOINT}" \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "id": 1,
    "method": "initialize",
    "params": {
      "protocolVersion": "2024-11-05",
      "capabilities": {},
      "clientInfo": {
        "name": "test-client",
        "version": "1.0.0"
      }
    }
  }')

if echo "$RESPONSE" | jq -e '.result.serverInfo.name' > /dev/null 2>&1; then
  SERVER_NAME=$(echo "$RESPONSE" | jq -r '.result.serverInfo.name')
  echo -e "${GREEN}✓ Initialize successful. Server: ${SERVER_NAME}${NC}"
else
  echo -e "${RED}✗ Initialize failed${NC}"
  echo "$RESPONSE" | jq .
  exit 1
fi

# Test 2: Tools List
echo -e "\n${YELLOW}Test 2: List Tools${NC}"
RESPONSE=$(curl -s -X POST "${MCP_ENDPOINT}" \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "id": 2,
    "method": "tools/list",
    "params": {}
  }')

if echo "$RESPONSE" | jq -e '.result.tools' > /dev/null 2>&1; then
  TOOL_COUNT=$(echo "$RESPONSE" | jq '.result.tools | length')
  echo -e "${GREEN}✓ Tools list retrieved. Total: ${TOOL_COUNT} tools${NC}"

  # Show first few tools
  echo -e "\nSample tools:"
  echo "$RESPONSE" | jq -r '.result.tools[0:3][] | "  - \(.name): \(.description)"'
else
  echo -e "${RED}✗ Tools list failed${NC}"
  echo "$RESPONSE" | jq .
  exit 1
fi

# Test 3: Ping
echo -e "\n${YELLOW}Test 3: Ping${NC}"
RESPONSE=$(curl -s -X POST "${MCP_ENDPOINT}" \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "id": 3,
    "method": "ping"
  }')

if echo "$RESPONSE" | jq -e '.result.pong' > /dev/null 2>&1; then
  echo -e "${GREEN}✓ Ping successful${NC}"
else
  echo -e "${RED}✗ Ping failed${NC}"
  echo "$RESPONSE" | jq .
  exit 1
fi

# Test 4: Invalid Method
echo -e "\n${YELLOW}Test 4: Invalid Method (should fail gracefully)${NC}"
RESPONSE=$(curl -s -X POST "${MCP_ENDPOINT}" \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "id": 4,
    "method": "nonexistent/method"
  }')

if echo "$RESPONSE" | jq -e '.error.code' > /dev/null 2>&1; then
  ERROR_CODE=$(echo "$RESPONSE" | jq -r '.error.code')
  ERROR_MSG=$(echo "$RESPONSE" | jq -r '.error.message')
  echo -e "${GREEN}✓ Error handling works. Code: ${ERROR_CODE}, Message: ${ERROR_MSG}${NC}"
else
  echo -e "${RED}✗ Error handling test failed${NC}"
  echo "$RESPONSE" | jq .
  exit 1
fi

# Test 5: Batch Request
echo -e "\n${YELLOW}Test 5: Batch Request${NC}"
RESPONSE=$(curl -s -X POST "${MCP_ENDPOINT}" \
  -H "Content-Type: application/json" \
  -d '[
    {"jsonrpc": "2.0", "id": 5, "method": "ping"},
    {"jsonrpc": "2.0", "id": 6, "method": "tools/list"}
  ]')

if echo "$RESPONSE" | jq -e '.[0].result.pong' > /dev/null 2>&1 && \
   echo "$RESPONSE" | jq -e '.[1].result.tools' > /dev/null 2>&1; then
  echo -e "${GREEN}✓ Batch request successful${NC}"
else
  echo -e "${RED}✗ Batch request failed${NC}"
  echo "$RESPONSE" | jq .
  exit 1
fi

# Test 6: OPTIONS (CORS preflight)
echo -e "\n${YELLOW}Test 6: CORS Preflight${NC}"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X OPTIONS "${MCP_ENDPOINT}" \
  -H "Origin: https://example.com" \
  -H "Access-Control-Request-Method: POST")

if [ "$HTTP_CODE" = "204" ]; then
  echo -e "${GREEN}✓ CORS preflight successful (204)${NC}"
else
  echo -e "${RED}✗ CORS preflight failed. HTTP: ${HTTP_CODE}${NC}"
  exit 1
fi

# Test 7: Tool Call (if WordPress is configured)
echo -e "\n${YELLOW}Test 7: Tool Call (wp_site_info)${NC}"
RESPONSE=$(curl -s -X POST "${MCP_ENDPOINT}" \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "id": 7,
    "method": "tools/call",
    "params": {
      "name": "wp_site_info",
      "arguments": {}
    }
  }')

if echo "$RESPONSE" | jq -e '.result.content' > /dev/null 2>&1; then
  echo -e "${GREEN}✓ Tool call successful${NC}"
  echo -e "\nResponse preview:"
  echo "$RESPONSE" | jq -r '.result.content[0].text' | head -5
elif echo "$RESPONSE" | jq -e '.error' > /dev/null 2>&1; then
  ERROR_MSG=$(echo "$RESPONSE" | jq -r '.error.message')
  echo -e "${YELLOW}⚠ Tool call returned error (expected if no site configured): ${ERROR_MSG}${NC}"
else
  echo -e "${RED}✗ Tool call unexpected response${NC}"
  echo "$RESPONSE" | jq .
fi

# Summary
echo -e "\n${GREEN}========================================${NC}"
echo -e "${GREEN}All core MCP protocol tests passed!${NC}"
echo -e "${GREEN}========================================${NC}"

echo -e "\n${YELLOW}Next steps:${NC}"
echo "1. Configure SITE_CONFIGS in your worker"
echo "2. Test actual WordPress operations"
echo "3. Add to Claude Desktop config"
echo "4. Deploy to production"
