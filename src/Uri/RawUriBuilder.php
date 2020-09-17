<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Uri;

use Phpro\HttpTools\Request\RequestInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

final class RawUriBuilder implements UriBuilderInterface
{
    private UriFactoryInterface $uriFactory;

    public function __construct(UriFactoryInterface $uriFactory)
    {
        $this->uriFactory = $uriFactory;
    }

    public function __invoke(RequestInterface $request): UriInterface
    {
        return $this->uriFactory->createUri($request->uri());
    }
}
