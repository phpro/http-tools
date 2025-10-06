<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport\Presets;

use Phpro\HttpTools\Encoding\DecoderInterface;
use Phpro\HttpTools\Encoding\FormUrlencoded\FormUrlencodedDecoder;
use Phpro\HttpTools\Encoding\FormUrlencoded\FormUrlencodedEncoder;
use Phpro\HttpTools\Transport\EncodedTransportFactory;
use Phpro\HttpTools\Transport\TransportInterface;
use Phpro\HttpTools\Uri\UriBuilderInterface;
use Psr\Http\Client\ClientInterface;

final class FormUrlencodedPreset
{
    /**
     * @param DecoderInterface<array>|null $decoder
     *
     * @return TransportInterface<array|null, array>
     */
    public static function create(
        ClientInterface $client,
        UriBuilderInterface $uriBuilder,
        ?DecoderInterface $decoder = null,
    ): TransportInterface {
        return EncodedTransportFactory::create(
            $client,
            $uriBuilder,
            FormUrlencodedEncoder::createWithAutodiscoveredPsrFactories(),
            $decoder ?? FormUrlencodedDecoder::createWithAutodiscoveredPsrFactories()
        );
    }
}
