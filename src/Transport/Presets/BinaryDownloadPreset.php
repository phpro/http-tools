<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport\Presets;

use Phpro\HttpTools\Encoding\Binary\BinaryFile;
use Phpro\HttpTools\Encoding\Binary\BinaryFileDecoder;
use Phpro\HttpTools\Encoding\Mime\MultiPartEncoder;
use Phpro\HttpTools\Encoding\Raw\EmptyBodyEncoder;
use Phpro\HttpTools\Transport\EncodedTransportFactory;
use Phpro\HttpTools\Transport\TransportInterface;
use Phpro\HttpTools\Uri\UriBuilderInterface;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Mime\Part\AbstractMultipartPart as MultiPart;

final class BinaryDownloadPreset
{
    /**
     * @deprecated Will be removed in v3. Use withEmptyRequest instead.
     *
     * @return TransportInterface<null, BinaryFile>
     */
    public static function create(
        ClientInterface $client,
        UriBuilderInterface $uriBuilder
    ): TransportInterface {
        return self::withEmptyRequest($client, $uriBuilder);
    }

    /**
     * @return TransportInterface<null, BinaryFile>
     */
    public static function withEmptyRequest(
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

    /**
     * @return TransportInterface<MultiPart, BinaryFile>
     */
    public static function withMultiPartRequest(
        ClientInterface $client,
        UriBuilderInterface $uriBuilder
    ): TransportInterface {
        return EncodedTransportFactory::create(
            $client,
            $uriBuilder,
            MultiPartEncoder::createWithAutodiscoveredPsrFactories(),
            BinaryFileDecoder::createWithAutodiscoveredPsrFactories()
        );
    }
}
