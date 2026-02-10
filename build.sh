#!/usr/bin/env bash
#
# Build script for Site Pilot AI WordPress plugin
#
# Creates two distribution zips:
#   1. site-pilot-ai-free.zip - Free version (without includes/pro/)
#   2. site-pilot-ai.zip - Premium version (with includes/pro/)
#
# Both zips exclude patterns defined in .distignore
#
# Usage: ./build.sh

set -e  # Exit on error

# Color output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Directories
REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_DIR="${REPO_ROOT}/site-pilot-ai"
DIST_DIR="${REPO_ROOT}/dist"
BUILD_DIR="${REPO_ROOT}/.build-tmp"

# Plugin main file
PLUGIN_FILE="${PLUGIN_DIR}/site-pilot-ai.php"

# Distignore file
DISTIGNORE="${REPO_ROOT}/.distignore"

echo -e "${GREEN}=== Site Pilot AI Build Script ===${NC}"
echo ""

# Step 1: Extract version from plugin main file
echo -e "${YELLOW}[1/6] Extracting version from ${PLUGIN_FILE}...${NC}"
if [ ! -f "$PLUGIN_FILE" ]; then
    echo -e "${RED}Error: Plugin file not found: ${PLUGIN_FILE}${NC}"
    exit 1
fi

VERSION=$(grep -m 1 "Version:" "$PLUGIN_FILE" | sed -E 's/.*Version:\s*([0-9.]+).*/\1/')

if [ -z "$VERSION" ]; then
    echo -e "${RED}Error: Could not extract version from ${PLUGIN_FILE}${NC}"
    exit 1
fi

echo -e "${GREEN}Version: ${VERSION}${NC}"
echo ""

# Step 2: Create dist directory
echo -e "${YELLOW}[2/6] Creating dist directory...${NC}"
mkdir -p "$DIST_DIR"
echo -e "${GREEN}Created: ${DIST_DIR}${NC}"
echo ""

# Step 3: Clean up any previous build temp directory
echo -e "${YELLOW}[3/6] Cleaning up previous builds...${NC}"
rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR"
echo -e "${GREEN}Cleaned${NC}"
echo ""

# Function to copy plugin files excluding patterns from .distignore
copy_plugin_files() {
    local src="$1"
    local dest="$2"
    local exclude_pro="$3"  # "yes" or "no"

    echo "  Copying files from ${src} to ${dest}..."

    # Copy the entire plugin directory
    cp -r "$src" "$dest"

    # Remove files/directories matching .distignore patterns
    if [ -f "$DISTIGNORE" ]; then
        echo "  Applying .distignore exclusions..."

        # Read .distignore line by line
        while IFS= read -r pattern; do
            # Skip empty lines and comments
            [[ -z "$pattern" || "$pattern" =~ ^[[:space:]]*# ]] && continue

            # Handle negation patterns (starting with !)
            if [[ "$pattern" =~ ^! ]]; then
                continue  # Skip negation patterns in removal phase
            fi

            # Remove trailing slashes for consistency
            pattern="${pattern%/}"

            # Find and remove matching files/directories inside the copied plugin
            if [[ "$pattern" == *"*"* ]]; then
                # Pattern with wildcard
                find "$dest/site-pilot-ai" -name "${pattern}" -exec rm -rf {} + 2>/dev/null || true
            else
                # Exact path pattern - check if it exists relative to plugin root
                if [ -e "$dest/site-pilot-ai/$pattern" ]; then
                    rm -rf "$dest/site-pilot-ai/$pattern"
                fi
            fi
        done < "$DISTIGNORE"
    fi

    # Remove pro directory if building free version
    if [ "$exclude_pro" = "yes" ]; then
        echo "  Removing includes/pro/ directory (free version)..."
        rm -rf "$dest/site-pilot-ai/includes/pro"
    fi
}

# Step 4: Build premium version (with pro)
echo -e "${YELLOW}[4/6] Building premium version (site-pilot-ai.zip)...${NC}"
PREMIUM_BUILD_DIR="${BUILD_DIR}/premium"
mkdir -p "$PREMIUM_BUILD_DIR"
copy_plugin_files "$PLUGIN_DIR" "$PREMIUM_BUILD_DIR" "no"

# Create premium zip
PREMIUM_ZIP="${DIST_DIR}/site-pilot-ai.zip"
cd "$PREMIUM_BUILD_DIR"
zip -r -q "$PREMIUM_ZIP" site-pilot-ai/
cd "$REPO_ROOT"

echo -e "${GREEN}Created: ${PREMIUM_ZIP}${NC}"
echo ""

# Step 5: Build free version (without pro)
echo -e "${YELLOW}[5/6] Building free version (site-pilot-ai-free.zip)...${NC}"
FREE_BUILD_DIR="${BUILD_DIR}/free"
mkdir -p "$FREE_BUILD_DIR"
copy_plugin_files "$PLUGIN_DIR" "$FREE_BUILD_DIR" "yes"

# Create free zip
FREE_ZIP="${DIST_DIR}/site-pilot-ai-free.zip"
cd "$FREE_BUILD_DIR"
zip -r -q "$FREE_ZIP" site-pilot-ai/
cd "$REPO_ROOT"

echo -e "${GREEN}Created: ${FREE_ZIP}${NC}"
echo ""

# Step 6: Clean up build temp directory
echo -e "${YELLOW}[6/6] Cleaning up temporary files...${NC}"
rm -rf "$BUILD_DIR"
echo -e "${GREEN}Cleaned${NC}"
echo ""

# Summary
echo -e "${GREEN}=== Build Complete ===${NC}"
echo ""
echo "Version: ${VERSION}"
echo ""
echo "Output files:"
echo "  Premium: ${PREMIUM_ZIP}"
echo "  Free:    ${FREE_ZIP}"
echo ""

# Show file sizes
if command -v du &> /dev/null; then
    PREMIUM_SIZE=$(du -h "$PREMIUM_ZIP" | cut -f1)
    FREE_SIZE=$(du -h "$FREE_ZIP" | cut -f1)
    echo "File sizes:"
    echo "  Premium: ${PREMIUM_SIZE}"
    echo "  Free:    ${FREE_SIZE}"
    echo ""
fi

echo -e "${GREEN}Done!${NC}"
