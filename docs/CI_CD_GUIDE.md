# CI/CD Guide - GitHub Actions Automation

This guide explains the automated release and validation workflows for Site Pilot AI.

## Overview

We have two main workflows:

1. **Release Workflow** - Automated releases when version tags are pushed
2. **Validate Workflow** - PR validation for code quality and structure

## Release Workflow

### Trigger

The release workflow automatically runs when you push a version tag:

```bash
git tag v1.0.45
git push origin v1.0.45
```

### What It Does

1. **Version Verification**
   - Extracts version from tag (strips `v` prefix)
   - Verifies version in `site-pilot-ai.php` matches tag
   - Verifies `SPAI_VERSION` constant matches tag
   - Fails if any mismatch detected

2. **Free Distribution Build**
   - Copies `site-pilot-ai/` directory
   - **Removes** `includes/pro/` (Pro features stripped)
   - **Removes** development files:
     - `.git`, `.github`, `tests/`
     - `node_modules/`, `dist/`
     - `.gitignore`, `.distignore`
     - `composer.json`, `package.json`
     - `README.md`, `CHANGELOG.md`
     - Build scripts
   - Creates: `site-pilot-ai-{version}-free.zip`

3. **Premium Distribution Build**
   - Copies `site-pilot-ai/` directory
   - **Keeps** `includes/pro/` (all Pro features)
   - **Removes** same development files as free
   - Creates: `site-pilot-ai-{version}.zip`

4. **Release Notes Generation**
   - Fetches commits since last version tag
   - Generates formatted release notes
   - Includes installation instructions
   - Adds download links

5. **GitHub Release Creation**
   - Creates release with version tag
   - Attaches both zip files
   - Publishes release notes
   - Marks as non-draft, non-prerelease

### File Naming

- **Free:** `site-pilot-ai-1.0.45-free.zip`
- **Premium:** `site-pilot-ai-1.0.45.zip`

### Usage Example

```bash
# 1. Update version in code
vim site-pilot-ai/site-pilot-ai.php
# Change: * Version: 1.0.45
# Change: define( 'SPAI_VERSION', '1.0.45' );

# 2. Commit changes
git add site-pilot-ai/site-pilot-ai.php
git commit -m "release: 1.0.45 - Elementor template CRUD"

# 3. Push to main
git push origin main

# 4. Create and push tag
git tag v1.0.45
git push origin v1.0.45

# 5. GitHub Actions will:
#    - Build free and premium zips
#    - Create GitHub release
#    - Attach both files
#    - Generate release notes
```

### Expected Output

After the workflow runs, you'll have:

- GitHub Release at: `https://github.com/themusicalunicorn/wp-ai-operator/releases/tag/v1.0.45`
- Two downloadable assets:
  - `site-pilot-ai-1.0.45-free.zip` (no Pro features)
  - `site-pilot-ai-1.0.45.zip` (full Pro features)

## Validate Workflow

### Trigger

Runs automatically on pull requests to `main` that touch:
- `site-pilot-ai/**` files
- `.github/workflows/validate.yml`

### What It Checks

1. **PHP Syntax Check** (PHP 7.4, 8.0, 8.1, 8.2)
   - Runs `php -l` on all `.php` files
   - Fails if any syntax errors found

2. **Plugin Headers Validation**
   - Verifies `Plugin Name` exists
   - Checks `Version` header is present and valid
   - Confirms `SPAI_VERSION` constant matches version header
   - Validates all required headers:
     - Description
     - Author
     - Requires at least
     - Requires PHP
     - License

3. **Required Files Check**
   - `site-pilot-ai/site-pilot-ai.php`
   - `site-pilot-ai/index.php`
   - `site-pilot-ai/readme.txt`
   - `site-pilot-ai/includes/api/class-spai-rest-mcp.php`
   - `site-pilot-ai/includes/core/class-spai-plugin.php`
   - `site-pilot-ai/includes/admin/class-spai-admin.php`

4. **Directory Structure Validation**
   - `includes/`, `includes/api/`, `includes/core/`
   - `includes/admin/`, `includes/traits/`
   - `admin/`, `languages/`

5. **Security Checks**
   - Warns if PHP files lack `ABSPATH` protection
   - Fails if `eval()` usage detected
   - Basic security pattern scanning

6. **Pro Structure Validation** (if Pro exists)
   - Checks for `includes/pro/class-spai-pro-bootstrap.php`
   - Validates Pro directory structure

