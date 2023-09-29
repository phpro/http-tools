<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport;

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
    public static function create(
        ClientInterface $client,
        UriBuilderInterface $uriBuilder,
        EncoderInterface $encoder,
        DecoderInterface $decoder
    ): TransportInterface {
        /** @var CallbackTransport<RequestType, ResponseType> $transport */
        $transport = new CallbackTransport(
            EncodingRequestConverter::createWithAutodiscoveredPsrFactories($uriBuilder, $encoder),
            static fn (PsrRequestInterface $request) => $client->sendRequest($request),
            static fn (ResponseInterface $response) => $decoder($response)
        );

        return $transport;
    }
}
