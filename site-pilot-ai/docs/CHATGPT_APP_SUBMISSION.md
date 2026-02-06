# ChatGPT App Submission Runbook

This runbook covers the end-to-end process for submitting Site Pilot AI as a public ChatGPT app after internal Developer Mode validation.

## Preconditions

Before submission, confirm:

- ChatGPT Developer Mode integration is stable.
- MCP conformance checks pass.
- Security review is complete for auth, scopes, and destructive tools.
- Privacy policy and support contact pages are publicly reachable.

Use this conformance checklist first:

- `docs/CHATGPT_CONFORMANCE.md`

## Account and Access Requirements

Submission requires:

- Verified OpenAI profile (individual or business)
- Owner role for the app in OpenAI Platform Dashboard
- Organization billing and policy setup completed

## App Metadata Package

Prepare these assets before opening the submission form:

- App name: `Site Pilot AI`
- Short description (one sentence)
- Full description and intended use cases
- App icon (square, high resolution)
- Support URL
- Privacy policy URL
- Terms of use URL (if applicable)
- Contact email

## Connector and MCP Readiness

Verify these integration details:

- Public HTTPS MCP endpoint is reachable.
- Authentication mode is configured for production (OAuth when required).
- Tool descriptions are clear and safe.
- Tool annotations are present (`readOnlyHint`, `openWorldHint`, `destructiveHint`).
- Error handling is deterministic and human-readable.

## Safety and Policy Readiness

Document how the app handles:

- Authentication and key/token revocation
- Rate limiting and abuse prevention
- Permission scoping for read/write/admin operations
- Error handling for invalid input and downstream failures
- Data handling and retention expectations

## Submission Flow

1. Open OpenAI Platform Dashboard and locate the app listing flow.
2. Enter metadata package values.
3. Provide integration details and required policy links.
4. Submit for review.
5. Track review feedback and patch issues in this repository.
6. Resubmit if required.
7. Publish after approval.

## Preflight Checklist

Complete this checklist before pressing submit:

- [ ] Conformance script and manual matrices are complete.
- [ ] Production endpoint URL and auth mode are final.
- [ ] Privacy/support/terms URLs are valid and public.
- [ ] All tool descriptions are reviewed for clarity.
- [ ] Destructive tools are clearly labeled.
- [ ] Rate limiting and logging are enabled as intended.
- [ ] GitHub issues for known limitations are linked.

## Post-Approval Publish Checklist

After approval:

- [ ] Publish the app in dashboard.
- [ ] Smoke test install from a non-owner account.
- [ ] Validate core flows (`wp_site_info`, `wp_search`, `wp_fetch`, one safe write action).
- [ ] Monitor logs for first 24 hours.
- [ ] Create follow-up issues for any production incidents.

## Release Gate for Repository

Do not mark a public release complete until:

- Submission status is approved.
- Publish step is complete.
- Repository changelog reflects app publish version and date.
