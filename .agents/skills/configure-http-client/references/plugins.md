# Plugins reference

A plugin is HTTPlug middleware: it sees the PSR-7 request on the way out and the response on the way back. Everything cross-cutting belongs here — authentication, logging, retries, headers — never in a request handler.

## Check before you build

HTTPlug already ships most of what you need. [Full list](http://docs.php-http.org/en/latest/plugins/) — the ones that come up in practice:

| Plugin | Namespace `Http\Client\Common\Plugin\` | Notes |
|---|---|---|
| `ErrorPlugin` | | 4xx → `ClientErrorException`, 5xx → `ServerErrorException`. In `ClientBuilder::default()`. |
| `BaseUriPlugin` | | Added by `addBaseUri()`. |
| `HeaderSetPlugin` | | Added by `addHeaders()`. Also `HeaderDefaultsPlugin`, `HeaderAppendPlugin`, `HeaderRemovePlugin`. |
| `AuthenticationPlugin` | | Added by `addAuthentication()`. |
| `RetryPlugin` | | `['retries' => 3]`. Retries on exceptions by default. |
| `RedirectPlugin` | | Follows 3xx. |
| `CookiePlugin` | | Needs a `CookieJar`. |
| `DecoderPlugin` | | Transparent gzip/deflate. |
| `HistoryPlugin` | | Records requests via a journal — handy in tests. |
| `ContentLengthPlugin`, `ContentTypePlugin` | | Fill in the obvious headers. |
| `CachePlugin` | | Needs a PSR-6 pool. |
| `LoggerPlugin` | `Http\Client\Common\Plugin\LoggerPlugin` | Added by `addLogger()`. |
| `RecordPlugin` / `ReplayPlugin` | `Http\Client\Plugin\Vcr\` | Added by `addRecording()`. See `test-http-integration`. |

From this library:

| Plugin | Purpose |
|---|---|
| `Phpro\HttpTools\Plugin\AcceptLanguagePlugin` | Sets `Accept-Language`. |
| `Phpro\HttpTools\Plugin\CallbackPlugin` | Promotes a closure into a plugin. |

## Authentication

`Http\Message\Authentication` implementations, passed to `addAuthentication()`:

```php
use Http\Message\Authentication\BasicAuth;
use Http\Message\Authentication\Bearer;
use Http\Message\Authentication\Header;
use Http\Message\Authentication\QueryParam;
use Http\Message\Authentication\Chain;
use Http\Message\Authentication\Matching;
use Http\Message\Authentication\RequestConditional;
use Http\Message\Authentication\Wsse;

ClientBuilder::default()
    ->addAuthentication(new BasicAuth($config->username, $config->password))
    ->addAuthentication(new Bearer($config->token))
    ->addAuthentication(new Header('X-Bakery-Key', $config->apiKey))
    ->addAuthentication(new QueryParam(['api_key' => $config->apiKey]))
    ->build();
```

A single static header is equally fine via `addHeaders(['X-Bakery-Key' => …])`. Prefer `addAuthentication()` when the value is a credential — it lands at `PRIORITY_LEVEL_SECURITY`, which keeps it inside the logging plugin and makes the intent obvious.

### Per-request credentials

When the credential depends on runtime state (the current user, a tenant), build the plugin from a service rather than a config value:

```php
final readonly class RemoteUserPluginFactory
{
    public static function create(CurrentUserLoader $currentUserLoader): Plugin\AuthenticationPlugin
    {
        return new Plugin\AuthenticationPlugin(
            new Authentication\Header('X-Remote-User', $currentUserLoader->load()->username()),
        );
    }
}
```

The factory is then also usable from a test with a stubbed loader — the whole reason it is a factory and not an inline `new`.

### Tokens fetched from the API itself

`addPluginWithCurrentlyConfiguredClient()` hands you the client as configured *so far*, so a token-fetch plugin can call the API without a circular dependency:

```php
ClientBuilder::default()
    ->addBaseUri($config->apiUri)
    ->addPluginWithCurrentlyConfiguredClient(
        static fn (ClientInterface $client): Plugin => new OAuthTokenPlugin(
            new FetchTokenRequestHandler(
                JsonPreset::create($client, new TemplatedUriBuilder()),
            ),
            $tokenCache,
        ),
        priority: ClientBuilder::PRIORITY_LEVEL_SECURITY,
    )
    ->build();
```

Cache the token. Without a cache this fetches one per request.

## Custom plugins

Only when nothing existing fits. For anything small, `addCallback()` avoids a class entirely:

```php
$builder->addCallback(
    static fn (RequestInterface $request, callable $next, callable $first): Promise
        => $next($request->withHeader('X-Correlation-Id', $correlationId)),
);
```

The three arguments are HTTPlug's: `$next` continues down the chain, `$first` restarts it from the top (used by redirect and retry plugins).

A full class when the logic warrants one:

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Bakery\Plugin;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class BakeryTenantPlugin implements Plugin
{
    public function __construct(
        private TenantContext $tenantContext,
    ) {
    }

    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        return $next(
            $request->withHeader('X-Tenant', $this->tenantContext->current()->id),
        )->then(static function (ResponseInterface $response): ResponseInterface {
            // Inspect or rewrite the response here if needed.
            return $response;
        });
    }
}
```

`handleRequest()` must return the promise from `$next()`/`$first()`. Returning a response directly, or forgetting to return at all, breaks the chain in ways that are hard to debug.

## Priorities

```php
ClientBuilder::PRIORITY_LEVEL_LOGGING   // 2000 — outermost
ClientBuilder::PRIORITY_LEVEL_SECURITY  // 1000
ClientBuilder::PRIORITY_LEVEL_DEFAULT   //    0 — innermost
```

Higher priority runs earlier on the way out, so it sees the request *before* lower-priority plugins modify it — and it is the last to see the response on the way back.

Consequences worth internalising:

- Logging and recording sit at 2000 so they capture the request as it will actually be sent, including auth headers (which the sensitive-header formatter then strips from the log).
- A plugin that must inspect the *signed* request needs a priority **below** security, not above.
- Between-level ordering is what the explicit `priority:` argument is for: `priority: 1500` runs after logging but before authentication.

## Logging without leaking secrets

`FormatterBuilder` composes the formatter that `LoggerPlugin` uses:

```php
FormatterBuilder::default()
    ->withDebug($config->debug)        // false: one line per request; true: full headers + body
    ->withMaxBodyLength(1000)
    ->addDecorator(RemoveSensitiveHeadersFormatter::createDecorator([
        'X-Bakery-Key',
        'Authorization',
    ]))
    ->addDecorator(RemoveSensitiveJsonKeysFormatter::createDecorator([
        'password',
        'refreshToken',
    ]))
    ->addDecorator(RemoveSensitiveQueryStringsFormatter::createDecorator([
        'api_key',
    ]))
    ->build();
```

Add the decorator for every credential the integration handles, in whichever place it travels — header, JSON body, query string. Debug logging that dumps an API key into your log aggregator is a security incident, not a debugging convenience.

Each decorator is also usable directly as a constructor-wrapping formatter (`new RemoveSensitiveHeadersFormatter($inner, [...])`); `createDecorator()` exists so it composes in the builder.

## Decorators vs plugins

`addDecorator(fn (ClientInterface $client): ClientInterface)` wraps the *client object*, not the request chain. Use it when you need to replace or wrap the client itself — a fiber-aware client, an in-memory fake, instrumentation that isn't request-shaped. For anything that reads or rewrites requests and responses, use a plugin.
