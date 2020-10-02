<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport;

use Amp\Promise;
use Phpro\HttpTools\Request\RequestInterface;

/**
 * @template R
 */
interface AsyncTransportInterface
{
    /**
     * @return Promise<R>
     */
    public function __invoke(RequestInterface $request): Promise;
}
