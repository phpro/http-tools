<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\Json;

use Phpro\HttpTools\Encoding\DecoderInterface;
use Psl\Json;
use Psr\Http\Message\ResponseInterface;

/**
 * @implements DecoderInterface<array>
 */
final class JsonDecoder implements DecoderInterface
{
    public static function createWithAutodiscoveredPsrFactories(): self
    {
        return new self();
    }

    public function __invoke(ResponseInterface $response): array
    {
        if (!$responseBody = (string) $response->getBody()) {
            return [];
        }

        return (array) Json\decode($responseBody, true);
    }
}
