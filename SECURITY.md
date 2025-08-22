# Security Policy

Thank you for helping keep Contributor Photo Gallery and its users safe.

## Supported Versions

The following versions currently receive security updates and fixes.

| Version                | Supported |
| --------------------- | --------- |
| main (development)    | ✅        |
| latest tagged release | ✅        |
| older than last tag   | ❌        |

Notes:
- If formal releases with semantic versioning are introduced, this table will be updated to list explicit supported ranges (for example, 1.2.x).
- Security fixes are backported to the latest tagged release when feasible.

## Reporting a Vulnerability

Please report security issues privately so there’s time to fix them before public disclosure.

- Preferred: send details via the contact form at https://satyamvishwakarma.com/contact with the subject “Security - Contributor Photo Gallery”.
- If possible, include:
  - A clear description of the issue and potential impact.
  - Steps to reproduce (PoC if available).
  - Affected versions/branches (e.g., commit SHA, tag).
  - Environment details (WordPress version, PHP version, theme, relevant plugins).

Response targets:
- Acknowledgment: within 72 hours.
- Triage/initial assessment: within 7 days.
- Target fix window:
  - Critical/High: within 30 days
  - Medium: within 60 days
  - Low: within 90 days

Coordinated disclosure:
- We prefer coordinated disclosure. We can publish a GitHub Security Advisory and, if appropriate, request a CVE.
- Public disclosure happens after a fix is available and tested, unless there is active exploitation and early notice is necessary.

## Scope

In scope:
- Code in this repository that runs as part of the WordPress plugin, including:
  - Shortcodes, data fetching/processing, templating, and any remote calls performed by plugin logic.

Out of scope:
- Vulnerabilities in WordPress core or other themes/plugins.
- Issues caused solely by unsupported WordPress/PHP versions or misconfiguration.

## Security Guidance for Contributors

- Do not commit secrets (API keys, tokens, passwords). Use local env/config files and ensure they are ignored by VCS.
- Validate, sanitize, and escape:
  - Sanitize all inputs (including shortcode attributes and request data).
  - Escape outputs and follow WordPress security best practices (nonces, capability checks, prepared statements, etc.).
- Avoid unsafe operations:
  - No unsafe deserialization, eval, or unvalidated file/remote operations.
- Keep dependencies updated and address vulnerability alerts promptly.
- Prefer least privilege and minimal data exposure in admin and public contexts.

## Credits

We appreciate responsible disclosure. With consent, reporters may be credited in release notes after remediation.

