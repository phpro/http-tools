<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Uri;

use Phpro\HttpTools\Request\RequestInterface;
use Psr\Http\Message\UriInterface;

interface UriBuilderInterface
{
    public function __invoke(RequestInterface $request): UriInterface;
}
