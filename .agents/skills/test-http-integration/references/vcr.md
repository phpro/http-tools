# VCR cassettes reference

`php-http/vcr-plugin` records real HTTP responses to files and replays them on later runs. That gives request-handler tests one honest round-trip against the real API, then permanent offline reruns through the full stack.

```bash
composer require --dev php-http/vcr-plugin
```

## How the two plugins interact

| Plugin | Role |
|---|---|
| `ReplayPlugin($namingStrategy, $player, bool $throw)` | Looks for a cassette matching the request. Found → returns it and stops. |
| `RecordPlugin($namingStrategy, $recorder)` | Writes the response to a cassette after a real request. |

`ClientBuilder::addRecording()` installs both with `throw: false`, which produces the self-recording behaviour you want:

1. **No cassette, network available** → real request, cassette written.
2. **Cassette present** → replayed, no network. Response carries `X-VCR-REPLAYED`.
3. **No cassette, no network** → the test fails with a connection error.

Case 3 is the expected state in CI for a cassette you forgot to commit. `useRecording()` from `UseVcrClient` also uses `throw: false`; construct `ReplayPlugin` yourself with `throw: true` if you would rather fail loudly than silently hit the network.

## Naming strategies

`PathNamingStrategy` builds the filename from host, method, path, a hash of selected headers, a hash of the query string, and — for `PUT`/`POST`/`PATCH` — a hash of the body:

```
tb-bakery-test.example.com_GET_orders_ord-1.txt
tb-bakery-test.example.com_POST_orders_3f0a1.txt
```

```php
new PathNamingStrategy([
    'hash_headers' => ['X-Bakery-Key'],          // default: []
    'hash_body_methods' => ['PUT', 'POST', 'PATCH'], // the default
]);
```

Consequences to plan for:

- **Body hashing means POST cassettes are payload-specific.** Change one field in the request model and the cassette no longer matches; you get a re-record (or a CI failure). That's a feature — the recorded response really did correspond to that payload.
- **`hash_headers` when the credential varies per test.** Add the header so two users don't collide on one cassette; leave it out when the credential is constant, or every rotation invalidates every cassette.
- **The host is part of the name.** Pointing tests at a different environment invalidates all cassettes. Keep the base URI stable via a committed test env var.

Write your own `NamingStrategyInterface` when you need something else — for instance including the test method name so each test owns its cassettes:

```php
final readonly class TestAwareNamingStrategy implements NamingStrategyInterface
{
    public function __construct(
        private string $prefix,
        private NamingStrategyInterface $inner = new PathNamingStrategy(),
    ) {
    }

    public function name(RequestInterface $request): string
    {
        return $this->prefix.'_'.$this->inner->name($request);
    }
}
```

## Sanitising secrets before they hit disk

Cassettes are serialized HTTP responses, and they go into version control. `FilesystemRecorder`'s third argument is a map of **regex pattern → replacement**, applied at record time:

```php
new FilesystemRecorder(
    FIXTURE_DIR.'/Bakery',
    filters: [
        '/Set-Cookie: .*/' => 'Set-Cookie: [REDACTED]',
        '/"token":"[^"]+"/' => '"token":"[REDACTED]"',
        '/[\w.+-]+@[\w-]+\.[\w.]+/' => 'redacted@example.com',
    ],
);
```

This only covers the **response**. The request is not stored at all, so request-side credentials never land in a cassette — but they do land in *logs* if you also attached a logger, which is what the sensitive-header formatters are for (see `configure-http-client`).

Rules worth keeping:

- Record against a test account with fabricated data, never production.
- Read a new cassette before committing it. Once it's in git history, a leaked token is leaked.
- Never sanitise by hand-editing a cassette without also adding the filter — the next re-record puts the secret back.

## Where cassettes live

Commit them next to the fixtures, grouped per integration:

```
tests/Fixtures/Bakery/
├── bakery-test.example.com_GET_orders_ord-1.txt
└── bakery-test.example.com_POST_orders_3f0a1.txt
```

Define `FIXTURE_DIR` in `tests/bootstrap.php`:

```php
define('FIXTURE_DIR', __DIR__.'/Fixtures');
```

`FilesystemRecorder` creates the directory if it's missing; `UseVcrClient::useRecording()` asserts it exists, which turns a typo into an immediate, clear failure rather than a mystery cassette written somewhere else.

## Re-recording

When the API changes, or you change a request payload:

1. Delete the affected cassette files.
2. Make sure the test env vars point at a reachable test environment.
3. Run the test — it records again.
4. Read the new cassette, check it holds no secrets, commit it.

Only delete the cassettes you mean to re-record. Wiping the directory turns one focused re-record into a full-suite network run.

## Diagnosing a failing cassette test

| Symptom | Cause |
|---|---|
| `Unable to find a response to replay request "…"` | `throw: true` and no matching cassette. Check the generated name against the filenames. |
| Connection refused / DNS failure in CI | Cassette missing or misnamed; the test fell through to a real request. |
| Passes locally, fails in CI | Cassette not committed, or `FIXTURE_DIR` differs. |
| Suddenly re-records on every run | Something in the name is unstable — a rotating credential in `hash_headers`, a timestamp or a random id in the query or body. |
| `Psl\Type` coercion error on replay | The cassette predates a model change, or the API changed shape. Compare cassette to `::type()`. |
| Response replayed but assertions fail | Read the cassette. It is a plain text HTTP response — usually the fastest debugging tool you have. |

## When not to use VCR

- **Error paths.** A 404 or a malformed payload is easier and clearer with `UseMockClient` than with a recorded cassette.
- **Plugins, encoders, decoders.** No real API needed — mock client.
- **Model parsing.** No HTTP at all.
- **APIs with no test environment.** Record once by hand, or fall back to a mock client and accept that you're testing less.
