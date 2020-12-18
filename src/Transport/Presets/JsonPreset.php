<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport\Presets;

use Amp\Promise;
use Http\Client\HttpAsyncClient;
use Phpro\HttpTools\Encoding\Json\JsonDecoder;
use Phpro\HttpTools\Encoding\Json\JsonEncoder;
use Phpro\HttpTools\Transport\EncodedTransportFactory;
use Phpro\HttpTools\Transport\TransportInterface;
use Phpro\HttpTools\Uri\UriBuilderInterface;
use Psr\Http\Client\ClientInterface;

final class JsonPreset
{
    /**
     * @return TransportInterface<array|null, array>
     */
    public static function sync(
        ClientInterface $client,
        UriBuilderInterface $uriBuilder
    ): TransportInterface {
        return EncodedTransportFactory::sync(
            $client,
            $uriBuilder,
            JsonEncoder::createWithAutodiscoveredPsrFactories(),
            JsonDecoder::createWithAutodiscoveredPsrFactories()
        );
    }

    /**
     * @return TransportInterface<array|null, Promise<array>>
     */
    public static function async(
        HttpAsyncClient $client,
        UriBuilderInterface $uriBuilder
    ): TransportInterface {
        return EncodedTransportFactory::async(
            $client,
            $uriBuilder,
            JsonEncoder::createWithAutodiscoveredPsrFactories(),
            JsonDecoder::createWithAutodiscoveredPsrFactories()
        );
    }
}
