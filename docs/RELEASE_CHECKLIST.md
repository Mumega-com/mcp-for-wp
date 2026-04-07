# Release Checklist

Current release flow for Site Pilot AI.

This project no longer uses GitHub releases, Freemius, or the Cloudflare worker as the source of truth for plugin updates. The canonical update path is:

- Static manifest: `https://mumega.com/spai-updates/version.json`
- Download ZIP: `https://mumega.com/spai-updates/mumega-site-pilot-ai-latest.zip`

## Pre-Release

- [ ] All update-related changes committed to `main`
- [ ] Local tests passing
- [ ] Version number decided
- [ ] `site-pilot-ai/site-pilot-ai.php` updated
- [ ] `version.json` updated
- [ ] `readme.txt` updated if needed
- [ ] `site-pilot-ai/CHANGELOG.md` updated if needed

## Version Bump

Update these locations together:

1. [site-pilot-ai.php](/home/mumega/projects/sitepilotai/wp-ai-operator/site-pilot-ai/site-pilot-ai.php)
   - plugin header `Version`
   - `SPAI_VERSION`
2. [version.json](/home/mumega/projects/sitepilotai/wp-ai-operator/version.json)
   - `version`
   - `download_url`
   - compatibility fields
3. `readme.txt`
   - stable tag / changelog if relevant

Quick check:

```bash
grep -E "(Version:|SPAI_VERSION)" site-pilot-ai/site-pilot-ai.php
cat version.json
```

## Build

```bash
bash scripts/build-wporg.sh
```

Expected output:

- `scripts/mumega-site-pilot-ai-X.Y.Z.zip`

## Publish

### 1. Publish release artifacts

Preferred:

```bash
bash scripts/publish_update_release.sh --build
```

This does all of the following:

- verifies version consistency across plugin header, `SPAI_VERSION`, `readme.txt`, and `version.json`
- builds `mumega-site-pilot-ai-X.Y.Z.zip`
- publishes the ZIP to `/var/www/spai-updates/mumega-site-pilot-ai-latest.zip`
- publishes `version.json` to `/var/www/spai-updates/version.json`
- verifies the live `mumega.com` artifact URLs

Manual fallback:

```bash
sudo cp scripts/mumega-site-pilot-ai-X.Y.Z.zip /var/www/spai-updates/mumega-site-pilot-ai-latest.zip
sudo cp version.json /var/www/spai-updates/version.json
```

### 2. Optional: Sync the legacy worker

```bash
bash scripts/publish_update_release.sh --deploy-worker --verify-only
```

The worker is no longer part of the critical update path. Only sync it if you still want the legacy worker URL to mirror the static manifest.

### 3. Commit and push repo changes

```bash
git add -A
git commit -m "Bump version to X.Y.Z"
git push origin main
```

## Canonical Update Rules

- The static `mumega.com` manifest and ZIP are the only canonical release artifacts.
- `spai_update_info` is an optional site-level override, not the source of truth.
- If `spai_update_info` is used during deploys, it must match the static manifest exactly.
- If no override is required, leave `spai_update_info` empty.

### Critical Warning

A stale `spai_update_info` option can block future updates even when `spai_version_url` is correct.

The updater currently checks:

1. `spai_update_info`
2. `spai_version_url`
3. built-in worker URL fallback

That means stale override data can hide newer static-manifest releases.

## Post-Release Verification

### Artifact checks

- [ ] `https://mumega.com/spai-updates/version.json` returns the new version
- [ ] `https://mumega.com/spai-updates/mumega-site-pilot-ai-latest.zip` exists
- [ ] ZIP and manifest versions match

### Site checks

On a target site:

- [ ] `spai_version_url` points at `https://mumega.com/spai-updates/version.json`
- [ ] `spai_update_info` is empty or matches the worker manifest exactly
- [ ] `/wp-json/site-pilot-ai/v1/update` reports the expected result

Example checks:

```bash
curl -fsSL "https://SITE/wp-json/site-pilot-ai/v1/option?key=spai_version_url" -H "X-API-Key: ..."
curl -fsSL "https://SITE/wp-json/site-pilot-ai/v1/option?key=spai_update_info" -H "X-API-Key: ..."
curl -fsSL "https://SITE/wp-json/site-pilot-ai/v1/update" -H "X-API-Key: ..."
```

### If a site is already on the new version

Expected:

```json
{
  "current_version": "X.Y.Z",
  "update_available": false,
  "new_version": null,
  "has_package": false
}
```

## Updating Target Sites

### Preferred

Let WordPress discover updates naturally from the static `mumega.com` manifest.

Requirements:

- target site plugin version is recent enough to use the self-update flow
- `spai_version_url` is correct
- `spai_update_info` is empty or current
- `wp-content/upgrade` is writable by the actual web/PHP user on the host

### Forced

If needed, trigger a direct package install via the REST update route:

```bash
curl -fsSL -X POST "https://SITE/wp-json/site-pilot-ai/v1/update" \
  -H "X-API-Key: ..." \
  -H "Content-Type: application/json" \
  --data '{"package_url":"https://mumega.com/spai-updates/mumega-site-pilot-ai-latest.zip"}'
```

## Failure Mode: Update Not Appearing

Check these in order:

1. `spai_update_info` is stale
2. static manifest version is wrong
3. ZIP URL is wrong or unreachable
4. `wp-content/upgrade` or the plugin directory is not writable by the runtime PHP user
5. site is still on an older plugin build with weaker self-update behavior
6. `update_plugins` / `spai_update_check` caches need clearing

Useful reset:

```bash
curl -fsSL "https://SITE/wp-json/site-pilot-ai/v1/update" -H "X-API-Key: ..."
```

That route clears update caches before checking.

## Rollback

If a release is bad:

1. Fix the issue locally
2. Bump to a newer version
3. Rebuild the ZIP
4. Replace the `mumega.com` ZIP
5. Verify static manifest + ZIP + site update checks again

Do not roll back by leaving mismatched manifests or stale `spai_update_info` on sites.

## Notes From 2026-03-31

- Live site `sitepilotai.mumega.com` was manually updated from `1.7.4` to `1.8.2`
- The site had a stale `spai_update_info` pinned to `1.7.6`
- Clearing `spai_update_info` restored normal worker-based update detection
- The permanent fix is to default all sites to `https://mumega.com/spai-updates/version.json` so the worker is no longer required for auto-updates
- A bind-mounted plugin directory is not a valid auto-update test environment because WordPress cannot replace the mounted plugin path during upgrade
- A clean local volume-backed WordPress install successfully self-updated from `1.8.4` to `1.8.5` once the updater ran as the plugin service user and `wp-content/upgrade` was writable

---

**Last Updated:** 2026-03-31
