<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport\Presets;

use Http\Client\HttpAsyncClient;
use Phpro\HttpTools\Encoding\Raw\RawDecoder;
use Phpro\HttpTools\Encoding\Raw\RawEncoder;
use Phpro\HttpTools\Transport\AsyncEncodedTransport;
use Phpro\HttpTools\Transport\AsyncTransportInterface;
use Phpro\HttpTools\Transport\EncodedTransport;
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
    ) {
        return EncodedTransport::createWithAutodiscoveredPsrFactories(
            $client,
            $uriBuilder,
            RawEncoder::createWithAutodiscoveredPsrFactories(),
            RawDecoder::createWithAutodiscoveredPsrFactories()
        );
    }

    /**
     * @return AsyncTransportInterface<string, string>
     */
    public static function async(
        HttpAsyncClient $client,
        UriBuilderInterface $uriBuilder
    ) {
        return AsyncEncodedTransport::createWithAutodiscoveredPsrFactories(
            $client,
            $uriBuilder,
            RawEncoder::createWithAutodiscoveredPsrFactories(),
            RawDecoder::createWithAutodiscoveredPsrFactories()
        );
    }
}
