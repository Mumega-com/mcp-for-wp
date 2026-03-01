#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

usage() {
	cat <<'EOF'
Usage:
  scripts/site-sync.sh <command> [options]

Commands:
  pull      Export site content to local JSON files
  push      Import local JSON files to a WordPress site
  verify    Check API connectivity and required scopes

Required (env or flags):
  --url URL            WordPress site URL (or SPAI_URL)
  --key KEY            API key (or SPAI_API_KEY)

Options:
  --dir DIR            Data directory (default: site-data)
  --dry-run            Show what would happen without making changes
  --skip-elementor     Skip Elementor data pull/push
  -h, --help           Show this help

Examples:
  # Pull from production
  scripts/site-sync.sh pull --url https://mysite.com --key spai_xxx

  # Push to staging (dry run)
  scripts/site-sync.sh push --url https://staging.mysite.com --key spai_xxx --dry-run

  # CI usage
  SPAI_URL=https://mysite.com SPAI_API_KEY=spai_xxx scripts/site-sync.sh push
EOF
}

require_cmd() {
	command -v "$1" >/dev/null 2>&1 || {
		echo "Missing required command: $1" >&2
		exit 1
	}
}

# ── Arg parsing ──────────────────────────────────────────────

COMMAND=""
URL="${SPAI_URL:-}"
KEY="${SPAI_API_KEY:-}"
DIR="site-data"
DRY_RUN=0
SKIP_ELEMENTOR=0

while [[ $# -gt 0 ]]; do
	case "$1" in
		pull|push|verify) COMMAND="$1"; shift ;;
		--url)            URL="${2:-}"; shift 2 ;;
		--key)            KEY="${2:-}"; shift 2 ;;
		--dir)            DIR="${2:-}"; shift 2 ;;
		--dry-run)        DRY_RUN=1; shift ;;
		--skip-elementor) SKIP_ELEMENTOR=1; shift ;;
		-h|--help)        usage; exit 0 ;;
		*) echo "Unknown argument: $1" >&2; usage; exit 1 ;;
	esac
done

[[ -z "$COMMAND" ]] && { echo "Command required: pull|push|verify" >&2; usage; exit 1; }
[[ -z "$URL" ]]     && { echo "--url or SPAI_URL required" >&2; exit 1; }
[[ -z "$KEY" ]]     && { echo "--key or SPAI_API_KEY required" >&2; exit 1; }

URL="${URL%/}"  # strip trailing slash

require_cmd curl
require_cmd python3

# ── HTTP helpers ─────────────────────────────────────────────

PUSH_WARNINGS=0

spai_request() {
	local method="$1" endpoint="$2" body="${3:-}"
	local full_url="$URL/wp-json/site-pilot-ai/v1/$endpoint"
	local args=(-sS -w '\n%{http_code}' -X "$method" -H "X-API-Key: $KEY")

	if [[ -n "$body" ]]; then
		args+=(-H "Content-Type: application/json" -d "$body")
	fi

	local response
	response="$(curl "${args[@]}" "$full_url")"

	local body_out http_code
	body_out="$(printf '%s' "$response" | sed '$d')"
	http_code="$(printf '%s' "$response" | tail -n1)"

	if [[ "$http_code" == "401" ]]; then
		echo "AUTH FAILED (401) for $endpoint" >&2
		local hint
		hint="$(printf '%s' "$body_out" | python3 -c 'import json,sys; d=json.load(sys.stdin); print(d.get("message",""))' 2>/dev/null || true)"
		[[ -n "$hint" ]] && echo "  $hint" >&2
		exit 1
	fi

	if [[ "$http_code" == "403" ]]; then
		echo "SCOPE FAILURE (403) for $endpoint" >&2
		echo "  Tip: Use an admin-scoped API key" >&2
		exit 1
	fi

	if [[ "$http_code" -ge 400 ]]; then
		echo "HTTP $http_code for $endpoint" >&2
		printf '%s\n' "$body_out" >&2
		exit 1
	fi

	printf '%s' "$body_out"
}

