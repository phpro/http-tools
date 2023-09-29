<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Helper\Formatter;

use Http\Message\Formatter as HttpFormatter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class SimpleFormatter implements HttpFormatter
{
    public function formatRequest(RequestInterface $request): string
    {
        return sprintf(
            '%s %s %s',
            $request->getMethod(),
            $request->getUri()->__toString(),
            $request->getProtocolVersion()
        );
    }

    public function formatResponse(ResponseInterface $response): string
    {
        return sprintf(
            '%s %s %s',
            $response->getStatusCode(),
            $response->getReasonPhrase(),
            $response->getProtocolVersion()
        );
    }

    public function formatResponseForRequest(ResponseInterface $response, RequestInterface $request): string
    {
        return sprintf(
            '%s %s %s',
            $response->getStatusCode(),
            $response->getReasonPhrase(),
            $response->getProtocolVersion()
        );
    }
}
