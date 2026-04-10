#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN_DIR="$ROOT_DIR/site-pilot-ai"
PLUGIN_MAIN="$PLUGIN_DIR/site-pilot-ai.php"
README_FILE="$PLUGIN_DIR/readme.txt"
MANIFEST_FILE="$ROOT_DIR/version.json"
BUILD_SCRIPT="$PLUGIN_DIR/scripts/build-wporg.sh"
STATIC_DIR="/var/www/mcp-updates"
STATIC_ZIP="$STATIC_DIR/mumega-mcp-latest.zip"
STATIC_MANIFEST="$STATIC_DIR/version.json"
WORKER_FILE="${SPAI_WORKER_FILE:-$HOME/projects/sitepilotai/spai-updates-worker/src/index.js}"

BUILD=0
DEPLOY_WORKER=0
VERIFY_ONLY=0

usage() {
	cat <<'EOF'
Usage:
  scripts/publish_update_release.sh [options]

Options:
  --build          Build the ZIP before publishing
  --deploy-worker  Also sync the legacy worker source file (optional)
  --verify-only    Run consistency and live artifact checks without publishing
  -h, --help       Show help

Notes:
  - The canonical updater source is https://mumega.com/mcp-updates/version.json
  - The worker is optional and no longer part of the critical path
EOF
}

require_cmd() {
	command -v "$1" >/dev/null 2>&1 || {
		echo "Missing required command: $1" >&2
		exit 1
	}
}

while [[ $# -gt 0 ]]; do
	case "$1" in
	--build)
		BUILD=1
		shift
		;;
	--deploy-worker)
		DEPLOY_WORKER=1
		shift
		;;
	--verify-only)
		VERIFY_ONLY=1
		shift
		;;
	-h | --help)
		usage
		exit 0
		;;
	*)
		echo "Unknown argument: $1" >&2
		usage
		exit 1
		;;
	esac
done

require_cmd python3
require_cmd curl
require_cmd unzip

PLUGIN_VERSION="$(grep -m1 "Version:" "$PLUGIN_MAIN" | sed 's/.*Version:[[:space:]]*//' | tr -d '[:space:]')"
DEFINE_VERSION="$(grep -E "^define\( 'SPAI_VERSION'" "$PLUGIN_MAIN" | sed -E "s/.*'([0-9.]+)'.*/\1/")"
README_VERSION="$(grep -m1 '^Stable tag:' "$README_FILE" | sed 's/Stable tag:[[:space:]]*//')"
MANIFEST_VERSION="$(python3 - "$MANIFEST_FILE" <<'PY'
import json, sys
with open(sys.argv[1], 'r', encoding='utf-8') as fh:
    data = json.load(fh)
print(data["version"])
PY
)"

if [[ "$PLUGIN_VERSION" != "$DEFINE_VERSION" || "$PLUGIN_VERSION" != "$README_VERSION" || "$PLUGIN_VERSION" != "$MANIFEST_VERSION" ]]; then
	echo "Version mismatch detected:" >&2
	echo "  plugin header: $PLUGIN_VERSION" >&2
	echo "  SPAI_VERSION:  $DEFINE_VERSION" >&2
	echo "  readme tag:    $README_VERSION" >&2
	echo "  version.json:  $MANIFEST_VERSION" >&2
	exit 1
fi

VERSION="$PLUGIN_VERSION"
ZIP_PATH="$PLUGIN_DIR/scripts/mumega-mcp-$VERSION.zip"

if [[ "$BUILD" -eq 1 ]]; then
	bash "$BUILD_SCRIPT" --version "$VERSION"
fi

if [[ ! -f "$ZIP_PATH" ]]; then
	echo "Missing ZIP artifact: $ZIP_PATH" >&2
	echo "Run with --build or build it first." >&2
	exit 1
fi

ZIP_VERSION="$(unzip -p "$ZIP_PATH" site-pilot-ai/site-pilot-ai.php | grep -m1 'Version:' | sed 's/.*Version:[[:space:]]*//' | tr -d '[:space:]')"
if [[ "$ZIP_VERSION" != "$VERSION" ]]; then
	echo "ZIP version mismatch: expected $VERSION, got $ZIP_VERSION" >&2
	exit 1
fi

if [[ "$VERIFY_ONLY" -eq 0 ]]; then
	sudo -n cp "$ZIP_PATH" "$STATIC_ZIP"
	sudo -n cp "$MANIFEST_FILE" "$STATIC_MANIFEST"
fi

if [[ "$DEPLOY_WORKER" -eq 1 ]]; then
	if [[ ! -f "$WORKER_FILE" ]]; then
		echo "Worker file not found: $WORKER_FILE" >&2
		exit 1
	fi

	python3 - "$MANIFEST_FILE" "$WORKER_FILE" <<'PY'
import json, pathlib, re, sys
manifest_path = pathlib.Path(sys.argv[1])
worker_path = pathlib.Path(sys.argv[2])
manifest = json.loads(manifest_path.read_text(encoding='utf-8'))
worker = worker_path.read_text(encoding='utf-8')
replacement = "const VERSION_INFO = " + json.dumps(manifest, indent=2) + ";"
updated, count = re.subn(r"const VERSION_INFO = \{.*?\n\};", replacement, worker, flags=re.S)
if count != 1:
    raise SystemExit("Could not update VERSION_INFO in worker source")
worker_path.write_text(updated, encoding='utf-8')
PY
fi

LIVE_STATIC_JSON="$(curl -fsSL https://mumega.com/mcp-updates/version.json)"
LIVE_STATIC_VERSION="$(python3 -c 'import json,sys; print(json.load(sys.stdin)["version"])' <<<"$LIVE_STATIC_JSON")"

if [[ "$LIVE_STATIC_VERSION" != "$VERSION" ]]; then
	echo "Live static manifest mismatch: expected $VERSION, got $LIVE_STATIC_VERSION" >&2
	exit 1
fi

LIVE_ZIP_HEADERS="$(curl -I -fsSL https://mumega.com/mcp-updates/mumega-mcp-latest.zip)"
if ! grep -q "200 OK" <<<"$LIVE_ZIP_HEADERS"; then
	echo "Live ZIP check failed" >&2
	echo "$LIVE_ZIP_HEADERS" >&2
	exit 1
fi

echo "Published Site Pilot AI $VERSION"
echo "  static manifest: https://mumega.com/mcp-updates/version.json"
echo "  static zip:      https://mumega.com/mcp-updates/mumega-mcp-latest.zip"
if [[ "$DEPLOY_WORKER" -eq 1 ]]; then
	echo "  worker source synced: $WORKER_FILE"
fi
