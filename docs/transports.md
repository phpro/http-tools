# Transports

TODO : basic concept


Built-in transports:

- JsonTransportFactory::sync()
- JsonTransportFactory::async()


# Encoding

TODO : basic concept encoding / decoding:

Built-in transports:

- EmptyBodyEncode :: `EncoderInterface<null>` 
- JsonDecoder :: `DecoderInterface<array>`
- JsonEncoder :: `EncoderInterface<array|null>`
- StreamDecoder :: `DecoderInterface<StreamInterface>`
- StreamEncoder :: `EncoderInterface<StreamInterface>`
- RawDecoder :: `EncoderInterface<string>`
- RawEncoder :: `DecoderInterface<string>`