### Matrix Testing

Validates against multiple PHP versions in parallel:
- PHP 7.4 (minimum requirement)
- PHP 8.0
- PHP 8.1
- PHP 8.2

### Usage Example

```bash
# 1. Create feature branch
git checkout -b feature/new-endpoint

# 2. Make changes
vim site-pilot-ai/includes/api/class-spai-rest-new.php

# 3. Commit and push
git add .
git commit -m "feat: add new endpoint"
git push origin feature/new-endpoint

# 4. Create PR on GitHub
# Validation workflow runs automatically

# 5. If validation fails:
#    - Check workflow logs on GitHub
#    - Fix issues locally
#    - Push fixes
#    - Workflow re-runs automatically
```

## Workflow Files

| File | Purpose |
|------|---------|
| `.github/workflows/release.yml` | Automated releases on version tags |
| `.github/workflows/validate.yml` | PR validation and testing |
| `.github/workflows/site-pilot-ai-ci.yml` | Existing CI (lint + unit tests) |
| `.github/RELEASE_TEMPLATE.md` | Manual release documentation template |

## Best Practices

### Version Management

1. **Always update both places:**
   ```php
   // In site-pilot-ai/site-pilot-ai.php
   * Version: 1.0.45
   define( 'SPAI_VERSION', '1.0.45' );
   ```

2. **Use semantic versioning:**
   - `v1.0.x` - Patch (bug fixes)
   - `v1.x.0` - Minor (new features, backward compatible)
   - `vx.0.0` - Major (breaking changes)

3. **Tag format:** Always use `v` prefix (e.g., `v1.0.45`)

### Release Process

1. Update version in code
2. Commit changes to `main`
3. Tag the commit
4. Push tag (triggers release)
5. Verify release on GitHub
6. Test both free and premium zips

### Pull Requests

1. Create feature branch
2. Make changes
3. Push and create PR
4. Wait for validation to pass
5. Address any failures
6. Merge when all checks green

## Troubleshooting

### Release Workflow Fails

**Version mismatch error:**
```
Error: Version mismatch! Plugin file has 1.0.44 but tag is v1.0.45
```

**Solution:**
```bash
# Update version in PHP file
vim site-pilot-ai/site-pilot-ai.php

# Commit the fix
git add site-pilot-ai/site-pilot-ai.php
git commit -m "fix: correct version to 1.0.45"
git push

# Delete and recreate tag
git tag -d v1.0.45
git push origin :refs/tags/v1.0.45
git tag v1.0.45
git push origin v1.0.45
```

**Zip creation fails:**
```
Error: No such file or directory
```

**Solution:** Verify `site-pilot-ai/` directory exists and has correct structure.

### Validate Workflow Fails

**PHP syntax error:**
```
Parse error: syntax error, unexpected token
```

**Solution:** Fix PHP syntax in the reported file and push.

**Missing file error:**
```
Required file missing: site-pilot-ai/includes/api/class-spai-rest-mcp.php
```

**Solution:** Ensure file exists or update validation to reflect new structure.

**Version inconsistency:**
```
Version mismatch! Header: 1.0.45, Constant: 1.0.44
```

**Solution:** Update both version locations in `site-pilot-ai.php`.

## Security Considerations

### GitHub Token

The workflows use `GITHUB_TOKEN` which is automatically provided by GitHub Actions. No manual configuration needed.

### Secrets

No sensitive data is exposed in workflows. All operations use public information.

### Permissions

Release workflow requires:
- `contents: write` - To create releases and upload assets

## Monitoring

### Check Workflow Status

1. Go to repository on GitHub
2. Click "Actions" tab
3. View workflow runs and logs

### Notifications

GitHub sends notifications when workflows:
- Fail (via email/web)
- Complete successfully (optional, configure in GitHub settings)

## Future Enhancements

Potential improvements:
- [ ] WordPress.org SVN deployment
- [ ] Automated changelog generation from commits
- [ ] Integration tests against live WordPress instance
- [ ] Freemius API integration for automatic updates
- [ ] Slack/Discord notifications on release
- [ ] Auto-increment version numbers
- [ ] Release candidate (RC) builds for testing

## Related Documentation

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Freemius Deployment Guide](https://freemius.com/help/)
- [WordPress Plugin Guidelines](https://developer.wordpress.org/plugins/)

---

**Last Updated:** 2026-02-10
**Workflow Versions:** release.yml v1.0, validate.yml v1.0
