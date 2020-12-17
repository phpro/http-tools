<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\Psr7;

use Phpro\HttpTools\Encoding\DecoderInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @implements DecoderInterface<ResponseInterface>
 */
final class ResponseDecoder implements DecoderInterface
{
    public static function createWithAutodiscoveredPsrFactories(): self
    {
        return new self();
    }

    public function __invoke(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }
}
