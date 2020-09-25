<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Test;

use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

trait UseHttpFactories
{
    private function createRequest(string $method, string $uri): RequestInterface
    {
        return Psr17FactoryDiscovery::findRequestFactory()->createRequest($method, $uri);
    }

    private function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return Psr17FactoryDiscovery::findResponseFactory()->createResponse($code, $reasonPhrase);
    }

    private function createStream(string $content): StreamInterface
    {
        return Psr17FactoryDiscovery::findStreamFactory()->createStream($content);
    }
}
