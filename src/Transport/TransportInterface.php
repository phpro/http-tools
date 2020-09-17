<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport;

use Phpro\HttpTools\Request\RequestInterface;

interface TransportInterface
{
    public function __invoke(RequestInterface $request): array;
}
