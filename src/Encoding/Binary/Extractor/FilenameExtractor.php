<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\Binary\Extractor;

use cardinalby\ContentDisposition\ContentDisposition;

use function Psl\Iter\first;
use function Psl\Result\try_catch;

use Psr\Http\Message\ResponseInterface;

final class FilenameExtractor
{
    public function __invoke(ResponseInterface $response): ?string
    {
        if (!$disposition = first($response->getHeader('Content-Disposition'))) {
            return null;
        }

        return try_catch(
            static fn () => ContentDisposition::parse($disposition)->getFilename(),
            static fn () => null
        );
    }
}
