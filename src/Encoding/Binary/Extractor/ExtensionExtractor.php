<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\Binary\Extractor;

use Phpro\HttpTools\Dependency\SymfonyMimeDependency;

use function Psl\Iter\first;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Mime\MimeTypes;

final class ExtensionExtractor
{
    public function __invoke(ResponseInterface $response): ?string
    {
        $mimeType = (new MimeTypeExtractor())($response);
        if (null !== $mimeType) {
            SymfonyMimeDependency::guard();
            $extensions = MimeTypes::getDefault()->getExtensions($mimeType);
            if ($extensions) {
                return first($extensions);
            }
        }

        $originalName = (new FilenameExtractor())($response);
        if (null !== $originalName) {
            return pathinfo($originalName, PATHINFO_EXTENSION) ?: null;
        }

        return null;
    }
}
