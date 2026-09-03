---
name: generate-http-response
description: Use when turning a decoded API payload into a typed value object for a phpro/http-tools integration — writing a response model, parsing JSON into objects, handling optional/nullable/unknown fields, mapping status strings to enums, or validating that an API returned what it promised. Triggers on "parse this response", "response model", "map the JSON to objects", "handle a missing field".
---

# Generate an HTTP response model

## Overview

**Never trust the API.** A response model converts a decoded payload (usually `array`) into a value object with typed properties, and fails loudly when the payload doesn't match.

Two rules, in order of importance:

1. **Non-negotiable:** the model is strictly typed and the payload is validated **once**, at construction. No raw array survives inside the object; no accessor re-guesses a missing key.
2. **Recommended:** do that validation with [`psl/type`](https://github.com/php-standard-library/php-standard-library) (`php-standard-library/type`), which this library itself uses. It is a recommendation, not a requirement — see [Without psl/type](#without-psltype) for equally valid alternatives. Follow whatever the surrounding project already does.

## When to Use

- The handler needs to return something other than a raw array (i.e. always).
- Modelling a nested object, a list, an enum, or an optional field from an API payload.

Part of the `generate-http-api-call` slice.

## The Iron Rule: parse once, then it's an object

**No `fromArray()` that stores the array. No getters with `$this->data['x'] ?? null`.**

That pattern defers every failure to the call site and hides which fields the integration actually depends on. A single parse step states the contract in one place, and the constructor receives values that are already the right type.

```php
// ✗ WRONG — the array survives, every accessor re-guesses, nothing is typed
final class Order
{
    private function __construct(private array $data) {}

    public static function fromArray(array $data): self { return new self($data); }

    public function status(): ?string { return $this->data['status'] ?? null; }
}

// ✓ RIGHT — validated at the boundary, typed from then on
final readonly class Order
{
    public function __construct(
        public string $id,
        public OrderStatus $status,
    ) {}

    public static function parse(mixed $data): self { /* validate, then construct */ }
}
```

## Recommended: psl/type

With `psl/type`, every response model exposes exactly two static methods:

- `::type(): TypeInterface<self>` — declares the payload shape and how to build the object from it.
- `::parse(mixed $data): self` — `self::type()->coerce($data)`.

Because `::type()` returns a composable type, nested models compose: a parent shape references `Customer::type()` directly, and you never hand-roll recursion.

```bash
composer require php-standard-library/type
```

### Complete example

`{"id":"…","status":"baking","loaves":3,"customer":{"name":"…","email":"…"},"notes":null}` from the imaginary Crumbs Bakery API.

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Bakery\Order;

use Psl\Type\TypeInterface;

use function Psl\Type\backed_enum;
use function Psl\Type\converted;
use function Psl\Type\instance_of;
use function Psl\Type\int;
use function Psl\Type\non_empty_string;
use function Psl\Type\nullable;
use function Psl\Type\optional;
use function Psl\Type\shape;
use function Psl\Type\string;

final readonly class Order
{
    public function __construct(
        public string $id,
        public OrderStatus $status,
        public int $loaves,
        public Customer $customer,
        public ?string $notes,
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
                'notes' => optional(nullable(string())),
            ]),
            instance_of(self::class),
            static fn (array $data): self => new self(
                $data['id'],
                $data['status'],
                $data['loaves'],
                $data['customer'],
                $data['notes'] ?? null,
            ),
        );
    }
}
```

`shape()`'s second argument, `allowUnknownFields`, does **not** control whether an unexpected field breaks parsing — under `coerce()` it never does. It only controls whether unknown keys survive into the coerced array (as `mixed`) or are dropped. Since the converter closure reads only the keys it names, the flag makes no observable difference in this pattern, so leave it at its default. It starts to matter only if you `assert()` instead of `coerce()`, or use `matches()` to pick between payload shapes — see [references/psl-types.md](references/psl-types.md).

The nested model is a plain sibling with the same two methods:

```php
final readonly class Customer
{
    public function __construct(
        public string $name,
        public string $email,
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
                'name' => non_empty_string(),
                'email' => non_empty_string(),
            ]),
            instance_of(self::class),
            static fn (array $data): self => new self($data['name'], $data['email']),
        );
    }
}
```

And the enum carries the wire values:

```php
enum OrderStatus: string
{
    case Baking = 'baking';
    case Ready = 'ready';
    case Collected = 'collected';
}
```

### Lists

A collection response wraps a `vec()` of the item type:

```php
final readonly class OrderList
{
    /**
     * @param list<Order> $orders
     */
    public function __construct(
        public array $orders,
        public int $total,
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
                'items' => vec(Order::type()),
                'total' => int(),
            ]),
            instance_of(self::class),
            static fn (array $data): self => new self($data['items'], $data['total']),
        );
    }
}
```

Note the constructor property is `orders` while the wire key is `items` — the converter closure is exactly where you rename wire vocabulary into your own.

### psl/type quick reference

Full catalogue and `coerce` vs `assert` semantics: [references/psl-types.md](references/psl-types.md).

| Payload | Type |
|---|---|
| `"abc"` | `string()`, `non_empty_string()` |
| `3` / `"3"` | `int()` (coerces numeric strings), `positive_int()` |
| `1.5` | `float()` |
| `true` / `"1"` | `bool()` |
| `"baking"` → enum | `backed_enum(OrderStatus::class)` |
| `null` allowed | `nullable(string())` |
| key may be absent | `optional(string())` |
| absent **or** null | `optional(nullable(string()))` |
| `["a","b"]` | `vec(string())` |
| `{"a":1,"b":2}` | `dict(string(), int())` |
| `{"id":…}` | `shape([...])` |
| either shape | `union(A::type(), B::type())` |
| `"2029-10-08"` → object | `converted(string(), instance_of(Date::class), $fn)` |

## Without psl/type

`psl/type` is a suggestion in this library's `composer.json`, not a dependency of your integration. Any approach that satisfies the Iron Rule is fine. Pick the one the project already uses:

| Approach | How it fits | Notes |
|---|---|---|
| **`symfony/serializer`** | Use `SerializerTransport` + `SymfonySerializer` instead of a preset, and `->withOutputType(Order::class)` in the handler. See `docs/transports.md`. | Denormalizes straight into typed constructors; no `::parse()` needed. |
| **`cuyz/valinor`** | Keep the preset; `::parse()` becomes `$mapper->map(self::class, Source::array($data))`. | Same two-method shape, different engine. Strong on unions and enums. |
| **`webmozart/assert`** | Already a dependency of this library. Assert in a private constructor or named constructor, then assign. | Verbose for nested payloads; fine for flat ones. |
| **Plain PHP** | Named constructor with explicit `match`/`instanceof` checks that throw on anything unexpected. | Acceptable when the payload is two or three scalars. Gets unmaintainable fast. |

Whatever you choose, keep the public surface identical — `::parse(mixed $data): self` plus typed readonly properties — so handlers and tests don't care which validator is underneath.

## Empty and error responses

- `JsonDecoder` returns `[]` for an empty body, so a `204 No Content` reaches you as `[]`. Model that as a request handler returning `void`, not as a response model with all-optional fields.
- HTTP error statuses should never reach `::parse()` — add `Http\Client\Common\Plugin\ErrorPlugin` to the client (`ClientBuilder::default()` already includes it) so 4xx/5xx throw. See `configure-http-client`.
- APIs that signal failure with `200 {"isError": true}` need a transport decorator that inspects the payload and throws, not an `isError` property on the response model. See `configure-http-client`.

## Common Mistakes

| Mistake | Fix |
|---|---|
| `fromArray()` storing the raw array | Validate at the boundary; typed constructor properties. |
| Getters with `?? null` fallbacks | Declare optionality once, in the shape/mapping. |
| Status/type as `string` | Backed enum + `backed_enum()` (or your validator's enum support). |
| Nested payload inlined as `dict(string(), mixed())` | Give it its own model with its own `::type()`. |
| `->assert()` on a JSON payload | Use `->coerce()` — JSON gives you `"3"` where you want `3`. |
| Response model with `toArray()` | Outgoing mapping belongs in the request model's `body()`. |
| Swallowing the validation exception and returning `null` | Let it bubble. A payload you can't parse is a real failure. |
| Adding `psl/type` to a project that standardised on another mapper | Follow the project; the Iron Rule is what matters, the tool isn't. |
