<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\Binary\Extractor;

use function Psl\Iter\first;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Mime\MimeTypes;

final class ExtensionExtractor
{
    public function __invoke(ResponseInterface $response): ?string
    {
        if ($mimeType = (new MimeTypeExtractor())($response)) {
            $extensions = MimeTypes::getDefault()->getExtensions($mimeType);
            if ($extensions) {
                return first($extensions);
            }
        }

        if ($originalName = (new FilenameExtractor())($response)) {
            return pathinfo($originalName, PATHINFO_EXTENSION) ?: null;
        }

        return null;
    }
}
