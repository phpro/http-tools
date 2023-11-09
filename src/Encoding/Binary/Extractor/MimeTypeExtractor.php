<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\Binary\Extractor;

use function Psl\Iter\first;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Mime\MimeTypes;

final class MimeTypeExtractor
{
    public function __invoke(ResponseInterface $response): ?string
    {
        if ($contentType = first($response->getHeader('Content-Type'))) {
            return $contentType;
        }

        if ($originalName = (new FilenameExtractor())($response)) {
            if ($extension = pathinfo($originalName, PATHINFO_EXTENSION)) {
                $mimeTypes = MimeTypes::getDefault()->getMimeTypes($extension);

                return first($mimeTypes);
            }
        }

        return null;
    }
}
