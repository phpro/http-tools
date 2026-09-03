# Transports, encoders and decoders

A transport turns a request model into a PSR-7 request, sends it, and turns the response back into data. It is built from three independent choices:

1. a **URI builder** — how `uri()` + `uriParameters()` become a URI,
2. an **encoder** — what the request body looks like on the wire,
3. a **decoder** — what the handler receives back.

`TransportInterface<RequestType, ResponseType>` = `<EncoderInterface<RequestType>, DecoderInterface<ResponseType>>`. The two halves are chosen separately, which is the point: JSON out and a binary file back is a perfectly ordinary combination.

## URI builders

| Builder | Behaviour |
|---|---|
| `new TemplatedUriBuilder()` | Expands RFC 6570 templates: `/orders/{id}`, `/orders{?status,page}`. **Default choice.** |
| `new TemplatedUriBuilder(['version' => 'v2'])` | Same, with default variables shared by every request (`/{version}/orders`). |
| `RawUriBuilder::createWithAutodiscoveredPsrFactories()` | Takes `uri()` verbatim. For finished URIs — pagination links returned by the API, for instance. |

## Encoders

| Encoder | `EncoderInterface<…>` | Wire format |
|---|---|---|
| `JsonEncoder` | `array\|null` | `application/json`; `null` → empty body |
| `FormUrlencodedEncoder` | `array\|null` | `application/x-www-form-urlencoded` |
| `RawEncoder` | `string` | body as-is, no content type |
| `EmptyBodyEncoder` | `null` | no body |
| `StreamEncoder` | `StreamInterface` | PSR-7 stream as body |
| `ResourceStreamEncoder` | `ResourceStream<resource>` | `phpro/resource-stream`, for large payloads |
| `MultiPartEncoder` | `MultiPart` | `multipart/form-data` via `symfony/mime` |
| `ContentTypeAwareEncoder` | `ContentTypeAwarePayload<T>` | wraps another encoder and overrides `Content-Type` |

## Decoders

| Decoder | `DecoderInterface<…>` | Produces |
|---|---|---|
| `JsonDecoder` | `array` | decoded JSON; `[]` for an empty body |
| `FormUrlencodedDecoder` | `array` | parsed form body |
| `RawDecoder` | `string` | body as a string |
| `StreamDecoder` | `StreamInterface` | the PSR-7 stream, unread |
| `ResourceStreamDecoder` | `ResourceStream<resource>` | a resource stream |
| `ResponseDecoder` | `ResponseInterface` | the whole PSR-7 response |
| `BinaryFileDecoder` | `BinaryFile` | stream + size, mime type, filename, extension, hash |

All of them have `::createWithAutodiscoveredPsrFactories()`, which is what you want unless you are injecting specific PSR-17 factories.

Decoders that hand back a stream (`StreamDecoder`, `ResourceStreamDecoder`, `BinaryFileDecoder`) do **not** buffer the body. Don't read the stream twice, and close it when you're done — see `docs/files.md`.

## Composing a custom transport

When no preset matches:

```php
use Phpro\HttpTools\Encoding\Json\JsonEncoder;
use Phpro\HttpTools\Encoding\Binary\BinaryFileDecoder;
use Phpro\HttpTools\Transport\EncodedTransportFactory;
use Phpro\HttpTools\Uri\TemplatedUriBuilder;

// POST a JSON filter, get a generated PDF back.
$transport = EncodedTransportFactory::create(
    $client,
    new TemplatedUriBuilder(),
    JsonEncoder::createWithAutodiscoveredPsrFactories(),
    BinaryFileDecoder::createWithAutodiscoveredPsrFactories(),
);
// TransportInterface<array|null, BinaryFile>
```

If you compose the same combination twice, promote it to a small preset class of your own next to the transport factory.

## Per-request content types

`ContentTypeAwareEncoder` wraps another encoder so the request model decides the content type — for APIs that version through the media type:

```php
$encoder = new ContentTypeAwareEncoder(
    JsonEncoder::createWithAutodiscoveredPsrFactories(),
);
// RequestInterface<ContentTypeAwarePayload<array>>
// body(): new ContentTypeAwarePayload('application/vnd.bakery.v2+json', ['loaves' => 3])
```

## SerializerTransport

An alternative to encoder/decoder pairs: hand serialization to `symfony/serializer` (or any serializer behind `Phpro\HttpTools\Serializer\SerializerInterface`) and deserialize directly into typed objects.

```php
use Phpro\HttpTools\Serializer\SymfonySerializer;
use Phpro\HttpTools\Transport\Presets\RawPreset;
use Phpro\HttpTools\Transport\Serializer\SerializerTransport;

$transport = (new SerializerTransport(
    new SymfonySerializer($symfonySerializer, 'json'),
    RawPreset::create($client, new TemplatedUriBuilder()),
))->withOutputType(Order::class);
```

This replaces the response model's `::parse()` — the serializer builds the object. Worth it when the project already standardises on `symfony/serializer`; otherwise a `::type()` per model is more explicit about what the API is allowed to send. See `generate-http-response` under "Without psl/type".

## Async

Transports are synchronous by signature and fiber-transparent in practice. Give the client a fiber-based PSR-18 implementation and run handlers under `React\Async\parallel()` — no change to request models, response models, handlers or transports. See the async section of `README.md`.

## SDK tools

`Phpro\HttpTools\Sdk\HttpResource` plus the `Sdk\Rest\*Trait` classes compose a generic multi-endpoint client. It exists for cases where a handler per endpoint is genuinely overkill — a thin passthrough SDK, a spike. Request handlers remain the recommended approach, because they are what let consumers depend on one call instead of forty. See `docs/sdk.md`.
