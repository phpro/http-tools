<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Uri;

use Http\Discovery\Psr17FactoryDiscovery;
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

    public static function createWithAutodiscoveredPsrFactories(): self
    {
        return new self(Psr17FactoryDiscovery::findUriFactory());
    }

    public function __invoke(RequestInterface $request): UriInterface
    {
        $uri = $this->uriFactory->createUri($request->uri());

        if (!$request->uriParameters()) {
            return $uri;
        }

        return $uri->withQuery(http_build_query($request->uriParameters()));
    }
}
