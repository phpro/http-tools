<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport;

use Phpro\HttpTools\Encoding\DecoderInterface;
use Phpro\HttpTools\Encoding\EncoderInterface;
use function Amp\call;
use Amp\Promise;
use Generator;
use Http\Client\HttpAsyncClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Phpro\HttpTools\Async\HttplugPromiseAdapter;
use Phpro\HttpTools\Request\RequestInterface;
use Phpro\HttpTools\Uri\UriBuilderInterface;
use Psr\Http\Message\RequestFactoryInterface;

/**
 * @template RequestType
 * @template ResponseType
 *
 * @implements AsyncTransportInterface<RequestType, ResponseType>
 */
final class AsyncEncodedTransport implements AsyncTransportInterface
{
    private HttpAsyncClient $client;
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
        HttpAsyncClient $client,
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
        HttpAsyncClient $client,
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
     * @return Promise<ResponseType>
     */
    public function __invoke(RequestInterface $request): Promise
    {
        $httpRequest = ($this->encoder)(
            $this->requestFactory->createRequest(
                $request->method(),
                ($this->uriBuilder)($request)
            ),
            $request->body()
        );

        $httpPromise = $this->client->sendAsyncRequest($httpRequest);

        return call(
            function () use ($httpPromise): Generator {
                $response = yield HttplugPromiseAdapter::adapt($httpPromise);

                return ($this->decoder)($response);
            }
        );
    }
}
