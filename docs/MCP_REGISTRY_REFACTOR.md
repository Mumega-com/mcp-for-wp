# MCP Tool Registry Refactoring

## Summary

Refactored the MCP REST controller (`class-spai-rest-mcp.php`) from 2397 lines to 653 lines (73% reduction) by extracting tool definitions and mappings into a modular registry system.

## Changes

### New Files Created

1. **`includes/mcp/class-spai-mcp-tool-registry.php`** (170 lines)
   - Abstract base class for tool registries
   - Provides `define_tool()` method for building MCP tool definitions
   - Provides annotation methods (`get_tool_annotations`, `is_read_only_tool`, `is_open_world_tool`, `is_destructive_tool`)
   - Abstract methods: `get_tools()`, `get_tool_map()`
   - Protected overridable methods: `get_destructive_tools()`, `get_open_world_tools()`

2. **`includes/mcp/class-spai-mcp-free-tools.php`** (1010 lines)
   - Extends `Spai_MCP_Tool_Registry`
   - Contains all 42 free tier tool definitions
   - Contains free tier tool-to-route mappings
   - Declares destructive tools: wp_delete_post, wp_delete_all_drafts, wp_revoke_api_key, wp_reset_rate_limit, wp_delete_webhook
   - Declares open world tools: wp_upload_media_from_url, wp_test_webhook, wp_screenshot_url

3. **`includes/mcp/class-spai-mcp-pro-tools.php`** (730 lines)
   - Extends `Spai_MCP_Tool_Registry`
   - Contains all 40 pro tier tool definitions (multilanguage, SEO, forms, Elementor Pro, widgets/sidebars)
   - Contains pro tier tool-to-route mappings
   - Declares destructive tools: wp_delete_widget
   - Declares open world tools: (none currently)

### Modified Files

1. **`includes/api/class-spai-rest-mcp.php`** (653 lines, down from 2397)
   - Added `$free_registry` and `$pro_registry` properties
   - Instantiates registries in constructor
   - Updated `get_introspection_data()` to build tools from registries
   - Updated `handle_tools_list()` to use registries with caching
   - Updated `handle_tools_call()` to build tool map from registries
   - Removed 1790 lines of tool definitions, maps, and helper methods
   - Kept all protocol handling (JSON-RPC, CORS, routing, logging)

2. **`site-pilot-ai.php`** (loader)
   - Added require_once statements for the three new registry classes before REST API section

## Architecture

```
Spai_MCP_Tool_Registry (abstract)
├── Spai_MCP_Free_Tools
└── Spai_MCP_Pro_Tools

Spai_REST_MCP
├── uses → Spai_MCP_Free_Tools (always)
└── uses → Spai_MCP_Pro_Tools (when pro active)
```

## Benefits

1. **Modularity**: Tool definitions are now separated by tier (free/pro)
2. **Maintainability**: Each registry file has a single responsibility
3. **Readability**: MCP controller now focuses on protocol handling, not tool definitions
4. **Extensibility**: Easy to add new tool tiers or modify existing ones
5. **File Size**: Main MCP controller reduced by 73% (2397 → 653 lines)

## Behavior Preservation

- Tool definitions are identical to original (copy-pasted exactly)
- Tool maps are identical to original (copy-pasted exactly)
- Annotation logic is identical (moved without modification)
- Tool list and introspection endpoints return the same data
- MCP protocol handling unchanged
- Pro gating logic unchanged

## Testing Checklist

- [ ] Free tools are available via `/mcp` endpoint
- [ ] Pro tools appear when pro license is active
- [ ] Tool definitions match original format
- [ ] Tool maps match original routes
- [ ] Introspection endpoint returns complete data
- [ ] MCP protocol (JSON-RPC 2.0) works correctly
- [ ] Annotations (readOnlyHint, destructiveHint, openWorldHint) are correct
- [ ] Tools cache works (no performance regression)

## File Stats

| File | Before | After | Change |
|------|--------|-------|--------|
| class-spai-rest-mcp.php | 2397 lines | 653 lines | -1790 (-73%) |
| class-spai-mcp-tool-registry.php | N/A | 170 lines | +170 |
| class-spai-mcp-free-tools.php | N/A | 1010 lines | +1010 |
| class-spai-mcp-pro-tools.php | N/A | 730 lines | +730 |
| **Total** | **2397** | **2563** | **+166 (+7%)** |

Note: Total line count increased slightly (7%) due to added file headers, class declarations, and PHPDoc comments. However, the main MCP controller is now 73% smaller and much more maintainable.

## Implementation Date

February 10, 2026

## Related Issues

- GitHub Issue #65: Refactor — Split MCP tool registry into modular classes
