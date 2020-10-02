<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport\Json;

use Http\Discovery\Psr17FactoryDiscovery;
use Phpro\HttpTools\Request\RequestInterface;
use Phpro\HttpTools\Transport\TransportInterface;
use Phpro\HttpTools\Uri\UriBuilderInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use function Safe\json_decode;
use function Safe\json_encode;

/**
 * @implements TransportInterface<array|null, array>
 */
final class JsonTransport implements TransportInterface
{
    private ClientInterface $client;
    private UriBuilderInterface $uriBuilder;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

    public function __construct(
        ClientInterface $client,
        UriBuilderInterface $uriBuilder,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->client = $client;
        $this->uriBuilder = $uriBuilder;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    public static function createWithAutodiscoveredPsrFactories(
        ClientInterface $client,
        UriBuilderInterface $uriBuilder
    ): self {
        return new self(
            $client,
            $uriBuilder,
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory()
        );
    }

    /**
     * @param RequestInterface<array|null> $request
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Safe\Exceptions\JsonException
     */
    public function __invoke(RequestInterface $request): array
    {
        $httpRequest = $this->requestFactory->createRequest(
            $request->method(),
            ($this->uriBuilder)($request)
        )
            ->withAddedHeader('Content-Type', 'application/json')
            ->withAddedHeader('Accept', 'application/json')
            ->withBody($this->streamFactory->createStream(
                null !== $request->body() ? json_encode($request->body()) : ''
            ));

        $response = $this->client->sendRequest($httpRequest);

        return (array) json_decode((string) $response->getBody(), true);
    }
}
