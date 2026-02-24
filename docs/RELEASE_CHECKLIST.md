# Release Checklist

Quick reference for releasing a new version of Site Pilot AI.

## Pre-Release Checklist

- [ ] All features/fixes committed to `main`
- [ ] All tests passing locally
- [ ] Version number decided (semantic versioning)
- [ ] CHANGELOG.md updated (if exists)
- [ ] README.md updated (if needed)

## Version Update

1. **Update plugin version** in `/site-pilot-ai/site-pilot-ai.php`:

```php
/**
 * Version: 1.0.XX  // ← Update this
 */

define( 'SPAI_VERSION', '1.0.XX' ); // ← Update this too
```

2. **Verify both versions match:**

```bash
# Quick check
grep -E "(Version:|SPAI_VERSION)" site-pilot-ai/site-pilot-ai.php
```

Expected output:
```
 * Version:           1.0.XX
define( 'SPAI_VERSION', '1.0.XX' );
```

## Create Release

### Option A: Automated (Recommended)

```bash
# 1. Commit version change
git add site-pilot-ai/site-pilot-ai.php
git commit -m "release: 1.0.XX - brief description"

# 2. Push to main
git push origin main

# 3. Create and push tag
git tag v1.0.XX
git push origin v1.0.XX

# 4. Wait for GitHub Actions (2-3 minutes)
# Check: https://github.com/themusicalunicorn/wp-ai-operator/actions

# 5. Verify release created
# Check: https://github.com/themusicalunicorn/wp-ai-operator/releases
```

### Option B: Manual (Fallback)

```bash
# 1-2. Same as above (commit + push)

# 3. Build distributions locally
./build.sh 1.0.XX

# 4. Create release manually on GitHub
# Upload: site-pilot-ai-1.0.XX-free.zip
# Upload: site-pilot-ai-1.0.XX.zip
```

## Post-Release Verification

- [ ] GitHub release created successfully
- [ ] Two zip files attached to release
  - [ ] `site-pilot-ai-1.0.XX-free.zip` (smaller, no Pro)
  - [ ] `site-pilot-ai-1.0.XX.zip` (larger, with Pro)
- [ ] Release notes generated
- [ ] Download and test free zip
- [ ] Download and test premium zip
- [ ] Verify free zip has NO `includes/pro/` directory
- [ ] Verify premium zip has `includes/pro/` directory

## Testing Checklist

### Test Free Distribution

```bash
# Download free zip
wget https://github.com/themusicalunicorn/wp-ai-operator/releases/download/v1.0.XX/site-pilot-ai-1.0.XX-free.zip

# Extract and verify
unzip site-pilot-ai-1.0.XX-free.zip
cd site-pilot-ai-temp

# Check: NO Pro directory
[ ! -d "includes/pro" ] && echo "✓ Pro correctly removed" || echo "✗ Pro directory found!"

# Check: NO dev files
[ ! -d ".git" ] && [ ! -d "tests" ] && echo "✓ Dev files removed" || echo "✗ Dev files found!"

# Install in WordPress test site
# Verify: Only free MCP tools available
```

### Test Premium Distribution

```bash
# Download premium zip
wget https://github.com/themusicalunicorn/wp-ai-operator/releases/download/v1.0.XX/site-pilot-ai-1.0.XX.zip

# Extract and verify
unzip site-pilot-ai-1.0.XX.zip
cd site-pilot-ai-premium

# Check: Pro directory exists
[ -d "includes/pro" ] && echo "✓ Pro included" || echo "✗ Pro missing!"

# Check: Pro bootstrap exists
[ -f "includes/pro/class-spai-pro-bootstrap.php" ] && echo "✓ Pro bootstrap found" || echo "✗ Missing!"

# Install in WordPress test site with license
# Verify: All Pro MCP tools available
```

## Common Issues

### Issue: Version Mismatch

**Symptom:**
```
Error: Version mismatch! Plugin file has 1.0.44 but tag is v1.0.45
```

**Fix:**
```bash
# Update both version locations
vim site-pilot-ai/site-pilot-ai.php

# Commit and re-tag
git add site-pilot-ai/site-pilot-ai.php
git commit -m "fix: correct version to 1.0.45"
git push

# Delete old tag
git tag -d v1.0.45
git push origin :refs/tags/v1.0.45

# Create new tag
git tag v1.0.45
git push origin v1.0.45
```

### Issue: Workflow Failed

**Symptom:** GitHub Actions shows red X

