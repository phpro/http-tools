<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Helper\Request;

use Phpro\HttpTools\Request\RequestInterface;

class SampleRequest implements RequestInterface
{
    private string $method;
    private string $uri;
    private array $uriParameters;
    private array $body;

    public function __construct(string $method, string $uri, array $uriParameters, array $body)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->uriParameters = $uriParameters;
        $this->body = $body;
    }

    public static function createWithUri(string $uri, array $params = []): self
    {
        return new self('GET', $uri, $params, []);
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

    public function body(): array
    {
        return $this->body;
    }
}
