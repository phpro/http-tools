<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\FormUrlencoded;

use Phpro\HttpTools\Encoding\DecoderInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @implements DecoderInterface<array>
 */
final class FormUrlencodedDecoder implements DecoderInterface
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

        parse_str($responseBody, $output);

        return $output;
    }
}