spai_get()  { spai_request GET  "$1"; }
spai_post() { spai_request POST "$1" "$2"; }
spai_put()  { spai_request PUT  "$1" "$2"; }

# ── Preflight ────────────────────────────────────────────────

ELEMENTOR_ACTIVE=0

preflight_check() {
	echo "Checking connectivity to $URL ..."
	local info
	info="$(spai_get "site-info")"

	local name version
	name="$(printf '%s' "$info" | python3 -c 'import json,sys; d=json.load(sys.stdin); print(d.get("name","?"))')"
	version="$(printf '%s' "$info" | python3 -c 'import json,sys; d=json.load(sys.stdin); p=d.get("plugin",{}); print(p.get("version","?") if isinstance(p,dict) else d.get("plugin_version","?"))')"
	ELEMENTOR_ACTIVE="$(printf '%s' "$info" | python3 -c 'import json,sys; d=json.load(sys.stdin); c=d.get("capabilities",{}); print(1 if c.get("elementor") or d.get("elementor_active") else 0)')"

	echo "  Site: $name"
	echo "  Plugin: v$version"
	echo "  Elementor: $([ "$ELEMENTOR_ACTIVE" -eq 1 ] && echo "active" || echo "not active")"
}

# ── Pull ─────────────────────────────────────────────────────

