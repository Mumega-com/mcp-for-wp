#!/usr/bin/env bash
#
# Build a WordPress.org-compliant zip of Mumega MCP for WordPress.
#
# All features are free — no Pro stripping needed.
# Just applies .distignore and creates a clean zip.
#
# Usage:
#   bash scripts/build-wporg.sh
#   bash scripts/build-wporg.sh --version 1.7.0

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
PLUGIN_SLUG="site-pilot-ai"
WPORG_NAME="mumega-mcp"

# Parse version from plugin header if not supplied
VERSION=""
while [[ $# -gt 0 ]]; do
    case "$1" in
        --version) VERSION="$2"; shift 2 ;;
        *) echo "Unknown option: $1"; exit 1 ;;
    esac
done

if [[ -z "$VERSION" ]]; then
    VERSION=$(grep -m1 "Version:" "$PLUGIN_DIR/$PLUGIN_SLUG.php" | sed 's/.*Version:[[:space:]]*//' | tr -d '[:space:]')
fi

if [[ -z "$VERSION" ]]; then
    echo "Error: Could not determine version."
    exit 1
fi

echo "==> Building $WPORG_NAME v$VERSION for WordPress.org"

# Create temp build directory
BUILD_DIR=$(mktemp -d)
DEST="$BUILD_DIR/$PLUGIN_SLUG"
trap 'rm -rf "$BUILD_DIR"' EXIT

# Copy plugin tree
echo "    Copying plugin files..."
cp -a "$PLUGIN_DIR" "$DEST"

# ── Strip WP.org directory assets (uploaded separately to SVN assets/) ──
rm -f "$DEST"/assets/banner-*.png
rm -f "$DEST"/assets/icon-*.png
rm -f "$DEST"/assets/screenshot-*.png
rm -f "$DEST"/assets/icon.svg

# ── Remove Freemius SDK (no longer used) ──
rm -rf "$DEST/freemius"
rm -f  "$DEST/includes/freemius-init.php"

# ── Apply .distignore exclusions ────────────────────────────────
echo "    Applying .distignore..."
if [[ -f "$DEST/.distignore" ]]; then
    while IFS= read -r pattern; do
        # Skip empty lines and comments
        [[ -z "$pattern" || "$pattern" == \#* ]] && continue
        # Remove leading slash for find compatibility
        pattern="${pattern#/}"
        # Use find to match and remove
        find "$DEST" -path "$DEST/$pattern" -exec rm -rf {} + 2>/dev/null || true
    done < "$DEST/.distignore"
    rm -f "$DEST/.distignore"
fi

# Remove build scripts from output (scripts/ is already in .distignore)
rm -rf "$DEST/scripts"

# ── Build zip ───────────────────────────────────────────────────
OUTPUT_DIR="$PLUGIN_DIR/scripts"
OUTPUT_ZIP="$OUTPUT_DIR/$WPORG_NAME-$VERSION.zip"

echo "    Creating zip..."
cd "$BUILD_DIR"
zip -qr "$OUTPUT_ZIP" "$PLUGIN_SLUG/"

echo ""
echo "==> Done: $OUTPUT_ZIP"
echo ""

# Sanity checks
echo "    Sanity checks:"

if unzip -l "$OUTPUT_ZIP" | grep -q "freemius/"; then
    echo "    [FAIL] Freemius SDK found in zip!"
    exit 1
else
    echo "    [OK] No Freemius SDK"
fi

FILE_COUNT=$(unzip -l "$OUTPUT_ZIP" | tail -1 | awk '{print $2}')
echo "    [INFO] $FILE_COUNT files in zip"
echo ""
echo "Ready to submit to WordPress.org!"