**Fix:**
1. Click "Actions" tab on GitHub
2. Click failed workflow run
3. Read error logs
4. Fix issue in code
5. Push fix (workflow re-runs if tag)

### Issue: Wrong Files in Zip

**Symptom:** Free zip contains Pro files or dev files

**Fix:**
1. Check `.github/workflows/release.yml` file list
2. Update exclusion patterns
3. Delete bad release on GitHub
4. Delete and recreate tag

## Quick Commands

```bash
# View current version
grep "Version:" site-pilot-ai/site-pilot-ai.php

# View latest tag
git describe --tags --abbrev=0

# List all releases
gh release list

# Delete release (if needed)
gh release delete v1.0.XX

# Delete tag (if needed)
git tag -d v1.0.XX
git push origin :refs/tags/v1.0.XX
```

## Release Cadence

Recommended schedule:
- **Patch releases (1.0.x):** As needed for bug fixes
- **Minor releases (1.x.0):** Monthly for new features
- **Major releases (x.0.0):** Annually for breaking changes

## Freemius Integration

After GitHub release:
1. Log in to Freemius dashboard
2. Upload premium zip: `site-pilot-ai-1.0.XX.zip`
3. Set as latest version
4. Mark as stable (not beta)
5. Trigger update for licensed users

### Freemius Opt-in Requirement (Critical)

**WordPress will NOT show plugin updates unless the site has completed the Freemius opt-in.**

The Freemius SDK (`_fetch_latest_version` in `class-freemius.php`) exits early if `is_registered() === false`:
```php
if ( ! $this->is_registered() || ! is_essentials_tracking_allowed() ) {
    return false; // No update check performed
}
```

**If a site skipped the opt-in ("Skip" on first activation), updates are permanently blocked.**

#### Fix for blocked sites

**Option A: WP Admin UI**
Visit Site Pilot AI in WP admin. If a Freemius consent banner appears, click "Allow & Continue".

**Option B: Reset anonymous flag via WP-CLI**
```bash
wp eval '
$accounts = get_option("fs_accounts", []);
if (isset($accounts["plugin_data"]["site-pilot-ai"]["is_anonymous"])) {
    unset($accounts["plugin_data"]["site-pilot-ai"]["is_anonymous"]);
    update_option("fs_accounts", $accounts);
    echo "Cleared. Visit WP admin to re-trigger opt-in.\n";
}
' --allow-root
```

**Option C: Reset via PHP (for Docker / no WP-CLI)**
```bash
docker exec CONTAINER bash -c 'php -r "
require_once \"/var/www/html/wp-load.php\";
\$a = get_option(\"fs_accounts\", []);
unset(\$a[\"plugin_data\"][\"site-pilot-ai\"][\"is_anonymous\"]);
update_option(\"fs_accounts\", \$a);
echo \"Done. Visit WP admin to complete opt-in.\\n\";
"'
```

#### Verify update checks work
```bash
wp eval '
require_once ABSPATH . "wp-content/plugins/site-pilot-ai/site-pilot-ai.php";
$fs = spa_fs();
echo "Registered: " . ($fs->is_registered() ? "YES" : "NO") . "\n";
echo "Anonymous: " . ($fs->is_anonymous() ? "YES" : "NO") . "\n";
$update = $fs->get_update(false, false);
echo "Update: " . (is_object($update) ? "v" . $update->version : "none/up-to-date") . "\n";
' --allow-root
```

#### Post-deploy checklist
- [ ] Run `release_freemius.sh` with `--release-mode released`
- [ ] Verify on a registered site that update appears in WP admin > Updates
- [ ] If update doesn't appear, check `fs_accounts` for `is_anonymous` flag

## Rollback Procedure

If release has critical issues:

```bash
# 1. Create hotfix
git checkout main
git pull

# 2. Fix issue
vim site-pilot-ai/...

# 3. Bump version (e.g., 1.0.45 → 1.0.46)
vim site-pilot-ai/site-pilot-ai.php

# 4. Commit and tag
git add .
git commit -m "hotfix: critical issue description"
git push
git tag v1.0.46
git push origin v1.0.46

# 5. Mark bad release as pre-release on GitHub
gh release edit v1.0.45 --prerelease

# 6. Notify users via release notes
```

## Support

- **GitHub Actions Issues:** Check workflow logs under "Actions" tab
- **Build Issues:** Review build script and workflow YAML
- **Distribution Issues:** Verify file exclusion patterns
- **Freemius Issues:** Contact Freemius support

---

**Last Updated:** 2026-02-10
**Next Review:** 2026-03-10
