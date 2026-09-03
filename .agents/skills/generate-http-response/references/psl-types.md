# psl/type reference for response models

Package: `php-standard-library/type` (namespace `Psl\Type`). Import the constructors as functions:
`use function Psl\Type\shape;`.

## coerce vs assert vs matches

| Method | Behaviour | Throws | Use for |
|---|---|---|---|
| `coerce($value)` | Converts compatible values (`"3"` → `3`, `1` → `true`) | `Psl\Type\Exception\CoercionException` | **Decoded API payloads.** JSON and form encoding blur scalar types. |
| `assert($value)` | Requires an exact type match | `Psl\Type\Exception\AssertException` | Values you produced yourself and want to prove. |
| `matches($value)` | `bool`, no exception | — | Branching on which of two payload shapes you got. |

Both exceptions implement `Psl\Type\Exception\ExceptionInterface` and extend `Psl\Type\Exception\Exception`. Their message names the failing path (e.g. `customer.email`), which makes them genuinely useful in logs — don't wrap them in a generic "invalid response" exception unless you keep the previous exception.

Default to `coerce()` in `::parse()`.

## Scalars

| Function | Accepts |
|---|---|
| `string()` | strings, and anything `Stringable`/numeric under coercion |
| `non_empty_string()` | as above, rejects `''` |
| `numeric_string()` | `"42"`, `"1.5"` |
| `int()` | ints, numeric strings, `"3"` |
| `positive_int()` | `>= 1` |
| `uint()` | `>= 0` |
| `int_range($min, $max)` | bounded int |
| `float()` / `f64()` | floats, numeric strings |
| `bool()` | `true`/`false`, `"1"`/`"0"`, `1`/`0` |
| `null()` | only `null` |
| `mixed()` | anything — a last resort, it defeats the purpose |
| `uuid()` | UUID-shaped string |

## Structures

| Function | Payload |
|---|---|
| `shape(array $elements, bool $allowUnknownFields = false)` | object/associative array with known keys |
| `vec(TypeInterface $t)` | JSON array → `list<T>` |
| `non_empty_vec($t)` | as above, rejects `[]` |
| `dict($keyType, $valueType)` | JSON object with arbitrary keys → `array<K, V>` |
| `non_empty_dict($k, $v)` | as above, rejects `{}` |
| `set($t)` | unique values |

#### `allowUnknownFields`, precisely

An unexpected field in the payload is **not** an error under `coerce()`, whatever the flag says. What the flag changes:

| | `allowUnknownFields: false` (default) | `true` |
|---|---|---|
| `coerce()` | unknown keys are **dropped** from the result | unknown keys are **kept**, typed `mixed` |
| `assert()` | unknown key throws `AssertException` | kept |
| `matches()` | unknown key → `false` | unknown keys ignored |

So for the `converted(shape(...), instance_of(...), $fn)` + `coerce()` pattern that response models use, the flag has **no observable effect** — the converter reads only the keys it names, and the dropped-or-kept extras never reach your constructor. Leave it at the default.

Reach for `allowUnknownFields: true` only when you genuinely consume the passthrough keys (a `dict`-ish payload with a few known keys plus arbitrary extras you want to keep), or when a `matches()` check must tolerate extra fields. Conversely, keep it `false` when `matches()` is your discriminator between two payload shapes and an extra key should mean "not this shape".

## Optionality

Three distinct cases — pick deliberately:

```php
'notes' => string(),                      // key MUST exist and be a string
'notes' => nullable(string()),            // key MUST exist, may be null
'notes' => optional(string()),            // key may be absent; if present, a string
'notes' => optional(nullable(string())),  // key may be absent OR null
```

`optional()` is only meaningful directly inside `shape()`. When a field is optional, read it in the converter with `$data['notes'] ?? null`.

## Enums

```php
backed_enum(OrderStatus::class)        // "baking" -> OrderStatus::Baking
backed_enum_value(OrderStatus::class)  // "baking" -> "baking", validated against the enum
unit_enum(SomeEnum::class)             // non-backed enums
enum_case_of(OrderStatus::class, 'Baking')
```

An unknown status now fails at the boundary with a clear message, instead of silently flowing through as a string.

## Composition

| Function | Use |
|---|---|
| `union($a, $b, ...)` | payload is one of several shapes — order matters, first match wins |
| `intersection($a, $b)` | value must satisfy both |
| `converted($from, $into, Closure $converter)` | validate as `$from`, then map into `$into` |
| `instance_of(SomeClass::class)` | the object type, used as `converted()`'s `$into` |
| `class_string(SomeClass::class)` | a class-string |

`converted()` is the workhorse for value objects:

```php
converted(
    shape([...]),               // what the wire looks like
    instance_of(self::class),   // what you want
    static fn (array $d): self => new self(...),
);
```

It also handles scalar-to-object mapping:

```php
// "2029-10-08" -> DateTimeImmutable
converted(
    non_empty_string(),
    instance_of(DateTimeImmutable::class),
    static fn (string $date): DateTimeImmutable => new DateTimeImmutable($date),
);
```

Extract that into a reusable `BakeryDate::type()` when more than one model needs it.

## Handling either-of payloads

Some APIs return a success shape or an error shape under `200`. `union()` plus `matches()` keeps it explicit:

```php
public static function parse(mixed $data): Order
{
    if (BakeryError::type()->matches($data)) {
        throw BakeryApiException::fromError(BakeryError::parse($data));
    }

    return Order::parse($data);
}
```

Better still, move that decision into a transport decorator so every endpoint inherits it — see `configure-http-client`.

## Testing types

`::type()` is a pure function, so parsing tests need no HTTP at all:

```php
#[Test]
public function it_parses_an_order(): void
{
    $order = Order::parse([
        'id' => 'ord-1',
        'status' => 'baking',
        'loaves' => 3,
        'customer' => ['name' => 'Jo', 'email' => 'jo@example.com'],
    ]);

    self::assertSame(OrderStatus::Baking, $order->status);
    self::assertNull($order->notes);
}

#[Test]
public function it_rejects_an_unknown_status(): void
{
    $this->expectException(CoercionException::class);

    Order::parse([
        'id' => 'ord-1',
        'status' => 'exploded',
        'loaves' => 3,
        'customer' => ['name' => 'Jo', 'email' => 'jo@example.com'],
    ]);
}
```

See `test-http-integration` for the rest of the test layers.
