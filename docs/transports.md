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

| Class                   | EncodingType<DataType>                | Action                                                                              |
|-------------------------|---------------------------------------|-------------------------------------------------------------------------------------|
| `BinaryFileDecoder`     | `DecoderInterface<BinaryFile>`        | Parses file information from the HTTP response and returns a `BinaryFile` DTO       |
| `ContentTypeAwareEncoder` | `EncoderInterface<ContentTypeAwarePayload<T>>` | Decorator that wraps any encoder and sets the Content-Type header from the payload |
| `EmptyBodyEncoder`      | `EncoderInterface<null>`              | Creates empty request body                                                          |
| `FormUrlencodedDecoder` | `DecoderInterface<array>`             | Converts form urlencoded response body to array                                     |
| `FormUrlencodedEncoder` | `EncoderInterface<?array>`            | Adds form urlencoded body and headers to request                                    |
| `JsonDecoder`           | `DecoderInterface<array>`             | Converts json response body to array                                                |
| `JsonEncoder`           | `EncoderInterface<?array>`            | Adds json body and headers to request                                               |
| `MultiPartEncoder`      | `EncoderInterface<AbstractMultipartPart>`   | Adds symfony/mime `AbstractMultipartPart`as HTTP body. Handy for form data + files. |
| `RawDecoder`            | `DecoderInterface<string>`            | Returns the raw PSR-7 body string as response result                                |
| `RawEncoder`            | `EncoderInterface<string>`            | Adds raw string as request body                                                     |
| `ResourceStreamDecoder` | `DecoderInterface<ResourceStream>`    | Returns `phpro/resource-stream` from response body                                  |
| `ResourceStreamEncoder` | `EncoderInterface<ResourceStream>`    | Adds `phpro/resource-stream` as request body                                        |
| `ResponseDecoder`       | `DecoderInterface<ResponseInterface>` | Returns the received PSR-7 response as result                                       |
| `StreamDecoder`         | `DecoderInterface<StreamInterface>`   | Returns the PSR-7 Stream as response result                                         |
| `StreamEncoder`         | `EncoderInterface<StreamInterface>`   | Adds PSR-7 Stream as request body                                                   |

> **Note:** The `ResourceStreamEncoder` passes the underlying PHP resource to a PSR-7 stream.
> PSR-7's `StreamInterface::close()` will close this resource, which also closes the original `ResourceStream`.
> If you need to reuse the resource after sending a request, pass a copy of the stream (e.g. via `$stream->copyTo(MemoryStream::create())`) to the encoder instead.

## Built-in transport presets:

We've composed some of the encodings above into pre-configured transports:


| Preset                 | RequestType             | ResponseType        | Factory method         |
|------------------------|-------------------------|---------------------|------------------------|
| `BinaryDownloadPreset` | `null`                  | `BinaryFile`        | `withEmptyRequest`     |
| `BinaryDownloadPreset` | `AbstractMultipartPart` | `BinaryFile`        | `withMultiPartRequest` |
| `FormUrlencodedPreset` | `?array`                | `array`             | `create`               |
| `JsonPreset`           | `?array`                | `array`             | `create`               |
| `PsrPreset`            | `string`                | `ResponseInterface` | `create`               |
| `RawPreset`            | `string`                | `string`            | `create`               |

## Creating your own configuration

We provide an `EncodedTransport` class that helps you build your own configuration.
This transport takes a configurable encoder and decoder:


```php
use Phpro\HttpTools\Transport\EncodedTransportFactory;

EncodedTransportFactory::create(
    $client,
    $uriBuilder,
    $encoder,
    $decoder
);
```

## Other transports

### Files

We provide encoders and decoders for uploading and downloading files.

[You can learn more about this on this dedicated page.](./files.md)

### SerializerTransport

This transport allows you to use an external serializer to handle request serialization and response deserialization.
You can use the symfony/serializer component or any other serializer you please.

However, you do need to specify what output type the transport will deserialize to. (e.g. inside a request handler)

```php
use Phpro\HttpTools\Serializer\SymfonySerializer;
use Phpro\HttpTools\Transport\Presets\RawPreset;
use Phpro\HttpTools\Transport\Serializer\SerializerTransport;

$transport = new SerializerTransport(
    new SymfonySerializer($theSymfonySerializer, 'json'),
    RawPreset::create($client, $uriBuilder)
);

$transport->withOutputType(SomeResponse::class);
```

If you want to use symfony/validator, you might need to:

```bash
composer require symfony/serializer
```
