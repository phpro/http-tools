---
name: test-http-integration
description: Use when writing or fixing tests for a phpro/http-tools integration — testing request models, response parsing, request handlers, custom plugins or transport decorators — or when deciding between a mock client and VCR cassette recording. Triggers on "test this API call", "how do I mock the HTTP client", "record a cassette", "my VCR test fails offline", "test the request handler".
---

# Test an HTTP integration

## Overview

Four layers, each with a different tool. Picking the wrong tool is what makes these tests either useless or permanently broken.

| What you're testing | Tool | Test type |
|---|---|---|
| Request model: method, URI template, body mapping | nothing — plain assertions | unit |
| Response model: payload → typed object, and rejection of bad payloads | nothing — call `::parse()` | unit |
| Custom plugin, encoder/decoder, transport decorator | `UseMockClient` (`php-http/mock-client`) | unit |
| **Request handler**, end to end through the real transport | `addRecording()` / `UseVcrClient` — VCR cassettes | integration |
| Application service that *calls* a handler | stub the handler interface | unit |

**Core principle: handler tests go through the real transport.** A handler test that mocks `TransportInterface` proves only that you can call a closure — it exercises none of the URI templating, encoding, plugin stack or decoding, which is where integrations actually break.

## When to Use

- Step 4 of `generate-http-api-call`.
- A VCR test fails and you need to know whether to re-record.
- Deciding how to test something that talks to an API.

## Layer 1 — models, no HTTP

```php
final class ListOrdersRequestTest extends TestCase
{
    #[Test]
    public function it_omits_the_status_filter_when_listing_all(): void
    {
        $request = ListOrdersRequest::all(page: 2);

        self::assertSame('GET', $request->method());
        self::assertSame('/orders{?status,page}', $request->uri());
        self::assertSame(['status' => null, 'page' => 2], $request->uriParameters());
        self::assertNull($request->body());
    }
}
```

Response parsing is equally cheap — payload in, object out, plus one test per way the API can lie to you. Examples in `generate-http-response`'s [psl-types reference](../generate-http-response/references/psl-types.md).

Assert the *template*, not the expanded URI. Expansion is `TemplatedUriBuilder`'s job and it already has tests here.

## Layer 2 — plugins and transports, mock client

`UseMockClient` gives you `mockClient()` (an `Http\Mock\Client`) and, through `UseHttpFactories`, `createRequest()` / `createResponse()` / `createStream()` / `createEmptyHttpClientException()`. `UseHttpToolsFactories` adds `createToolsRequest()` for building request models inline.

```php
final class BakeryTenantPluginTest extends TestCase
{
    use UseMockClient;
    use UseHttpToolsFactories;

    #[Test]
    public function it_adds_the_tenant_header(): void
    {
        $client = $this->mockClient(function (Client $client): Client {
            $client->setDefaultResponse($this->createResponse(200));

            return $client;
        });

        $configured = ClientBuilder::default($client)
            ->addPlugin(new BakeryTenantPlugin(new FixedTenantContext('crumbs-be')))
            ->build();

        $configured->sendRequest($this->createRequest('GET', '/orders'));

        self::assertSame('crumbs-be', $client->getLastRequest()->getHeaderLine('X-Tenant'));
    }
}
```

`mockClient()` without a configurator returns a bare client; add responses with `addResponse()` (queued, in order) or `setDefaultResponse()`. `setDefaultException()` is the honest default for a client that should never be called:

```php
$this->mockClient(function (Client $client): Client {
    $client->setDefaultException(new \Exception('Dont call me!'));

    return $client;
});
```

Then inspect `getLastRequest()` / `getRequests()` to assert what went out.

Same pattern for a transport decorator: build the real preset over a mock client, queue the payload, assert the decorator's behaviour.

## Layer 3 — request handlers, VCR cassettes

VCR records real HTTP traffic to files on first run and replays it forever after. That means one honest round-trip against the real API, then a fast offline test that still exercises your entire stack.

**Reuse the production transport factory.** This is the whole reason it takes an optional client:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Bakery;

