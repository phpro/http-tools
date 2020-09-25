<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport\Json;

use Amp\Promise;
use Http\Client\HttpAsyncClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Phpro\HttpTools\Request\RequestInterface;
use Phpro\HttpTools\Transport\TransportInterface;
use Phpro\HttpTools\Uri\UriBuilderInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Webmozart\Assert\Assert;
use function Safe\json_decode;
use function Safe\json_encode;
use function Amp\call;

final class AsyncJsonTransport implements TransportInterface
{
    private HttpAsyncClient $client;
    private UriBuilderInterface $uriBuilder;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

    public function __construct(
        HttpAsyncClient $client,
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
        HttpAsyncClient $client,
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
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Safe\Exceptions\JsonException
     *
     * @return Promise<array<array-key, mixed>>
     */
    public function __invoke(RequestInterface $request): Promise
    {
        $httpRequest = $this->requestFactory->createRequest(
            $request->method(),
            ($this->uriBuilder)($request)
        )
            ->withAddedHeader('Content-Type', 'application/json')
            ->withAddedHeader('Accept', 'application/json')
            ->withBody($this->streamFactory->createStream(
                $body = json_encode($request->body())
            ));

        return call(
            function () use ($httpRequest): array {
                $response = $this->client->sendAsyncRequest($httpRequest)->wait();
                Assert::isInstanceOf($response, ResponseInterface::class);

                return (array) json_decode((string) $response->getBody(), true);
            }
        );
    }
}
