<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport;

use Http\Discovery\Psr17FactoryDiscovery;
use Phpro\HttpTools\Encoding\DecoderInterface;
use Phpro\HttpTools\Encoding\EncoderInterface;
use Phpro\HttpTools\Request\RequestInterface;
use Phpro\HttpTools\Uri\UriBuilderInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

/**
 * @template RequestType
 * @template ResponseType
 *
 * @implements TransportInterface<RequestType, ResponseType>
 */
final class EncodedTransport implements TransportInterface
{
    private ClientInterface $client;
    private UriBuilderInterface $uriBuilder;
    private RequestFactoryInterface $requestFactory;

    /**
     * @var EncoderInterface<RequestType>
     */
    private EncoderInterface $encoder;

    /**
     * @var DecoderInterface<ResponseType>
     */
    private DecoderInterface $decoder;

    /**
     * @param EncoderInterface<RequestType> $encoder
     * @param DecoderInterface<ResponseType> $decoder
     */
    public function __construct(
        ClientInterface $client,
        UriBuilderInterface $uriBuilder,
        EncoderInterface $encoder,
        DecoderInterface $decoder,
        RequestFactoryInterface $requestFactory
    ) {
        $this->client = $client;
        $this->uriBuilder = $uriBuilder;
        $this->encoder = $encoder;
        $this->decoder = $decoder;
        $this->requestFactory = $requestFactory;
    }

    /**
     * @param EncoderInterface<RequestType> $encoder
     * @param DecoderInterface<ResponseType> $decoder
     */
    public static function createWithAutodiscoveredPsrFactories(
        ClientInterface $client,
        UriBuilderInterface $uriBuilder,
        EncoderInterface $encoder,
        DecoderInterface $decoder
    ): self {
        return new self(
            $client,
            $uriBuilder,
            $encoder,
            $decoder,
            Psr17FactoryDiscovery::findRequestFactory()
        );
    }

    /**
     * @param RequestInterface<RequestType> $request
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Safe\Exceptions\JsonException
     *
     * @return ResponseType
     */
    public function __invoke(RequestInterface $request)
    {
        $httpRequest = ($this->encoder)(
            $this->requestFactory->createRequest(
                $request->method(),
                ($this->uriBuilder)($request)
            ),
            $request->body()
        );

        $response = $this->client->sendRequest($httpRequest);

        return ($this->decoder)($response);
    }
}
