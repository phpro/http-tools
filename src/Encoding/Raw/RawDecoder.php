<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\Raw;

use Phpro\HttpTools\Encoding\DecoderInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @implements DecoderInterface<string>
 */
final class RawDecoder implements DecoderInterface
{
    public static function createWithAutodiscoveredPsrFactories(): self
    {
        return new self();
    }

    public function __invoke(ResponseInterface $response): string
    {
        return (string) $response->getBody();
    }
}
