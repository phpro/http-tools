<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport;

use Phpro\HttpTools\Request\RequestInterface;

interface TransportInterface
{
    /**
     * @return mixed Feel free to use any return type you please!
     */
    public function __invoke(RequestInterface $request);
}
