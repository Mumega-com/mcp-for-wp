# Git-backed Site Sync

Version-control your WordPress site content and deploy it from Git.

## Overview

`site-sync.sh` exports pages (with Elementor data), options, custom CSS, and site context to JSON files. These files can be committed to Git, reviewed in PRs, and automatically deployed via CI.

```
WordPress Site ──pull──> site-data/ (JSON) ──git push──> CI ──push──> WordPress Site
```

## Quick Start

```bash
# 1. Pull content from your site
bash scripts/site-sync.sh pull \
  --url https://mysite.com \
  --key spai_your_api_key

# 2. Review and commit
git add site-data/
git commit -m "Snapshot site content"

# 3. Push to another site (or same site after changes)
bash scripts/site-sync.sh push \
  --url https://staging.mysite.com \
  --key spai_staging_key
```

## Commands

### `pull` — Export site to JSON

Fetches all pages, Elementor data, reading options, custom CSS, and site context.

```bash
scripts/site-sync.sh pull --url URL --key KEY [--dir DIR] [--skip-elementor]
```

Creates:
```
site-data/
├── manifest.json       # pull metadata
├── options.json        # reading settings (slugs, not IDs)
├── custom-css.json     # Additional CSS
├── site-context.json   # AI brief
└── pages/
    ├── home.json       # page + Elementor data
    ├── about.json
    └── ...
```

### `push` — Import JSON to site

Creates or updates pages, pushes Elementor data, CSS, and options.

```bash
scripts/site-sync.sh push --url URL --key KEY [--dir DIR] [--dry-run] [--skip-elementor]
```

- Pages are matched by **slug** (not ID) — works across different sites
- New pages are created automatically
- Options resolve slug references to target site IDs
- Elementor data is sent via base64 encoding for reliability
- Verifies section counts after each save

### `verify` — Check connectivity and scopes

```bash
scripts/site-sync.sh verify --url URL --key KEY
```

Tests API key authentication and required permissions.

## Options

| Flag | Env Var | Description |
|------|---------|-------------|
| `--url URL` | `SPAI_URL` | WordPress site URL |
| `--key KEY` | `SPAI_API_KEY` | Site Pilot AI API key |
| `--dir DIR` | — | Data directory (default: `site-data`) |
| `--dry-run` | — | Show what would happen, no changes |
| `--skip-elementor` | — | Skip Elementor data pull/push |

## CI/CD Setup

### GitHub Actions

1. Add repository secrets:
   - `SPAI_URL` — your WordPress site URL
   - `SPAI_API_KEY` — your API key

2. The workflow at `.github/workflows/site-deploy.yml` automatically runs `push` when `site-data/` files change on `main`.

3. Manual deploy: go to Actions → "Deploy Site Content" → Run workflow.

### Other CI Systems

```bash
# Any CI that has bash, curl, python3
SPAI_URL="$YOUR_URL" SPAI_API_KEY="$YOUR_KEY" bash scripts/site-sync.sh push
```

## How Page Matching Works

Pages are identified by **slug**, not WordPress post ID. This means:

- `site-data/pages/home.json` maps to the page with slug `home` on any site
- If the page exists, it's updated
- If it doesn't exist, it's created
- Reading options (`page_on_front`) store slug references, resolved to IDs during push

This makes the export portable between sites, staging environments, and fresh installs.

## Exit Codes

| Code | Meaning |
|------|---------|
| 0 | Success |
| 1 | Fatal error (auth failure, network error, missing data) |
| 2 | Partial success (some Elementor saves had section count mismatches) |

## Workflow Examples

### Staging → Production

```bash
# Pull from staging
bash scripts/site-sync.sh pull --url https://staging.mysite.com --key spai_staging

# Review changes
git diff site-data/

# Commit and push to trigger CI deploy to production
git add site-data/ && git commit -m "Update homepage hero section"
git push  # CI deploys to production via SPAI_URL secret
```

### Fresh Install Setup

```bash
# On a fresh WordPress with Site Pilot AI + Elementor installed:
bash scripts/site-sync.sh push \
  --url http://localhost:8080 \
  --key spai_local_key \
  --dir site-data
```

### Dry Run

```bash
bash scripts/site-sync.sh push --url https://mysite.com --key spai_xxx --dry-run
# Output:
#   [DRY RUN] would update: home
#              + Elementor data
#   [DRY RUN] would create: new-landing-page
#              + Elementor data
#   [DRY RUN] would update custom CSS
#   [DRY RUN] would update reading options
```

## Phase 2 (Planned)

- Menu export/import
- SEO meta sync (Yoast/RankMath)
- Media asset references
- Elementor global colors/fonts
- Taxonomy terms
