<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\ContentType;

/**
 * @template T
 */
final readonly class ContentTypeAwarePayload
{
    /**
     * @param T $payload
     */
    public function __construct(
        public string $contentType,
        public mixed $payload,
    ) {
    }
}
