#!/bin/bash
# Test script for the batch endpoint
# Usage: ./test-batch-endpoint.sh <site-url> <api-key>

SITE_URL="${1:-https://musicalunicornfarm.com}"
API_KEY="${2:-$DIGID_API_KEY}"

if [ -z "$API_KEY" ]; then
    echo "Error: API key required"
    echo "Usage: $0 <site-url> <api-key>"
    exit 1
fi

echo "Testing Site Pilot AI Batch Endpoint"
echo "====================================="
echo "Site: $SITE_URL"
echo ""

# Test 1: Simple batch with 2 operations
echo "Test 1: Batch with 2 operations (site-info + list posts)"
echo "---------------------------------------------------------"
curl -s -X POST "$SITE_URL/wp-json/site-pilot-ai/v1/batch" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "operations": [
      {
        "method": "GET",
        "path": "/site-info"
      },
      {
        "method": "GET",
        "path": "/posts",
        "body": {
          "per_page": 5
        }
      }
    ]
  }' | jq '.'

echo ""
echo ""

# Test 2: Create multiple posts in batch
echo "Test 2: Create 3 draft posts in batch"
echo "--------------------------------------"
curl -s -X POST "$SITE_URL/wp-json/site-pilot-ai/v1/batch" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "operations": [
      {
        "method": "POST",
        "path": "/posts",
        "body": {
          "title": "Batch Test Post 1",
          "content": "<p>This post was created via batch endpoint.</p>",
          "status": "draft"
        }
      },
      {
        "method": "POST",
        "path": "/posts",
        "body": {
          "title": "Batch Test Post 2",
          "content": "<p>This is another batch-created post.</p>",
          "status": "draft"
        }
      },
      {
        "method": "POST",
        "path": "/posts",
        "body": {
          "title": "Batch Test Post 3",
          "content": "<p>Third post from batch operation.</p>",
          "status": "draft"
        }
      }
    ]
  }' | jq '.'

echo ""
echo ""

# Test 3: Batch size limit (should fail)
echo "Test 3: Batch with 26 operations (should fail - max 25)"
echo "-------------------------------------------------------"
OPERATIONS='{"operations":['
for i in {1..26}; do
    OPERATIONS+="{\"method\":\"GET\",\"path\":\"/site-info\"}"
    if [ $i -lt 26 ]; then
        OPERATIONS+=","
    fi
done
OPERATIONS+=']}'

curl -s -X POST "$SITE_URL/wp-json/site-pilot-ai/v1/batch" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d "$OPERATIONS" | jq '.'

echo ""
echo ""

# Test 4: Mixed operations with error handling
echo "Test 4: Mixed operations (valid + invalid)"
echo "------------------------------------------"
curl -s -X POST "$SITE_URL/wp-json/site-pilot-ai/v1/batch" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "operations": [
      {
        "method": "GET",
        "path": "/site-info"
      },
      {
        "method": "GET",
        "path": "/posts/999999"
      },
      {
        "method": "GET",
        "path": "/pages",
        "body": {
          "per_page": 3
        }
      }
    ]
  }' | jq '.'

echo ""
echo ""
echo "Tests complete!"
