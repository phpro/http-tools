---
name: generate-http-request-handler
description: Use when writing the class that executes one API call in a phpro/http-tools integration — a request handler taking a TransportInterface and returning a typed response model — or when deciding where API error handling, retries and exception translation belong. Triggers on "add a request handler", "call the API from my service", "where do I catch HTTP errors", "translate API errors to domain exceptions".
---

# Generate an HTTP request handler

## Overview

A request handler is the seam between your application and one API endpoint. It does three things:

```php
public function handle(FetchOrderRequest $request): Order
{
    return Order::parse(($this->transport)($request));
}
```

1. Takes a request model.
2. Runs it through the injected `TransportInterface`.
3. Returns a typed response model — or throws a meaningful exception.

**One handler per endpoint.** Application services depend on the handler *interface*, so they mock one call rather than a whole API client.

## When to Use

- Wiring a request model + response model into something callable.
- Deciding where to put error handling for a specific endpoint.

Part of the `generate-http-api-call` slice.

## Always ship an interface

The handler always comes as a pair: a one-method interface and a `final readonly` implementation.

The interface is not ceremony. It is what makes the endpoint mockable in the tests of every service that calls it, without those tests knowing that HTTP exists.

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Bakery\Order\FetchOrder;

use App\Infrastructure\Bakery\Order\Order;

interface FetchOrderRequestHandlerInterface
{
    public function handle(FetchOrderRequest $request): Order;
}
```

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Bakery\Order\FetchOrder;

use App\Infrastructure\Bakery\Order\Order;
use Phpro\HttpTools\Transport\TransportInterface;

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

`handle()` (rather than `__invoke()`) keeps the class readable when a handler grows a second, related method — but `__invoke()` is fine if the project prefers it. Be consistent within an integration.

## Getting the transport generics right

`TransportInterface<RequestBody, DecodedResponse>`:

- `RequestBody` = the `BodyType` of the request models this handler passes in — `null` for a bodyless GET, `array` for a JSON POST.
- `DecodedResponse` = whatever the transport's decoder produces — `array` for `JsonPreset`, `string` for `RawPreset`, `BinaryFile` for `BinaryDownloadPreset`.

Mismatched generics are the one thing psalm reliably catches here, so annotate them.

## Where error handling goes

Push each concern to the layer that owns it. Duplicating error handling per handler is the most common design mistake in these integrations.

| Concern | Layer | How |
|---|---|---|
| 4xx/5xx must throw | **client plugin** | `Http\Client\Common\Plugin\ErrorPlugin` — already in `ClientBuilder::default()` |
| Retry on timeout | **client plugin** | `Http\Client\Common\Plugin\RetryPlugin` |
| Authentication, base URI, headers, logging | **client plugin** | see `configure-http-client` |
| API-wide error envelope (`200 {"isError":true}`), problem+json → exception | **transport decorator** | wrap the preset once per API |
| Payload validation | **response model** | `::parse()` — see `generate-http-response` |
| *This endpoint's* semantics: 404 means "no such order", not a crash | **request handler** | catch and translate, as below |

So the handler catches only what is specific to this endpoint:

```php
public function handle(FetchOrderRequest $request): Order
{
    try {
        return Order::parse(($this->transport)($request));
    } catch (ClientErrorException $e) {
        if (404 === $e->getResponse()->getStatusCode()) {
            throw OrderNotFound::withId($request->orderId, $e);
        }

        throw $e;
    }
}
```

`ClientErrorException` and `ServerErrorException` come from `Http\Client\Common\Exception` and are thrown by `ErrorPlugin`. Always pass the original exception as `$previous` — the response body is usually the only clue about what the API actually objected to.

If you find yourself writing the same `catch` in three handlers, it belongs in a transport decorator instead.

### Transport decorator for API-wide behaviour

A decorator is just another `TransportInterface`, so handlers stay unchanged:

```php
/**
 * @template RequestType
 *
 * @implements TransportInterface<RequestType, array>
 */
final readonly class BakeryErrorHandlingTransport implements TransportInterface
{
    /**
     * @param TransportInterface<RequestType, array> $transport
     */
    public function __construct(
        private TransportInterface $transport,
    ) {
    }

    public function __invoke(RequestInterface $request): array
    {
        $response = ($this->transport)($request);

        if (BakeryError::type()->matches($response)) {
            throw BakeryApiException::fromError(BakeryError::parse($response));
        }

        return $response;
    }
}
```

Compose it in the transport factory — see `configure-http-client` — so every handler inherits it for free.

## Responses with no body

A `204 No Content` endpoint has nothing to parse. Return `void` rather than inventing an empty model:

```php
interface CancelOrderRequestHandlerInterface
{
    public function handle(CancelOrderRequest $request): void;
}
```

```php
public function handle(CancelOrderRequest $request): void
{
    ($this->transport)($request);
}
```

Yes, the body looks pointless. It is still the right place for the endpoint's error translation, and consumers still get a mockable seam.

## Calling it from the application

Application code depends on the interface, never on the transport or the client:

```php
final readonly class NotifyCustomerWhenReady
{
    public function __construct(
        private FetchOrderRequestHandlerInterface $fetchOrder,
        private Mailer $mailer,
    ) {
    }

    public function __invoke(string $orderId): void
    {
        $order = $this->fetchOrder->handle(new FetchOrderRequest($orderId));

        if (OrderStatus::Ready === $order->status) {
            $this->mailer->send(new OrderReadyMail($order->customer, $order));
        }
    }
}
```

That test needs one stub and no HTTP at all.

## Common Mistakes

| Mistake | Fix |
|---|---|
| No interface, services depend on the concrete class | Ship the one-method interface; bind it in the container. |
| Handler returns `array` | Return a parsed response model, or `void`. |
| One `BakeryClient` class with ten public methods | One handler per endpoint. |
| `ClientInterface`/`ClientBuilder` injected into the handler | Inject `TransportInterface`. Client construction is the factory's job. |
| Same `catch (ClientErrorException)` in every handler | Move it to a transport decorator or a client plugin. |
| Catching `Throwable` and returning `null` | Translate to a named domain exception, or let it bubble. |
| Discarding the original exception when translating | Pass it as `$previous`; the response body lives there. |
| Bare `TransportInterface` type annotation | Annotate `TransportInterface<RequestBody, DecodedResponse>`. |
| Retry loop written inside the handler | `RetryPlugin` on the client. |
