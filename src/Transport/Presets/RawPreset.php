<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport\Presets;

use Amp\Promise;
use Http\Client\HttpAsyncClient;
use Phpro\HttpTools\Encoding\Raw\RawDecoder;
use Phpro\HttpTools\Encoding\Raw\RawEncoder;
use Phpro\HttpTools\Transport\EncodedTransportFactory;
use Phpro\HttpTools\Transport\TransportInterface;
use Phpro\HttpTools\Uri\UriBuilderInterface;
use Psr\Http\Client\ClientInterface;

final class RawPreset
{
    /**
     * @return TransportInterface<string, string>
     */
    public static function sync(
        ClientInterface $client,
        UriBuilderInterface $uriBuilder
    ): TransportInterface {
        return EncodedTransportFactory::sync(
            $client,
            $uriBuilder,
            RawEncoder::createWithAutodiscoveredPsrFactories(),
            RawDecoder::createWithAutodiscoveredPsrFactories()
        );
    }

    /**
     * @return TransportInterface<string, Promise<string>>
     */
    public static function async(
        HttpAsyncClient $client,
        UriBuilderInterface $uriBuilder
    ): TransportInterface {
        return EncodedTransportFactory::async(
            $client,
            $uriBuilder,
            RawEncoder::createWithAutodiscoveredPsrFactories(),
            RawDecoder::createWithAutodiscoveredPsrFactories()
        );
    }
}
