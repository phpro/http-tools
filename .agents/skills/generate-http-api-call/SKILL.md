---
name: generate-http-api-call
description: Use when integrating a new third-party HTTP/REST/JSON API endpoint with phpro/http-tools — "add a call to endpoint X", "consume the /orders API", "wire up this API in our app" — and you need the whole slice (request model, response model, request handler, transport wiring, tests) rather than a single file.
---

# Generate an HTTP API call

## Overview

One endpoint = one vertical slice. A slice is four things, in this order:

1. **Request model** — a `RequestInterface<BodyType>` value object: method, URI template, URI params, body.
2. **Response model** — a value object that *parses* the raw decoded payload via `psl/type`.
3. **Request handler** — `handle(Request): Response`, wrapping a `TransportInterface`. Plus an interface for it.
4. **Tests** — unit tests for parsing, an integration test for the handler against a recorded cassette.

The transport and the PSR-18 client are configured **once per integration**, not per endpoint.

**Core principle: never trust the API.** Raw arrays never escape the slice. The handler returns a typed model or throws.

## When to Use

- Adding any call to an external API in an app that uses `phpro/http-tools`.
- Extending an existing integration with another endpoint.

**Not for:** building the client/transport itself (use `configure-http-client`), or generic multi-endpoint SDK wrappers (see `docs/sdk.md` in this repo — request handlers are the preferred approach).

## Do it in order

Each step is its own skill. Use them when you only need one piece.

| Step | Skill | Produces |
|------|-------|----------|
| 0 | `configure-http-client` | `…ClientConfig`, `…ClientFactory`, `…TransportFactory` — **once per API**, skip if it exists |
| 1 | `generate-http-request` | `FetchOrderRequest` |
| 2 | `generate-http-response` | `Order`, `Customer`, `OrderStatus` |
| 3 | `generate-http-request-handler` | `FetchOrderRequestHandlerInterface` + `FetchOrderRequestHandler` |
| 4 | `test-http-integration` | unit + integration tests |

Do not skip step 0's check: if `…TransportFactory` already exists, reuse it. Two transport factories for one API means the plugins drift apart.

## Directory layout

Group per API, then per resource, then per endpoint. Models shared between endpoints live at resource level.

```
src/Infrastructure/Bakery/
├── BakeryClientConfig.php               # step 0
├── BakeryClientFactory.php              # step 0
├── BakeryTransportFactory.php           # step 0
└── Order/
    ├── Customer.php                     # shared response model
    ├── Order.php                        # shared response model
    ├── OrderStatus.php                  # shared enum
    ├── FetchOrder/
    │   ├── FetchOrderRequest.php
    │   ├── FetchOrderRequestHandler.php
    │   └── FetchOrderRequestHandlerInterface.php
    └── PlaceOrder/
        ├── PlaceOrderRequest.php
        ├── PlaceOrderRequestHandler.php
        └── PlaceOrderRequestHandlerInterface.php

tests/
├── Unit/Infrastructure/Bakery/Order/OrderTest.php
└── Integration/Infrastructure/Bakery/
    ├── BakeryWebserviceTestCase.php
    └── Order/FetchOrder/FetchOrderRequestHandlerTest.php
```

One handler per endpoint. A single class exposing ten endpoints forces consumers to depend on nine calls they don't make.

## Worked example

Imaginary "Crumbs Bakery" API (snippets below omit `use` statements — the per-step skills show complete files): `GET /orders/{orderId}` returns
`{"id":"…","status":"baking","loaves":3,"customer":{"name":"…","email":"…"}}`.

```php
// 1. Request — the outgoing side.
/**
 * @psalm-immutable
 *
 * @template-implements RequestInterface<null>
 */
final readonly class FetchOrderRequest implements RequestInterface
{
    public function __construct(
        public string $orderId,
    ) {
    }

    public function method(): string
    {
        return 'GET';
    }

    public function uri(): string
    {
        return '/orders/{orderId}';
    }

    public function uriParameters(): array
    {
        return ['orderId' => $this->orderId];
    }

    public function body(): null
    {
        return null;
    }
}
```

```php
// 2. Response — the incoming side. ::type() describes the payload, ::parse() enforces it.
final readonly class Order
{
    public function __construct(
        public string $id,
        public OrderStatus $status,
        public int $loaves,
        public Customer $customer,
    ) {
    }

    public static function parse(mixed $data): self
    {
        return self::type()->coerce($data);
    }

    /**
     * @return TypeInterface<self>
     */
    public static function type(): TypeInterface
    {
        return converted(
            shape([
                'id' => non_empty_string(),
                'status' => backed_enum(OrderStatus::class),
                'loaves' => int(),
                'customer' => Customer::type(),
            ]),
            instance_of(self::class),
            static fn (array $data): self => new self(
                $data['id'],
                $data['status'],
                $data['loaves'],
                $data['customer'],
            ),
        );
    }
}
```

```php
// 3. Handler — glues both sides through the transport.
final readonly class FetchOrderRequestHandler implements FetchOrderRequestHandlerInterface
{
    /**
     * @param TransportInterface<null, array> $transport
     */
    public function __construct(
        private TransportInterface $transport,
    ) {
    }

    public function handle(FetchOrderRequest $request): Order
    {
        return Order::parse(
            ($this->transport)($request),
        );
    }
}
```

The `TransportInterface<null, array>` generics must match the request's `BodyType` and the decoder's output. `null` here because `FetchOrderRequest implements RequestInterface<null>`; `array` because `JsonPreset` decodes to `array`.

## Wiring it up

The handler takes a transport, so the container binds the transport factory's output:

```yaml
services:
    App\Infrastructure\Bakery\BakeryClientConfig:
        arguments:
            $apiUri: '%env(APP_BAKERY_API_URI)%'
            $apiKey: '%env(APP_BAKERY_API_KEY)%'

    bakery.transport:
        class: Phpro\HttpTools\Transport\TransportInterface
        factory: ['@App\Infrastructure\Bakery\BakeryTransportFactory', 'create']

    App\Infrastructure\Bakery\Order\FetchOrder\FetchOrderRequestHandler:
        arguments: ['@bakery.transport']
```

## Common Mistakes

| Mistake | Fix |
|---------|-----|
| Handler returns `array` | Return a parsed value object. Raw payloads must not leave the slice. |
| Response model with getters over `$this->data['x'] ?? null` | Parse once in `::type()`; the constructor gets typed properties. |
| One handler class per API instead of per endpoint | Split it — consumers should depend only on the call they make. |
| Request model built from a raw array | Take typed constructor args (or a named constructor from a command/DTO). |
| Transport generics left as bare `TransportInterface` | Annotate `TransportInterface<RequestBody, DecodedResponse>`; psalm catches real mismatches. |
| Duplicate client/transport wiring per endpoint | One `…TransportFactory` per API, injected everywhere. |
| String literals for statuses | Backed enum + `backed_enum()` in the shape. |
