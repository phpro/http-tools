<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport\Presets;

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
     * @return TransportInterface<string, ResponseInterface>
     */
    public static function create(
        ClientInterface $client,
        UriBuilderInterface $uriBuilder
    ): TransportInterface {
        return EncodedTransportFactory::create(
            $client,
            $uriBuilder,
            RawEncoder::createWithAutodiscoveredPsrFactories(),
            ResponseDecoder::createWithAutodiscoveredPsrFactories()
        );
    }
}
