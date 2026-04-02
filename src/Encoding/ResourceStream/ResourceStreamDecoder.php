<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\ResourceStream;

use Phpro\HttpTools\Encoding\DecoderInterface;
use Phpro\ResourceStream\Factory\Psr7Stream;
use Phpro\ResourceStream\ResourceStream;
use Psr\Http\Message\ResponseInterface;

/**
 * @implements DecoderInterface<ResourceStream<resource>>
 */
final class ResourceStreamDecoder implements DecoderInterface
{
    public static function createWithAutodiscoveredPsrFactories(): self
    {
        return new self();
    }

    public function __invoke(ResponseInterface $response): ResourceStream
    {
        return Psr7Stream::createFromResponse($response);
    }
}
