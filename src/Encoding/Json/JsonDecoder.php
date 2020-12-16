<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\Json;

use Phpro\HttpTools\Encoding\DecoderInterface;
use Psr\Http\Message\ResponseInterface;
use function Safe\json_decode;

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

        return (array) json_decode($responseBody, true);
    }
}
