<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Test;

use Phpro\HttpTools\Request\Request;
use Phpro\HttpTools\Request\RequestInterface;

trait UseHttpToolsFactories
{
    /**
     * @template B
     *
     * @param 'DELETE'|'GET'|'PATCH'|'POST'|'PUT' $method
     * @param B $body
     *
     * @return RequestInterface<B>
     */
    private function createToolsRequest(
        string $method,
        string $uri,
        array $uriParams = [],
        $body = null
    ): RequestInterface {
        return new Request($method, $uri, $uriParams, $body);
    }
}
