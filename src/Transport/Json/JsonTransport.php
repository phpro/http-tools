<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport\Json;

use Http\Message\RequestFactory;
use Phpro\HttpTools\Request\RequestInterface;
use Phpro\HttpTools\Transport\TransportInterface;
use Phpro\HttpTools\Uri\UriBuilderInterface;
use Psr\Http\Client\ClientInterface;
use function Safe\json_decode;
use function Safe\json_encode;

final class JsonTransport implements TransportInterface
{
    private ClientInterface $client;
    private RequestFactory $requestFactory;
    private UriBuilderInterface $uriBuilder;

    public function __construct(
        ClientInterface $client,
        RequestFactory $requestFactory,
        UriBuilderInterface $uriBuilder
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->uriBuilder = $uriBuilder;
    }

    public function __invoke(RequestInterface $request): array
    {
        $response = $this->client->sendRequest(
            $httpRequest = $this->requestFactory->createRequest(
                $request->method(),
                ($this->uriBuilder)($request),
                [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                $body = json_encode($request->body())
            )
        );

        return (array) json_decode((string) $response->getBody(), true);
    }
}
