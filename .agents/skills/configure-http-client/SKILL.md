---
name: configure-http-client
description: Use when setting up or changing the HTTP client and transport for a phpro/http-tools integration — base URI, authentication headers, logging with sensitive data stripped, retries, plugin order, choosing a transport preset or encoder/decoder, or making the same wiring reusable from tests. Triggers on "configure the client", "add a plugin", "set the base url", "how do I log requests", "which preset should I use", "add auth to the API client".
---

# Configure an HTTP client and transport

## Overview

Every integration gets three small classes, written once and reused by **all** its request handlers *and its tests*:

| Class | Responsibility |
|---|---|
| `…ClientConfig` | The variable inputs: base URI, credentials, logger. A readonly DTO. |
| `…ClientFactory` | Config → PSR-18 `ClientInterface`, via `ClientBuilder` plugins. |
| `…TransportFactory` | Client → `TransportInterface`, via a preset + any decorators. |

**Core principle: the same factory the application uses must be usable from a test.** If a test has to rebuild the plugin stack by hand, the two will drift and your tests will pass against a client that doesn't exist in production. So: take the seams as constructor arguments (logger, recorder, base URI) rather than reaching for globals inside.

## When to Use

- Starting a new API integration (step 0 of `generate-http-api-call`).
- Adding authentication, logging, retries or a header to an existing integration.
- Choosing between `JsonPreset`, `RawPreset` and friends, or composing a custom encoder/decoder.

## The three classes

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Bakery;

use Psr\Log\LoggerInterface;

