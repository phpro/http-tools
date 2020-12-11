<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport\Presets;

use Http\Client\HttpAsyncClient;
use Phpro\HttpTools\Encoding\Json\JsonDecoder;
use Phpro\HttpTools\Encoding\Json\JsonEncoder;
use Phpro\HttpTools\Transport\AsyncEncodedTransport;
use Phpro\HttpTools\Transport\AsyncTransportInterface;
use Phpro\HttpTools\Transport\EncodedTransport;
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
    ) {
        return EncodedTransport::createWithAutodiscoveredPsrFactories(
            $client,
            $uriBuilder,
            JsonEncoder::createWithAutodiscoveredPsrFactories(),
            JsonDecoder::createWithAutodiscoveredPsrFactories()
        );
    }

    /**
     * @return AsyncTransportInterface<array|null, array>
     */
    public static function async(
        HttpAsyncClient $client,
        UriBuilderInterface $uriBuilder
    ) {
        return AsyncEncodedTransport::createWithAutodiscoveredPsrFactories(
            $client,
            $uriBuilder,
            JsonEncoder::createWithAutodiscoveredPsrFactories(),
            JsonDecoder::createWithAutodiscoveredPsrFactories()
        );
    }
}
