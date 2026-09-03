# Agent skills for phpro/http-tools

Skills that teach a coding agent how to build integrations with this library. They live in
`.agents/skills/` (the cross-runtime location: Claude Code, Codex, Copilot CLI and Gemini CLI all
read it, some via a `.claude/skills` symlink which is git-ignored here).

They are written for the **consumer** of this package — someone integrating a third-party API in
their own application — not for contributors to the library itself.

## Skills

| Skill | Use when |
|---|---|
| [`generate-http-api-call`](generate-http-api-call/SKILL.md) | Integrating a whole endpoint: request + response + handler + tests. Orchestrates the four below. |
| [`configure-http-client`](configure-http-client/SKILL.md) | Client and transport setup: base URI, auth, logging, plugins, preset choice. Once per API. |
| [`generate-http-request`](generate-http-request/SKILL.md) | Writing a `RequestInterface` model: URI templates, parameters, body. |
| [`generate-http-response`](generate-http-response/SKILL.md) | Turning a decoded payload into a strictly typed value object. |
| [`generate-http-request-handler`](generate-http-request-handler/SKILL.md) | The class that runs one call, and where error handling belongs. |
| [`test-http-integration`](test-http-integration/SKILL.md) | Mock client vs VCR cassettes, and what each layer should assert. |

Each is usable on its own — ask for just a response model and you get just that skill.

## The conventions they encode

1. **One vertical slice per endpoint**, grouped per endpoint on disk — not `Model/`, `Request/`, `RequestHandler/` folders.
2. **A handler interface for every endpoint**, so consumers mock one call instead of an API client.
3. **Strictly typed response models, validated once** at the boundary. Never a stored raw array with `?? null` accessors. `psl/type` is the recommended validator, not a requirement.
4. **Client and transport configured once per API**, in a factory the tests reuse — so the tested plugin stack is the production one.
5. **Error handling at the layer that owns it**: plugin → transport decorator → handler, in that order of preference.
6. **Handler tests run through the real transport** against a recorded cassette; mock clients are for plugins, encoders and error paths.

## Keeping them honest

The examples use an imaginary "Crumbs Bakery" API (`App\Infrastructure\Bakery`, `GET /orders/{orderId}`)
throughout, so snippets compose across skills. Every library API they reference exists on this branch —
when the library changes, update the skills alongside it.