final readonly class BakeryClientConfig
{
    public function __construct(
        public string $apiUri,
        #[\SensitiveParameter]
        public string $apiKey,
        public LoggerInterface $logger,
        public bool $debug = false,
    ) {
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Bakery;

use Phpro\HttpTools\Client\ClientBuilder;
use Phpro\HttpTools\Formatter\FormatterBuilder;
use Phpro\HttpTools\Formatter\RemoveSensitiveHeadersFormatter;
use Psr\Http\Client\ClientInterface;

final readonly class BakeryClientFactory
{
    public static function create(BakeryClientConfig $config): ClientInterface
    {
        return ClientBuilder::default()
            ->addBaseUri($config->apiUri)
            ->addHeaders(['X-Bakery-Key' => $config->apiKey])
            ->addLogger(
                $config->logger,
                FormatterBuilder::default()
                    ->withDebug($config->debug)
                    ->withMaxBodyLength(1000)
                    ->addDecorator(RemoveSensitiveHeadersFormatter::createDecorator([
                        'X-Bakery-Key',
                    ]))
                    ->build(),
            )
            ->build();
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Bakery;

use Phpro\HttpTools\Client\ClientBuilder;
use Phpro\HttpTools\Transport\Presets\JsonPreset;
use Phpro\HttpTools\Transport\TransportInterface;
use Phpro\HttpTools\Uri\TemplatedUriBuilder;
use Psr\Http\Client\ClientInterface;

final readonly class BakeryTransportFactory
{
    public function __construct(
        private BakeryClientConfig $config,
    ) {
    }

    /**
     * @return TransportInterface<array|null, array>
     */
    public function create(?ClientInterface $client = null): TransportInterface
    {
        return new BakeryErrorHandlingTransport(
            JsonPreset::create(
                $client ?? BakeryClientFactory::create($this->config),
                new TemplatedUriBuilder(),
            ),
        );
    }
}
```

The optional `?ClientInterface $client` argument is the seam that makes this factory usable from a test: production passes nothing, a test passes a mock or recording client and still gets the real preset and the real error-handling decorator. See `test-http-integration`.

`ClientBuilder::default()` already installs `ErrorPlugin`, so 4xx/5xx throw `ClientErrorException`/`ServerErrorException`. Use the bare constructor `new ClientBuilder()` only when you deliberately want to inspect error responses yourself.

## Plugin order matters

`ClientBuilder` orders plugins by priority, highest first, so you don't have to think about array order:

| Constant | Value | For |
|---|---|---|
| `ClientBuilder::PRIORITY_LEVEL_LOGGING` | 2000 | logging, recording — runs outermost, sees the final request |
| `ClientBuilder::PRIORITY_LEVEL_SECURITY` | 1000 | authentication |
| `ClientBuilder::PRIORITY_LEVEL_DEFAULT` | 0 | everything else |

`addLogger()` and `addRecording()` default to logging priority; `addAuthentication()` defaults to security. Only pass an explicit `priority:` when you need something between the levels — for instance a plugin that must run *before* authentication signs the request.

Getting this wrong is subtle: a logger at default priority logs the request *before* the auth plugin adds its header, so your logs and your cassettes won't match what went over the wire.

## Builder methods

| Method | Adds |
|---|---|
| `addBaseUri($uri, replaceHost: true)` | `BaseUriPlugin` — request models keep relative paths |
| `addHeaders(['X-Key' => '…'])` | `HeaderSetPlugin` — static headers, API keys |
| `addAuthentication($auth)` | `AuthenticationPlugin` — any `Http\Message\Authentication` (`BasicAuth`, `Bearer`, `Header`, `QueryParam`) |
| `addLogger($logger, $formatter)` | `LoggerPlugin` |
| `addRecording($namingStrategy, $recorder)` | VCR record + replay — for tests, see `test-http-integration` |
| `addPlugin($plugin, priority: …)` | any HTTPlug plugin |
| `addCallback(fn, priority: …)` | promotes a closure to a plugin, no class needed |
| `addPluginWithCurrentlyConfiguredClient(fn)` | a plugin that needs to call the API itself (OAuth token fetch) |
| `addDecorator(fn(ClientInterface): ClientInterface)` | wraps the client itself, not a plugin |

Plugin catalogue, custom plugins, and the OAuth-token-refresh pattern: [references/plugins.md](references/plugins.md).

**Before writing a plugin, check whether HTTPlug already has one** — retry, redirect, cookies, caching, decoding, history and more are [already available](http://docs.php-http.org/en/latest/plugins/).

## Choosing a transport

Presets pair an encoder with a decoder. Pick by what goes over the wire, not by what your models look like.

| Preset | `TransportInterface<…>` | Use for |
|---|---|---|
| `JsonPreset` | `<array\|null, array>` | JSON APIs — the common case |
| `FormUrlencodedPreset` | `<array\|null, array>` | `application/x-www-form-urlencoded` submissions |
| `RawPreset` | `<string, string>` | XML, CSV, plain text |
| `PsrPreset` | `<string, ResponseInterface>` | you need headers/status in the handler |
| `BinaryDownloadPreset::withEmptyRequest` | `<null, BinaryFile>` | downloads |
| `BinaryDownloadPreset::withMultiPartRequest` | `<MultiPart, BinaryFile>` | uploads returning a file |

Mixing encodings (JSON out, raw in; multipart out, JSON in) means composing your own — the full encoder/decoder matrix is in [references/transports.md](references/transports.md).

Pair a preset with `new TemplatedUriBuilder()` unless your request models return finished URIs, in which case use `RawUriBuilder::createWithAutodiscoveredPsrFactories()`.

## Transport decorators

API-wide behaviour that isn't HTTP-level belongs in a decorator around the preset, composed once in the transport factory: error envelopes returned under `200`, `application/problem+json` translation, unwrapping a `{"data": …}` envelope before handlers see it.

A decorator implements `TransportInterface` and delegates — see `generate-http-request-handler` for a worked example. Handlers stay unchanged, and every endpoint inherits the behaviour.

## Symfony wiring

```yaml
services:
    App\Infrastructure\Bakery\BakeryClientConfig:
        arguments:
            $apiUri: '%env(APP_BAKERY_API_URI)%'
            $apiKey: '%env(APP_BAKERY_API_KEY)%'
            $logger: '@monolog.logger.bakery'
            $debug: '%kernel.debug%'

    App\Infrastructure\Bakery\BakeryTransportFactory: ~

    bakery.transport:
        class: Phpro\HttpTools\Transport\TransportInterface
        factory: ['@App\Infrastructure\Bakery\BakeryTransportFactory', 'create']

    App\Infrastructure\Bakery\Order\:
        resource: '../src/Infrastructure/Bakery/Order/*/*RequestHandler.php'
        arguments: ['@bakery.transport']
```

Credentials come from env vars, never from a constant or a committed config file. More on framework integration: `docs/framework/symfony.md`.

## Common Mistakes

| Mistake | Fix |
|---|---|
| Tests rebuild the plugin stack by hand | Give the factory an optional `?ClientInterface` and reuse it. |
| A second transport factory for the same API | One per API; the plugin stacks will otherwise drift. |
| Base URI hardcoded in request models' `uri()` | `addBaseUri()` on the client; keep `uri()` relative. |
| API key logged in plaintext | `RemoveSensitiveHeadersFormatter` / `RemoveSensitiveJsonKeysFormatter` / `RemoveSensitiveQueryStringsFormatter` decorators on the formatter. |
| Logger added at default priority | Leave it at `PRIORITY_LEVEL_LOGGING` so it sees the fully-built request. |
| `new ClientBuilder()` then surprised 404s don't throw | Use `ClientBuilder::default()` for `ErrorPlugin`. |
| Custom plugin for something HTTPlug ships | Check the plugin list first. |
| Retry logic in a handler | `RetryPlugin` on the client. |
| Client built inline inside a request handler | Handlers receive a `TransportInterface`, nothing else. |
