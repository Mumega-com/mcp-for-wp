#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

usage() {
	cat <<'EOF'
Usage:
  scripts/release_freemius.sh --version X.Y.Z [options]

Required:
  --version X.Y.Z                Release version for both free/pro plugins

Options:
  --token TOKEN                  Freemius bearer token (or FREEMIUS_BEARER_TOKEN)
  --product-id ID                Freemius product id (default: 23824 or FREEMIUS_PRODUCT_ID)
  --release-mode MODE            release mode for tag update (default: released)
  --skip-bump                    Do not edit plugin/readme/changelog versions
  --dry-run                      Build and print actions; skip Freemius API calls
  --keep-zips                    Keep generated zip files in repo root
  -h, --help                     Show help

Examples:
  FREEMIUS_BEARER_TOKEN=... scripts/release_freemius.sh --version 1.0.23
  scripts/release_freemius.sh --version 1.0.23 --token ... --dry-run
EOF
}

require_cmd() {
	command -v "$1" >/dev/null 2>&1 || {
		echo "Missing required command: $1" >&2
		exit 1
	}
}

replace_or_fail() {
	local file="$1"
	local pattern="$2"
	local replacement="$3"
	if ! grep -Eq "$pattern" "$file"; then
		echo "Pattern not found in $file: $pattern" >&2
		exit 1
	fi
	sed -E -i "s|$pattern|$replacement|" "$file"
}

VERSION=""
TOKEN="${FREEMIUS_BEARER_TOKEN:-}"
PRODUCT_ID="${FREEMIUS_PRODUCT_ID:-23824}"
RELEASE_MODE="released"
SKIP_BUMP=0
DRY_RUN=0
KEEP_ZIPS=0

while [[ $# -gt 0 ]]; do
	case "$1" in
	--version)
		VERSION="${2:-}"
		shift 2
		;;
	--token)
		TOKEN="${2:-}"
		shift 2
		;;
	--product-id)
		PRODUCT_ID="${2:-}"
		shift 2
		;;
	--release-mode)
		RELEASE_MODE="${2:-}"
		shift 2
		;;
	--skip-bump)
		SKIP_BUMP=1
		shift
		;;
	--dry-run)
		DRY_RUN=1
		shift
		;;
	--keep-zips)
		KEEP_ZIPS=1
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

if [[ -z "$VERSION" ]]; then
	echo "--version is required" >&2
	usage
	exit 1
fi

