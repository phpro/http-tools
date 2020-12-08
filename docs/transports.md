# Transports

Transports are responsible for transforming a Request model into a PSR-7 HTTP request and asks a response through the actual HTTP client.
Since the data inside the request and response value objects is set-up in a flexible way, we also provide a lot of ways of transforming the value objects into PSR-7 HTTP Requests and Responses.

This is done by providing an encoding layer.

## Encoding

Encoding is split up into `Encoder` and `Decoder` classes.
The Encoder classes are responsible for converting the body of a Request value object into the body payload of a PSR-7 Http request.
The Decoder classes are responsible for converting the payload of a PSR-7 Response object into a result that can be used by the transport or request handler.

By splitting the Encoding into 2 components, you can compose any encoding component in a transport. 
Examples:


| Transport<RequestType, ResponseType> | Encoder<DataType> | Decoder<DataType> |
| --- | --- | --- |
| `TransportInterface<?array, array>` | `JsonEncoder<?array>` | `JsonDecoder<array>` |
| `TransportInterface<string, string>` | `RawEncoder<string>` | `RawStringEncoder<string>` |
| `TransportInterface<array, string>` | `JsonEncoder<?array>` | `RawStringEncoder<string>` |
| `TransportInterface<null, string>` | `EmptyBodyEncoder<null>` | `RawStringEncoder<string>` |


### Built-in encodings

This package contains some frequently used encoders / decoders for you:

| Class | EncodingType<DataType> | Action |
| --- | --- | --- |
| `EmptyBodyEncoder` | `EncoderInterface<null>` | Creates epmty request body | 
| `JsonEncoder` | `EncoderInterface<?array>` | Adds json body and headers to request |
| `JsonDecoder` | `DecoderInterface<array>` | Converts json response body to array |
| `StreamEncoder` | `EncoderInterface<StreamInterface>` | Adds PSR-7 Stream as request body |
| `StreamDecoder` | `DecoderInterface<StreamInterface>` | Returns the PSR-7 Stream as response result |
| `RawEncoder` | `EncoderInterface<string>` | Adds raw string as request body |
| `RawDecoder` | `DecoderInterface<string>` | Returns the raw PSR-7 body string as response result |

## Built-in transport configurations:

We've composed some of the encodings above into pre-configured transports:


| Factory | RequestType | ResponseType |
| --- | --- | --- |
| `JsonTransportFactory::sync()` | `?array` | `array` |
| `JsonTransportFactory::async()` | `?array` | `Promise<array>`> |


## Creating your own configuration

We provide a `EncodedTransport` and an `AsyncEncodedTransport`.
This transport takes a configurable encoder and decoder. 

### Sync

```php
use Phpro\HttpTools\Transport\EncodedTransport;

EncodedTransport::createWithAutodiscoveredPsrFactories(
    $client,
    $uriBuilder,
    $encoder,
    $decoder
);
```

### Async

```php
use Phpro\HttpTools\Transport\AsyncEncodedTransport;

AsyncEncodedTransport::createWithAutodiscoveredPsrFactories(
    $client,
    $uriBuilder,
    $encoder,
    $decoder
);
```

