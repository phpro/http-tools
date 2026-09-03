---
name: generate-http-request
description: Use when writing or reviewing a phpro/http-tools request model — a class implementing Phpro\HttpTools\Request\RequestInterface — including choosing the URI template, mapping query/path parameters, shaping the request body, or deciding the BodyType generic. Triggers on "add a request model", "how do I pass query parameters", "POST body for this endpoint".
---

# Generate an HTTP request model

## Overview

A request model is an immutable value object describing **one** outgoing call. It answers four questions and nothing else — no HTTP client, no headers, no serialization:

```php
interface RequestInterface // @template BodyType
{
    public function method(): string;      // 'GET'|'POST'|'PUT'|'PATCH'|'DELETE'|'OPTIONS'|'HEAD'
    public function uri(): string;         // RFC 6570 URI template, path only
    public function uriParameters(): array;// variables for the template
    public function body();                // BodyType — what the encoder receives
}
```

The `BodyType` generic is what the transport's **encoder** consumes. Get it wrong and psalm complains at the handler.

## When to Use

- Adding a call to an external API endpoint.
- A call needs query parameters, path parameters, or a payload.

Part of the `generate-http-api-call` slice. The matching handler is `generate-http-request-handler`.

## BodyType per transport

| Transport preset | `RequestInterface<…>` | `body()` returns |
|---|---|---|
| `JsonPreset` | `array` or `null` | associative array, or `null` for no body |
| `FormUrlencodedPreset` | `array` or `null` | flat associative array |
| `RawPreset` / `PsrPreset` | `string` | raw string |
| `BinaryDownloadPreset::withEmptyRequest` | `null` | `null` |
| `BinaryDownloadPreset::withMultiPartRequest` | `MultiPart` | `FormDataPart` |
| Custom `EncodedTransportFactory` | whatever the encoder accepts | idem |

For a GET with no payload under `JsonPreset`, use `RequestInterface<null>` and `body(): null` — `JsonEncoder` writes an empty body for `null` instead of `"null"`.

## Complete example

`GET /orders{?status,page}` on the imaginary Crumbs Bakery API:

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Bakery\Order\ListOrders;

use App\Infrastructure\Bakery\Order\OrderStatus;
use Phpro\HttpTools\Request\RequestInterface;

/**
 * @psalm-immutable
 *
 * @template-implements RequestInterface<null>
 */
final readonly class ListOrdersRequest implements RequestInterface
{
    private function __construct(
        private ?OrderStatus $status,
        private int $page,
    ) {
    }

    public static function all(int $page = 1): self
    {
        return new self(null, $page);
    }

    public static function withStatus(OrderStatus $status, int $page = 1): self
    {
        return new self($status, $page);
    }

    public function method(): string
    {
        return 'GET';
    }

    public function uri(): string
    {
        return '/orders{?status,page}';
    }

    public function uriParameters(): array
    {
        return [
            'status' => $this->status?->value,
            'page' => $this->page,
        ];
    }

    public function body(): null
    {
        return null;
    }
}
```

Named constructors are the point: `ListOrdersRequest::withStatus(OrderStatus::Ready)` reads better at the call site than a nullable positional argument, and each variant documents a real use case. Keep the real constructor private when you have them.

### A request with a body

`POST /orders` — the body is a plain array because `JsonPreset` encodes arrays:

```php
/**
 * @psalm-immutable
 *
 * @template-implements RequestInterface<array>
 */
final readonly class PlaceOrderRequest implements RequestInterface
{
    public function __construct(
        private Customer $customer,
        private int $loaves,
    ) {
    }

    public function method(): string
    {
        return 'POST';
    }

    public function uri(): string
    {
        return '/orders';
    }

    public function uriParameters(): array
    {
        return [];
    }

    public function body(): array
    {
        return [
            'customer' => [
                'name' => $this->customer->name,
                'email' => $this->customer->email,
            ],
            'loaves' => $this->loaves,
        ];
    }
}
```

`body()` is where domain objects flatten into the wire format. Do the mapping here, explicitly, so the payload is readable in one place.

## URI templates

`TemplatedUriBuilder` expands [RFC 6570](https://www.rfc-editor.org/rfc/rfc6570) templates and handles the encoding.

| Need | Template | Parameters |
|---|---|---|
| Path segment | `/orders/{orderId}` | `['orderId' => 'abc-1']` |
| Query string | `/orders{?status,page}` | `['status' => 'ready', 'page' => 2]` |
| Optional query param | `/orders{?status}` | `['status' => null]` → omitted |
| Repeated query param | `/orders{?ids*}` | `['ids' => ['a', 'b']]` → `?ids=a&ids=b` |
| Reserved chars kept | `/files{+path}` | `['path' => 'a/b.txt']` |

A `null` parameter is dropped from the expansion, which is what you want for optional filters — no `?status=` noise.

Use `RawUriBuilder` instead only when the URI is already final and contains no template. Never build the query string by hand with `http_build_query` in `uri()`: you lose the null-dropping and re-encode already-encoded values.

Paths are relative. The base URI comes from the client (`ClientBuilder::addBaseUri()` — see `configure-http-client`), so `uri()` must not contain a host.

## Common Mistakes

| Mistake | Fix |
|---|---|
| Absolute URL in `uri()` | Relative path only; base URI belongs on the client. |
| `'/orders?status='.$status` | Use a template: `'/orders{?status}'`. |
| Constructor takes `array $data` | Take typed arguments, or add a named constructor from your command/DTO. |
| Headers set in the request model | Headers are a client concern (plugins) or an encoder concern (`ContentTypeAwareEncoder`). |
| `RequestInterface<array>` with `body(): null` | Match the generic to what `body()` actually returns. |
| Mutable class with setters | `final readonly` + named constructors; annotate `@psalm-immutable`. |
| Same class serving two endpoints via a flag | One request model per endpoint. |
