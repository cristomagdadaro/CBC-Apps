# Controller Refactor Prompt (Docs Mirror)

Canonical file: [.github/prompts/agent.refactor-controllers.prompt.md](../.github/prompts/agent.refactor-controllers.prompt.md)

This docs copy intentionally mirrors only the active guardrails; the full working prompt now lives under `.github/prompts/`.

## 2026-03-30 Refactor Guardrails
- `BaseController` and repository standardization still apply.
- For guest/public controllers, return explicit safe payloads instead of raw models.
- Never trust request `employee_id`, email, or similar identifiers for authorization; use auth context or signed callbacks/tokens.
- Keep guest mutations behind auth or signed kiosk flows.
- Update tests, regenerate Ziggy when routes change, and record newly found issues in the tracker report.
