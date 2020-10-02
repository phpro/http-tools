<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport;

use Amp\Promise;
use Phpro\HttpTools\Request\RequestInterface;

/**
 * @template RequestType
 * @template ResponseType
 */
interface AsyncTransportInterface
{
    /**
     * @param RequestInterface<RequestType> $request
     *
     * @return Promise<ResponseType>
     */
    public function __invoke(RequestInterface $request): Promise;
}
