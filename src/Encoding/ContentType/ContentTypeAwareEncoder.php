<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\ContentType;

use Phpro\HttpTools\Encoding\EncoderInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @template T
 *
 * @implements EncoderInterface<ContentTypeAwarePayload<T>>
 */
final class ContentTypeAwareEncoder implements EncoderInterface
{
    /**
     * @param EncoderInterface<T> $encoder
     */
    public function __construct(
        private EncoderInterface $encoder,
    ) {
    }

    /**
     * @param ContentTypeAwarePayload<T> $data
     */
    public function __invoke(RequestInterface $request, $data): RequestInterface
    {
        $request = ($this->encoder)($request, $data->payload);

        return $request->withHeader('Content-Type', $data->contentType);
    }
}