use App\Infrastructure\Bakery\BakeryClientConfig;
use App\Infrastructure\Bakery\BakeryTransportFactory;
use Http\Client\Plugin\Vcr\NamingStrategy\PathNamingStrategy;
use Http\Client\Plugin\Vcr\Recorder\FilesystemRecorder;
use Phpro\HttpTools\Client\ClientBuilder;
use Phpro\HttpTools\Test\UseHttpFactories;
use Phpro\HttpTools\Transport\TransportInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

abstract class BakeryWebserviceTestCase extends TestCase
{
    use UseHttpFactories;

    /**
     * @return TransportInterface<array|null, array>
     */
    protected function createTransport(): TransportInterface
    {
        $config = new BakeryClientConfig(
            apiUri: $_ENV['APP_BAKERY_API_URI'],
            apiKey: $_ENV['APP_BAKERY_API_KEY'],
            logger: new NullLogger(),
        );

        $recordingClient = ClientBuilder::default()
            ->addBaseUri($config->apiUri)
            ->addHeaders(['X-Bakery-Key' => $config->apiKey])
            ->addRecording(
                new PathNamingStrategy(['hash_headers' => ['X-Bakery-Key']]),
                new FilesystemRecorder(FIXTURE_DIR.'/Bakery'),
            )
            ->build();

        return (new BakeryTransportFactory($config))->create($recordingClient);
    }
}
```

The test itself then reads like application code:

```php
final class FetchOrderRequestHandlerTest extends BakeryWebserviceTestCase
{
    #[Test]
    public function it_fetches_an_order(): void
    {
        $handler = new FetchOrderRequestHandler($this->createTransport());

        $order = $handler->handle(new FetchOrderRequest('ord-1'));

        self::assertSame('ord-1', $order->id);
        self::assertSame(OrderStatus::Baking, $order->status);
    }
}
```

`addRecording()` installs both `RecordPlugin` and `ReplayPlugin` at logging priority, so the cassette contains the request as it was actually sent — auth headers included. Alternatively `UseVcrClient::useRecording($path, $namingStrategy)` returns the plugin pair for a client factory that takes a plugin list; it asserts the directory exists, which catches a mistyped fixture path immediately.

Cassette details, naming strategies and re-recording: [references/vcr.md](references/vcr.md).

## Layer 4 — consumers stub the interface

Everything that *uses* a handler depends on `…RequestHandlerInterface`, so its tests need no HTTP at all:

```php
$fetchOrder = new class implements FetchOrderRequestHandlerInterface {
    public function handle(FetchOrderRequest $request): Order
    {
        return new Order('ord-1', OrderStatus::Ready, 3, new Customer('Jo', 'jo@example.com'), null);
    }
};

(new NotifyCustomerWhenReady($fetchOrder, $mailer))('ord-1');
```

This is what the handler interface buys you. Without it, every consumer test drags in a transport.

## Error paths

Endpoint-specific error translation is worth a test, and a mock client is the right tool — a cassette for a 404 is more trouble than it's worth:

```php
#[Test]
public function it_throws_when_the_order_does_not_exist(): void
{
    $client = $this->mockClient(function (Client $client): Client {
        $client->setDefaultResponse($this->createResponse(404));

        return $client;
    });

    $handler = new FetchOrderRequestHandler(
        (new BakeryTransportFactory($this->config()))->create($client),
    );

    $this->expectException(OrderNotFound::class);

    $handler->handle(new FetchOrderRequest('nope'));
}
```

The transport still comes from the real factory, so `ErrorPlugin` and the decorators are in play — only the wire response is faked.

## Common Mistakes

| Mistake | Fix |
|---|---|
| Handler test mocks `TransportInterface` | Use a cassette (or a mock *client*) through the real transport factory. |
| Test rebuilds the plugin stack by hand | Reuse the transport factory; give it an optional `?ClientInterface`. |
| Cassettes not committed | Commit them — that's what makes the test run offline and in CI. |
| Real credentials or customer data in a cassette | Sanitise before committing; record against a test account. See the VCR reference. |
| Asserting the expanded URI in a request model test | Assert the template and the parameters. |
| `expectNotToPerformAssertions()` as the whole test | Assert something about the parsed response. |
| A `Psl\Type` exception treated as a test bug | It means the API changed shape. Update the model, re-record. |
| Recording plugin added at default priority | Leave it at logging priority so the cassette matches the real request. |
