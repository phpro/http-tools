<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Test;

use Phpro\HttpTools\Request\RequestInterface;

trait UseHttpToolsFactories
{
    /**
     * @template B
     *
     * @param 'POST'|'GET'|'PUT'|'PATCH'|'DELETE' $method
     * @param B $body
     *
     * @return RequestInterface<B>
     */
    private function createToolsRequest(
        string $method,
        string $uri,
        array $uriParams = [],
        $body = null
    ): RequestInterface
    {
        return new class($method, $uri, $uriParams, $body) implements RequestInterface {
            private string $method;
            private string $uri;
            private array $uriParameters;
            private $body;

            /**
             * @param 'POST'|'GET'|'PUT'|'PATCH'|'DELETE' $method
             * @param mixed $body
             */
            public function __construct(string $method, string $uri, array $uriParameters, $body)
            {
                $this->method = $method;
                $this->uri = $uri;
                $this->uriParameters = $uriParameters;
                $this->body = $body;
            }

            public function method(): string
            {
                return $this->method;
            }

            public function uri(): string
            {
                return $this->uri;
            }

            public function uriParameters(): array
            {
                return $this->uriParameters;
            }

            public function body()
            {
                return $this->body;
            }
        };
    }
}
