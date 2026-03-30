# CBC-Apps Copilot Instructions (Docs Mirror)

Canonical file: [.github/copilot-instructions.md](../.github/copilot-instructions.md)

This docs copy is intentionally lightweight so the repository keeps one authoritative instruction source in `.github/`.

## 2026-03-30 Sync Points
- Treat `api/guest/*` as a public surface. Default to read-only, sanitize payloads, and never authorize by caller-supplied identifiers.
- Keep public rental availability and detail responses privacy-safe even for authenticated callers using guest routes.
- Keep public personnel lookup exact-match-only and limited to display-safe identity fields.
- Store generated PDFs in `storage/app/private/generated-pdfs`, not `public/`.
- Update [codebase-analysis-report-2026-03-25.md](./codebase-analysis-report-2026-03-25.md) when you discover or resolve issues.
- Regenerate `resources/js/ziggy.js` after route changes and keep `tests/setup.ts` present when Vitest references it.
