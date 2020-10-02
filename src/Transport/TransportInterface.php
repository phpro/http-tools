<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport;

use Phpro\HttpTools\Request\RequestInterface;

/**
 * @template RequestType
 * @template ResponseType
 */
interface TransportInterface
{
    /**
     * @param RequestInterface<RequestType> $request
     *
     * @return ResponseType
     */
    public function __invoke(RequestInterface $request);
}
