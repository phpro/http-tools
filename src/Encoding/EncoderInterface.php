<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding;

use Psr\Http\Message\RequestInterface;

/**
 * @template DataType
 */
interface EncoderInterface
{
    /**
     * @param DataType $data
     */
    public function __invoke(RequestInterface $request, $data): RequestInterface;

    /**
     * @return self<DataType>
     */
    public static function createWithAutodiscoveredPsrFactories(): self;
}
