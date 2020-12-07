<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\Stream;

use Phpro\HttpTools\Encoding\DecoderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @implements DecoderInterface<StreamInterface>
 */
final class StreamDecoder implements DecoderInterface
{
    public static function createWithAutodiscoveredPsrFactories(): self
    {
        return new self();
    }

    public function __invoke(ResponseInterface $response): StreamInterface
    {
        return $response->getBody();
    }
}
