<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\Binary\Extractor;

use function Psl\Iter\first;
use function Psl\Result\try_catch;
use function Psl\Type\int;

use Psr\Http\Message\ResponseInterface;

final class SizeExtractor
{
    public function __invoke(ResponseInterface $response): ?int
    {
        $size = $response->getBody()->getSize();
        if (null !== $size) {
            return $size;
        }

        if ($length = first($response->getHeader('Content-Length'))) {
            return try_catch(
                static fn () => int()->coerce($length),
                static fn () => null
            );
        }

        return null;
    }
}