cmd_pull() {
	preflight_check
	mkdir -p "$DIR/pages"

	# 1. Fetch all pages (paginated), build id→slug map
	echo ""
	echo "Pulling pages..."
	local page_num=1 max_pages=1
	local all_pages="[]"

	while [[ $page_num -le $max_pages ]]; do
		local resp
		resp="$(spai_get "pages?per_page=100&page=$page_num&status=any")"
		max_pages="$(printf '%s' "$resp" | python3 -c 'import json,sys; d=json.load(sys.stdin); print(d.get("pages_count",1))')"

		all_pages="$(python3 -c "
import json,sys
existing = json.loads(sys.argv[1])
new_resp = json.loads(sys.argv[2])
existing.extend(new_resp.get('pages',[]))
print(json.dumps(existing))
" "$all_pages" "$resp")"

		page_num=$((page_num + 1))
	done

	local page_count
	page_count="$(printf '%s' "$all_pages" | python3 -c 'import json,sys; print(len(json.load(sys.stdin)))')"
	echo "  Found $page_count pages"

	# Write each page file + fetch Elementor data
	printf '%s' "$all_pages" | python3 -c "
import json, sys, os
pages = json.load(sys.stdin)
out_dir = sys.argv[1]
for p in pages:
    slug = p.get('slug','')
    if not slug:
        continue
    entry = {
        'slug': slug,
        'title': p.get('title',''),
        'status': p.get('status','draft'),
        'template': p.get('template','default'),
        'menu_order': p.get('menu_order', 0),
        'has_elementor': p.get('has_elementor', False),
        'elementor_data': None,
        '_source_id': p.get('id')
    }
    path = os.path.join(out_dir, 'pages', slug + '.json')
    with open(path, 'w') as f:
        json.dump(entry, f, indent=2)
    print(f'{slug}:{p.get(\"id\",0)}:{\"1\" if entry[\"has_elementor\"] else \"0\"}')
" "$DIR" > "$DIR/.page_index"

	# Fetch Elementor data for pages that have it
	if [[ "$SKIP_ELEMENTOR" -eq 0 && "$ELEMENTOR_ACTIVE" -eq 1 ]]; then
		local el_count=0
		while IFS=: read -r slug page_id has_el; do
			if [[ "$has_el" == "1" ]]; then
				echo "  Fetching Elementor: $slug (id=$page_id)"
				spai_get "elementor/$page_id" | python3 -c "
import json, sys, os
el_resp = json.load(sys.stdin)
page_file = os.path.join(sys.argv[1], 'pages', sys.argv[2] + '.json')
with open(page_file) as f:
    page = json.load(f)
page['elementor_data'] = el_resp.get('elementor_data')
with open(page_file, 'w') as f:
    json.dump(page, f, indent=2)
" "$DIR" "$slug"
				el_count=$((el_count + 1))
			fi
		done < "$DIR/.page_index"
		echo "  Fetched Elementor data for $el_count pages"
	fi

	# 2. Pull options (resolve IDs to slugs)
	echo ""
	echo "Pulling options..."
	local options_raw
	options_raw="$(spai_get "options")"

	python3 -c "
import json, sys, os
options = json.loads(sys.argv[1])
out_dir = sys.argv[2]

# Build id→slug map from page index
id_to_slug = {}
index_file = os.path.join(out_dir, '.page_index')
if os.path.exists(index_file):
    with open(index_file) as f:
        for line in f:
            parts = line.strip().split(':')
            if len(parts) >= 2:
                id_to_slug[int(parts[1])] = parts[0]

# Resolve IDs to slugs for portability
front_id = options.get('page_on_front', 0)
posts_id = options.get('page_for_posts', 0)

out = {
    'show_on_front': options.get('show_on_front', 'posts'),
    'page_on_front_slug': id_to_slug.get(front_id, ''),
    'page_for_posts_slug': id_to_slug.get(posts_id, ''),
    'posts_per_page': options.get('posts_per_page', 10),
}
with open(os.path.join(out_dir, 'options.json'), 'w') as f:
    json.dump(out, f, indent=2)
print('  show_on_front: ' + out['show_on_front'])
if out['page_on_front_slug']:
    print('  homepage: ' + out['page_on_front_slug'])
" "$options_raw" "$DIR"

	# 3. Pull custom CSS
	echo ""
	echo "Pulling custom CSS..."
	spai_get "custom-css" > "$DIR/custom-css.json"
	local css_len
	css_len="$(python3 -c "import json; d=json.load(open('$DIR/custom-css.json')); print(len(d.get('css','')))")"
	echo "  $css_len bytes"

	# 4. Pull site context
	echo ""
	echo "Pulling site context..."
	local ctx_resp
	ctx_resp="$(spai_get "site-context" 2>/dev/null || echo '{"context":""}')"
	printf '%s' "$ctx_resp" > "$DIR/site-context.json"

	# 5. Write manifest
	python3 -c "
import json, sys, os, glob
out_dir, source_url = sys.argv[1], sys.argv[2]
pages = sorted([os.path.basename(f).replace('.json','')
    for f in glob.glob(os.path.join(out_dir, 'pages', '*.json'))])
manifest = {
    'pulled_at': '$(date -u +%Y-%m-%dT%H:%M:%SZ)',
    'source_url': source_url,
    'tool': 'site-sync.sh',
    'page_count': len(pages),
    'page_slugs': pages,
}
with open(os.path.join(out_dir, 'manifest.json'), 'w') as f:
    json.dump(manifest, f, indent=2)
" "$DIR" "$URL"

	# Clean up temp file
	rm -f "$DIR/.page_index"

	echo ""
	echo "Pull complete: $DIR/"
	echo "  Pages: $page_count"
	echo "  Ready to commit."
}

# ── Push ─────────────────────────────────────────────────────

