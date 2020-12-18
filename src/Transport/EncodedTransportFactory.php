<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport;

use Amp\Promise;
use Http\Client\HttpAsyncClient;
use Phpro\HttpTools\Encoding\DecoderInterface;
use Phpro\HttpTools\Encoding\EncoderInterface;
use Phpro\HttpTools\Transport\IO\Input\EncodingRequestConverter;
use Phpro\HttpTools\Uri\UriBuilderInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\ResponseInterface;

final class EncodedTransportFactory
{
    /**
     * @template RequestType
     * @template ResponseType
     *
     * @param EncoderInterface<RequestType> $encoder
     * @param DecoderInterface<ResponseType> $decoder
     *
     * @return TransportInterface<RequestType, ResponseType>
     */
    public static function sync(
        ClientInterface $client,
        UriBuilderInterface $uriBuilder,
        EncoderInterface $encoder,
        DecoderInterface $decoder
    ): TransportInterface {
        /** @var CallbackTransport<RequestType, ResponseType, false> $transport */
        $transport = new CallbackTransport(
            EncodingRequestConverter::createWithAutodiscoveredPsrFactories($uriBuilder, $encoder),
            static fn (PsrRequestInterface $request) => $client->sendRequest($request),
            static fn (ResponseInterface $response) => $decoder($response)
        );

        return $transport;
    }

    /**
     * @template RequestType
     * @template ResponseType
     *
     * @param EncoderInterface<RequestType> $encoder
     * @param DecoderInterface<ResponseType> $decoder
     *
     * @return TransportInterface<RequestType, Promise<ResponseType>>
     */
    public static function async(
        HttpAsyncClient $client,
        UriBuilderInterface $uriBuilder,
        EncoderInterface $encoder,
        DecoderInterface $decoder
    ): TransportInterface {
        /** @var CallbackTransport<RequestType, ResponseType, true> $transport */
        $callback = new CallbackTransport(
            EncodingRequestConverter::createWithAutodiscoveredPsrFactories($uriBuilder, $encoder),
            static fn (PsrRequestInterface $request) => $client->sendAsyncRequest($request),
            static fn (ResponseInterface $response) => $decoder($response)
        );

        return $callback;
    }
}
