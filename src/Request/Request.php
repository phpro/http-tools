<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Request;

/**
 * @template RequestType
 *
 * @psalm-immutable
 *
 * @psalm-import-type Method from RequestInterface
 *
 * @implements RequestInterface<RequestType>
 */
final class Request implements RequestInterface
{
    /**
     * @var Method
     */
    private string $method;
    private string $uri;
    private array $uriParameters;

    /**
     * @var RequestType
     */
    private $body;

    /**
     * @param Method $method
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
     * @return Method
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
