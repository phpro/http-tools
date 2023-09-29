<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport\Presets;

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
    public static function create(
        ClientInterface $client,
        UriBuilderInterface $uriBuilder
    ): TransportInterface {
        return EncodedTransportFactory::create(
            $client,
            $uriBuilder,
            JsonEncoder::createWithAutodiscoveredPsrFactories(),
            JsonDecoder::createWithAutodiscoveredPsrFactories()
        );
    }
}
