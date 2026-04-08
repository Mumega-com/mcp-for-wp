## What this does

## Why

## How to test

1. Start Docker: `cd wp-test && docker compose up -d`
2. Generate key: see CONTRIBUTING.md
3. Test: 

## Checklist

- [ ] Tested on Docker test site
- [ ] No new `console.log` or `error_log` in production code
- [ ] All functions/classes prefixed with `spai_` / `Spai_` / `SPAI_`
- [ ] Text domain `site-pilot-ai` on all translatable strings