if [[ ! "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
	echo "Invalid version: $VERSION (expected X.Y.Z)" >&2
	exit 1
fi

require_cmd zip
require_cmd curl
require_cmd sed
require_cmd grep
require_cmd python3

if [[ "$DRY_RUN" -eq 0 && -z "$TOKEN" ]]; then
	echo "Freemius bearer token is required (--token or FREEMIUS_BEARER_TOKEN)" >&2
	exit 1
fi

FREE_MAIN_FILE="site-pilot-ai/site-pilot-ai.php"
FREE_README_FILE="site-pilot-ai/readme.txt"
FREE_CHANGELOG_FILE="site-pilot-ai/CHANGELOG.md"
FREE_FS_INIT_FILE="site-pilot-ai/includes/freemius-init.php"
FREE_LICENSE_FILE="site-pilot-ai/includes/class-spai-license.php"

PRO_MAIN_FILE="site-pilot-ai-pro/site-pilot-ai-pro.php"
PRO_README_FILE="site-pilot-ai-pro/readme.txt"
PRO_WOO_FILE="site-pilot-ai-pro/includes/api/class-spai-rest-woocommerce.php"

if [[ "$SKIP_BUMP" -eq 0 ]]; then
	echo "Bumping plugin versions to $VERSION"

	replace_or_fail "$FREE_MAIN_FILE" "^ \\* Version:[[:space:]]+.*$" " * Version:           $VERSION"
	replace_or_fail "$FREE_MAIN_FILE" "^define\( 'SPAI_VERSION', '[^']+' \);$" "define( 'SPAI_VERSION', '$VERSION' );"
	replace_or_fail "$FREE_README_FILE" "^Stable tag: .*$" "Stable tag: $VERSION"

	if ! grep -q "^## \[$VERSION\]" "$FREE_CHANGELOG_FILE"; then
		DATE_TODAY="$(date +%Y-%m-%d)"
		sed -i "0,/^## \[/s//## [$VERSION] - $DATE_TODAY\\n\\n### Changed\\n- Release automation update.\\n\\n## [/" "$FREE_CHANGELOG_FILE"
	fi

	replace_or_fail "$PRO_MAIN_FILE" "^ \\* Version:[[:space:]]+.*$" " * Version:           $VERSION"
	replace_or_fail "$PRO_MAIN_FILE" "^define\( 'SPAI_PRO_VERSION', '[^']+' \);$" "define( 'SPAI_PRO_VERSION', '$VERSION' );"
	replace_or_fail "$PRO_README_FILE" "^Stable tag: .*$" "Stable tag: $VERSION"

	if ! grep -q "^= $VERSION =" "$PRO_README_FILE"; then
		tmp_file="$(mktemp)"
		awk -v ver="$VERSION" '
			{
				print $0
				if ( $0 == "== Changelog ==" && !done ) {
					print ""
					print "= " ver " ="
					print "* Version sync with base plugin release"
					print ""
					done = 1
				}
			}
		' "$PRO_README_FILE" > "$tmp_file"
		mv "$tmp_file" "$PRO_README_FILE"
	fi
fi

FREE_ZIP="site-pilot-ai-$VERSION.zip"
PRO_ZIP="site-pilot-ai-pro-$VERSION.zip"

echo "Building zip packages"
(
	cd site-pilot-ai
	zip -qr "../$FREE_ZIP" .
)
(
	cd site-pilot-ai-pro
	zip -qr "../$PRO_ZIP" .
)

if [[ "$DRY_RUN" -eq 1 ]]; then
	echo "Dry run complete."
	echo "- Free zip: $FREE_ZIP"
	echo "- Pro zip:  $PRO_ZIP"
	echo "- API calls skipped"
	exit 0
fi

echo "Uploading $VERSION to Freemius product $PRODUCT_ID"
CREATE_RESPONSE="$(
	curl -sS -X POST "https://api.freemius.com/v1/products/$PRODUCT_ID/tags.json" \
		-H "Authorization: Bearer $TOKEN" \
		-F "file=@$FREE_ZIP;type=application/zip" \
		-F "premium_file=@$PRO_ZIP;type=application/zip" \
		-F "add_contributor_to_rel=false"
)"

TAG_ID="$(printf '%s' "$CREATE_RESPONSE" | python3 -c 'import json,sys
try:
    data=json.load(sys.stdin)
except Exception:
    print("")
    sys.exit(0)
print(data.get("id",""))')"

if [[ -z "$TAG_ID" ]]; then
	echo "Freemius upload failed:"
	printf '%s\n' "$CREATE_RESPONSE"
	echo "Tip: If error is duplicate_plugin_version, bump --version and retry."
	exit 1
fi

echo "Uploaded tag id: $TAG_ID"
echo "Setting release_mode=$RELEASE_MODE"
RELEASE_RESPONSE="$(
	curl -sS -X PUT "https://api.freemius.com/v1/products/$PRODUCT_ID/tags/$TAG_ID.json" \
		-H "Authorization: Bearer $TOKEN" \
		-H "Content-Type: application/x-www-form-urlencoded" \
		--data "release_mode=$RELEASE_MODE"
)"

FINAL_MODE="$(printf '%s' "$RELEASE_RESPONSE" | python3 -c 'import json,sys
try:
    data=json.load(sys.stdin)
except Exception:
    print("")
    sys.exit(0)
print(data.get("release_mode",""))')"

if [[ "$FINAL_MODE" != "$RELEASE_MODE" ]]; then
	echo "Release mode update response:"
	printf '%s\n' "$RELEASE_RESPONSE"
	echo "Expected release_mode=$RELEASE_MODE but got '$FINAL_MODE'" >&2
	exit 1
fi

echo "Freemius release successful"
echo "- product_id:   $PRODUCT_ID"
echo "- version:      $VERSION"
echo "- tag_id:       $TAG_ID"
echo "- release_mode: $FINAL_MODE"

if [[ "$KEEP_ZIPS" -eq 0 ]]; then
	rm -f "$FREE_ZIP" "$PRO_ZIP"
	echo "Cleaned local zip artifacts"
else
	echo "Kept local zip artifacts"
fi
