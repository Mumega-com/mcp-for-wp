# Product Roadmap

Status: Active
Updated: 2026-04-01

This roadmap tracks the operator-focused polish work for Site Pilot AI. The target user is a founder, marketer, or agency operator who needs AI to produce WordPress pages and products quickly without losing structure, brand consistency, or control.

## Progress Snapshot

- Done: Design References admin UI in the Library tab
- Done: Draft-page creation from design references
- Done: Automatic starter-part creation and reference-to-part linking
- Done: Library operating-sequence card and provenance badges
- Done: Update fallback and manual recovery UI in Setup
- Done: State-based operator onboarding checklist in Setup
- Done: Asset lineage counts and drill-down links in the Library
- Done: Library health summary for unused references and unlinked assets
- In progress: Sharpen operator-facing messaging across README and plugin readme
- Next: Validate the full real-site loop and tighten remaining release surfaces

## Beta

### Issue: Make the core workflow obvious in admin
- Show the operating sequence clearly: `Site Character -> Design References -> Archetypes -> Parts -> Build Draft`.
- Reduce the amount of product knowledge a new user must infer from scattered tabs.
- Make the Library tab feel like the system of record for reusable assets.

### Issue: Add Design References admin UI
- Create a human-facing UI for design references in the Library tab.
- Support creating references from file upload, URL, and existing media.
- Capture `title`, `page_intent`, `archetype_class`, `style`, `notes`, `must_keep`, `avoid`, and `section_outline`.
- Let operators manage the same image-based design memory that MCP models can already use.
- Status: Implemented in admin Library, including draft-page creation from a reference and automatic starter-part linking.

### Issue: Improve Library UX
- Add stronger filtering across archetypes, product archetypes, parts, and design references.
- Show provenance such as `from image`, `from figma`, or `from live page`.
- Add quick actions so humans can create drafts, apply parts, and connect references without leaving the Library.
- Status: In progress. Draft creation from references, provenance badges, usage counts, and drill-down links are live; richer previews and more operational quick actions still need polish.

### Issue: Fix release trust and update surfaces
- Keep the website version text aligned with the real shipped package version.
- Keep `readme.txt`, `CHANGELOG.md`, `version.json`, and the live ZIP in sync on every release.
- Improve in-plugin update UX so auto-update failures fall back to a clear manual path instead of vague errors.
- Status: In progress. The in-plugin update fallback and manual recovery UI are implemented; website/release-surface consistency still needs operational cleanup.

### Issue: Stabilize local verification
- Make the local WordPress test environment reliable enough for repeatable Elementor, WooCommerce, and design-reference smoke tests.
- Reduce release-time uncertainty caused by unstable local services.

### Issue: Tighten first-run onboarding
- Make draft-first, archetype-first, and parts-first behavior explicit from the first admin session.
- Teach users how to build a reusable system, not just how to call tools.
- Status: Implemented in Setup with an operator onboarding checklist and next-step guidance.

## Paid Launch

### Issue: Guided first-run setup
- Add a setup wizard for site character, starter archetypes, starter parts categories, and integrations.
- Make the product feel opinionated and ready-to-use for operators.

### Issue: Asset lineage and reuse visibility
- Show where parts, archetypes, and design references came from.
- Show where they are used.
- Make the Library a real asset system instead of a flat inventory.

### Issue: Executable design-reference flows
- Add direct flows from design references to draft pages, archetypes, and parts.
- Automatically link outputs back to the source image/reference.

### Issue: Better update fallback UX
- Attempt automatic updates when possible.
- If host restrictions block install, show the real reason and a direct download/manual install path.
- Keep customer trust even when shared hosting blocks unattended plugin replacement.

### Issue: Sharpen operator-facing messaging
- Position Site Pilot AI as a reusable AI production system for WordPress.
- Focus on speed, consistency, memory, and reusable structures rather than generic “AI for WordPress” messaging.
- Status: In progress. README and plugin readme now emphasize operators, reusable structure, site character, and design-reference workflows.

### Issue: Validate the full operator loop on real sites
- Verify landing pages, blog archetypes, WooCommerce product archetypes, Elementor 4, and mixed human+AI editing flows on real environments.

## Later

### Issue: Finish polished Figma OAuth
- Complete and verify the end-to-end OAuth flow on real sites.
- Keep Figma as a design-input system, not the primary source of site memory.

### Issue: Add Stitch and other design connectors
- Treat them as intake channels that feed design references, archetypes, and parts.

### Issue: Add per-page and per-product override UI
- Let operators manage inherited site character and archetype-specific briefs in admin directly.

### Issue: Team workflows
- Add approvals, handoff states, and governance for shared asset libraries.

### Issue: Usage analytics for reusable assets
- Show which references, archetypes, and parts are reused most.
- Surface what is saving time and where the operator system is failing.

## Current Execution Order

1. Validate the full operator loop on real sites
2. Tighten remaining release surfaces and website/version consistency
3. Continue Library preview and operational quick-action polish
