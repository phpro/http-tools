<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport;

use Http\Promise\Promise;
use Phpro\HttpTools\Request\RequestInterface;

interface AsyncTransportInterface
{
    public function __invoke(RequestInterface $request): Promise;
}
