<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Test;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Mock\Client;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Webmozart\Assert\Assert;

trait UseMockClient
{
    /**
     * @param null|callable(Client $client): Client $configurator
     */
    private function mockClient(callable $configurator = null): Client
    {
        Assert::classExists(Client::class, 'Could not find a mock client. Please run: "composer require --dev php-http/mock-client"');
        $configurator ??= fn (Client $client) => $client;

        return $configurator(new Client());
    }

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
