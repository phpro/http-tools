<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\Stream;

use Phpro\HttpTools\Encoding\EncoderInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @implements EncoderInterface<StreamInterface>
 */
final class StreamEncoder implements EncoderInterface
{
    public static function createWithAutodiscoveredPsrFactories(): self
    {
        return new self();
    }

    /**
     * @param StreamInterface $data
     */
    public function __invoke(RequestInterface $request, $data): RequestInterface
    {
        return $request->withBody($data);
    }
}