cmd_push() {
	[[ ! -f "$DIR/manifest.json" ]] && { echo "No manifest.json in $DIR/. Run pull first." >&2; exit 1; }

	preflight_check

	# 1. Build target slug→id map
	echo ""
	echo "Building target page map..."
	local target_pages
	target_pages="$(spai_get "pages?per_page=100&status=any")"

	# Parse into slug=id pairs
	declare -A SLUG_MAP
	while IFS='=' read -r slug id; do
		SLUG_MAP["$slug"]="$id"
	done < <(printf '%s' "$target_pages" | python3 -c "
import json, sys
resp = json.load(sys.stdin)
for p in resp.get('pages', []):
    print(f'{p[\"slug\"]}={p[\"id\"]}')
")

	local target_count=${#SLUG_MAP[@]}
	echo "  $target_count existing pages on target"

	# 2. Sync each page
	echo ""
	echo "Syncing pages..."
	local created=0 updated=0 el_ok=0 el_warn=0

	for page_file in "$DIR/pages/"*.json; do
		[[ ! -f "$page_file" ]] && continue

		local slug title status template menu_order has_el
		read -r slug title status template menu_order has_el < <(python3 -c "
import json, sys
d = json.load(open(sys.argv[1]))
slug = d.get('slug','')
title = d.get('title','').replace('\"','\\\\\"')
status = d.get('status','draft')
template = d.get('template','default')
menu_order = d.get('menu_order',0)
has_el = '1' if d.get('elementor_data') else '0'
print(f'{slug}\t{title}\t{status}\t{template}\t{menu_order}\t{has_el}')
" "$page_file" | tr '\t' ' ')

		# Fix: re-read properly with python
		slug="$(python3 -c "import json; print(json.load(open('$page_file')).get('slug',''))")"
		title="$(python3 -c "import json; print(json.load(open('$page_file')).get('title',''))")"
		status="$(python3 -c "import json; print(json.load(open('$page_file')).get('status','draft'))")"
		template="$(python3 -c "import json; print(json.load(open('$page_file')).get('template','default'))")"
		menu_order="$(python3 -c "import json; print(json.load(open('$page_file')).get('menu_order',0))")"
		has_el="$(python3 -c "import json; print('1' if json.load(open('$page_file')).get('elementor_data') else '0')")"

		local page_id action
		if [[ -n "${SLUG_MAP[$slug]:-}" ]]; then
			page_id="${SLUG_MAP[$slug]}"
			action="update"
		else
			action="create"
		fi

		if [[ "$DRY_RUN" -eq 1 ]]; then
			echo "  [DRY RUN] would $action: $slug"
			[[ "$has_el" == "1" ]] && echo "             + Elementor data"
			continue
		fi

		if [[ "$action" == "create" ]]; then
			local create_body
			create_body="$(python3 -c "
import json
print(json.dumps({'title': $(python3 -c "import json; print(json.dumps(json.load(open('$page_file')).get('title','')))"
), 'slug': '$slug', 'status': 'draft'}))")"
			local create_resp
			create_resp="$(spai_post "pages" "$create_body")"
			page_id="$(printf '%s' "$create_resp" | python3 -c 'import json,sys; print(json.load(sys.stdin).get("id",0))')"
			SLUG_MAP["$slug"]="$page_id"
			created=$((created + 1))
		fi

		# Update page meta
		local update_body
		update_body="$(python3 -c "
import json
d = json.load(open('$page_file'))
print(json.dumps({
    'title': d.get('title',''),
    'status': d.get('status','draft'),
    'template': d.get('template','default'),
    'menu_order': d.get('menu_order',0),
}))")"
		spai_put "pages/$page_id" "$update_body" > /dev/null
		[[ "$action" == "update" ]] && updated=$((updated + 1))

		# Push Elementor data via base64
		if [[ "$has_el" == "1" && "$SKIP_ELEMENTOR" -eq 0 && "$ELEMENTOR_ACTIVE" -eq 1 ]]; then
			local el_body
			el_body="$(python3 -c "
import json, sys, base64
d = json.load(open(sys.argv[1]))
el = d.get('elementor_data')
if el:
    b64 = base64.b64encode(json.dumps(el).encode()).decode()
    print(json.dumps({'elementor_data_base64': b64}))
else:
    print('{}')
" "$page_file")"

			if [[ "$el_body" != "{}" ]]; then
				local el_resp
				el_resp="$(spai_post "elementor/$page_id" "$el_body")"

				local saved submitted
				saved="$(printf '%s' "$el_resp" | python3 -c 'import json,sys; print(json.load(sys.stdin).get("sections_saved","?"))' 2>/dev/null || echo "?")"
				submitted="$(printf '%s' "$el_resp" | python3 -c 'import json,sys; print(json.load(sys.stdin).get("sections_submitted","?"))' 2>/dev/null || echo "?")"

				if [[ "$saved" != "$submitted" && "$saved" != "?" ]]; then
					echo "  WARN: $slug elementor incomplete ($saved/$submitted sections)"
					PUSH_WARNINGS=$((PUSH_WARNINGS + 1))
					el_warn=$((el_warn + 1))
				else
					el_ok=$((el_ok + 1))
				fi
			fi
		fi

		echo "  ${action}d: $slug (id=$page_id)$([ "$has_el" == "1" ] && echo " +elementor" || true)"
	done

	# 3. Push custom CSS
	if [[ -f "$DIR/custom-css.json" ]]; then
		echo ""
		echo "Pushing custom CSS..."
		if [[ "$DRY_RUN" -eq 1 ]]; then
			echo "  [DRY RUN] would update custom CSS"
		else
			local css_body
			css_body="$(python3 -c "
import json
d = json.load(open('$DIR/custom-css.json'))
print(json.dumps({'css': d.get('css','')}))")"
			spai_put "custom-css" "$css_body" > /dev/null
			echo "  Updated"
		fi
	fi

	# 4. Push site context
	if [[ -f "$DIR/site-context.json" ]]; then
		echo ""
		echo "Pushing site context..."
		if [[ "$DRY_RUN" -eq 1 ]]; then
			echo "  [DRY RUN] would update site context"
		else
			local ctx_body
			ctx_body="$(python3 -c "
import json
d = json.load(open('$DIR/site-context.json'))
print(json.dumps({'context': d.get('context','')}))")"
			spai_put "site-context" "$ctx_body" > /dev/null 2>/dev/null || true
			echo "  Updated"
		fi
	fi

	# 5. Push options (resolve slugs to IDs) — LAST
	if [[ -f "$DIR/options.json" ]]; then
		echo ""
		echo "Pushing options..."
		if [[ "$DRY_RUN" -eq 1 ]]; then
			echo "  [DRY RUN] would update reading options"
		else
			local options_body
			options_body="$(python3 -c "
import json, sys
opts = json.load(open('$DIR/options.json'))
slug_map = json.loads(sys.argv[1])

front_slug = opts.get('page_on_front_slug', '')
posts_slug = opts.get('page_for_posts_slug', '')

out = {
    'show_on_front': opts.get('show_on_front', 'posts'),
    'page_on_front': slug_map.get(front_slug, 0),
    'page_for_posts': slug_map.get(posts_slug, 0),
    'posts_per_page': opts.get('posts_per_page', 10),
}
print(json.dumps(out))
" "$(python3 -c "
import json
m = {$(for slug in "${!SLUG_MAP[@]}"; do printf "'%s': %s, " "$slug" "${SLUG_MAP[$slug]}"; done)}
print(json.dumps(m))
")")"
			spai_put "options" "$options_body" > /dev/null
			echo "  Updated (homepage: $(python3 -c "import json; print(json.load(open('$DIR/options.json')).get('page_on_front_slug','none'))"))"
		fi
	fi

	# Summary
	echo ""
	echo "Push complete."
	echo "  Created: $created pages"
	echo "  Updated: $updated pages"
	[[ "$el_ok" -gt 0 || "$el_warn" -gt 0 ]] && echo "  Elementor: $el_ok ok, $el_warn warnings"

	[[ "$PUSH_WARNINGS" -gt 0 ]] && {
		echo ""
		echo "WARNING: $PUSH_WARNINGS Elementor saves had mismatched section counts."
		echo "  Consider re-pushing affected pages with elementor_data_base64."
		exit 2
	}
}

# ── Verify ───────────────────────────────────────────────────

cmd_verify() {
	preflight_check

	echo ""
	echo "Verifying read scope..."
	spai_get "pages?per_page=1" > /dev/null
	echo "  OK: can read pages"

	echo ""
	echo "Verifying options scope..."
	spai_get "options" > /dev/null
	echo "  OK: can read options"

	echo ""
	echo "Verifying custom CSS scope..."
	spai_get "custom-css" > /dev/null
	echo "  OK: can read custom CSS"

	echo ""
	echo "All scopes verified. Ready for pull/push."
}

# ── Dispatch ─────────────────────────────────────────────────

case "$COMMAND" in
	pull)   cmd_pull ;;
	push)   cmd_push ;;
	verify) cmd_verify ;;
	*)      echo "Unknown command: $COMMAND" >&2; usage; exit 1 ;;
esac
