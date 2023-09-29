<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Uri;

use League\Uri\Http;
use League\Uri\UriTemplate;
use Phpro\HttpTools\Request\RequestInterface;
use Psr\Http\Message\UriInterface;

final class TemplatedUriBuilder implements UriBuilderInterface
{
    private array $defaultVariables;

    public function __construct(array $defaultVariables = [])
    {
        $this->defaultVariables = $defaultVariables;
    }

    public function __invoke(RequestInterface $request): UriInterface
    {
        $uriTemplate = new UriTemplate($request->uri(), $this->defaultVariables);

        return Http::new($uriTemplate->expand($request->uriParameters()));
    }
}
