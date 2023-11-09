<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport\Presets;

use Phpro\HttpTools\Encoding\Binary\BinaryFile;
use Phpro\HttpTools\Encoding\Binary\BinaryFileDecoder;
use Phpro\HttpTools\Encoding\Raw\EmptyBodyEncoder;
use Phpro\HttpTools\Transport\EncodedTransportFactory;
use Phpro\HttpTools\Transport\TransportInterface;
use Phpro\HttpTools\Uri\UriBuilderInterface;
use Psr\Http\Client\ClientInterface;

final class BinaryDownloadPreset
{
    /**
     * @return TransportInterface<null, BinaryFile>
     */
    public static function create(
        ClientInterface $client,
        UriBuilderInterface $uriBuilder
    ): TransportInterface {
        return EncodedTransportFactory::create(
            $client,
            $uriBuilder,
            EmptyBodyEncoder::createWithAutodiscoveredPsrFactories(),
            BinaryFileDecoder::createWithAutodiscoveredPsrFactories()
        );
    }
}
