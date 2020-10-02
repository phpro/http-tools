<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport;

use Phpro\HttpTools\Request\RequestInterface;

/**
 * @template R
 */
interface TransportInterface
{
    /**
     * @return R
     */
    public function __invoke(RequestInterface $request);
}
