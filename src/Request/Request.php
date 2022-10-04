<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Request;

/**
 * @template RequestType
 *
 * @psalm-immutable
 *
 * @implements RequestInterface<RequestType>
 */
final class Request implements RequestInterface
{
    /**
     * @var 'DELETE'|'GET'|'PATCH'|'POST'|'PUT'
     */
    private string $method;
    private string $uri;
    private array $uriParameters;

    /**
     * @var RequestType
     */
    private $body;

    /**
     * @param 'DELETE'|'GET'|'PATCH'|'POST'|'PUT' $method
     * @param RequestType $body
     */
    public function __construct(string $method, string $uri, array $uriParameters, $body)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->uriParameters = $uriParameters;
        $this->body = $body;
    }

    /**
     * @return 'DELETE'|'GET'|'PATCH'|'POST'|'PUT'
     */
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

    /**
     * @return RequestType
     */
    public function body()
    {
        return $this->body;
    }
}
