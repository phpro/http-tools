<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport\Presets;

use Amp\Promise;
use Http\Client\HttpAsyncClient;
use Phpro\HttpTools\Encoding\Psr7\ResponseDecoder;
use Phpro\HttpTools\Encoding\Raw\RawEncoder;
use Phpro\HttpTools\Transport\EncodedTransportFactory;
use Phpro\HttpTools\Transport\TransportInterface;
use Phpro\HttpTools\Uri\UriBuilderInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

final class PsrPreset
{
    /**
     * @return TransportInterface<string|null, ResponseInterface>
     */
    public static function sync(
        ClientInterface $client,
        UriBuilderInterface $uriBuilder
    ): TransportInterface {
        return EncodedTransportFactory::sync(
            $client,
            $uriBuilder,
            RawEncoder::createWithAutodiscoveredPsrFactories(),
            ResponseDecoder::createWithAutodiscoveredPsrFactories()
        );
    }

    /**
     * @return TransportInterface<string|null, Promise<ResponseInterface>>
     */
    public static function async(
        HttpAsyncClient $client,
        UriBuilderInterface $uriBuilder
    ): TransportInterface {
        return EncodedTransportFactory::async(
            $client,
            $uriBuilder,
            RawEncoder::createWithAutodiscoveredPsrFactories(),
            ResponseDecoder::createWithAutodiscoveredPsrFactories()
        );
    }
}
