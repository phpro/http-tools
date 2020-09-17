<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Plugin;

use Http\Client\Common\Plugin as HttpPlugin;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;

final class AcceptLanguagePlugin implements HttpPlugin
{
    private string $acceptLanguage;

    public function __construct(string $acceptLanguage)
    {
        $this->acceptLanguage = $acceptLanguage;
    }

    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        return $next(
            $request->withAddedHeader(
                'Accept-Language',
                $this->acceptLanguage
            )
        );
    }
}
