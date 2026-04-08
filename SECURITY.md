# Security Policy

## Supported Versions

| Version | Supported |
|---------|-----------|
| 2.6.x   | Yes       |
| 2.5.x   | Security fixes only |
| < 2.5   | No        |

## Reporting a Vulnerability

**Do not file a public GitHub issue for security vulnerabilities.**

Email **security@mumega.com** with:
- Description of the vulnerability
- Steps to reproduce
- Impact assessment
- Your suggested fix (if any)

We will acknowledge within 48 hours and provide a fix timeline within 7 days.

## Security Features

mumcp requires API key authentication for all MCP and REST operations. Keys are hashed using WordPress password hashing (not stored in plain text). A dedicated service account with limited capabilities handles API requests.

- All endpoints require `X-API-Key` header
- Role-scoped keys limit tool access per API key
- Activity logging tracks all API calls
- Rate limiting prevents abuse
- No user data leaves the WordPress installation (see Privacy Policy)
