<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\Json;

use Http\Discovery\Psr17FactoryDiscovery;
use Phpro\HttpTools\Encoding\EncoderInterface;
use Psl\Json;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * @implements EncoderInterface<array|null>
 */
final class JsonEncoder implements EncoderInterface
{
    private StreamFactoryInterface $streamFactory;

    public function __construct(StreamFactoryInterface $streamFactory)
    {
        $this->streamFactory = $streamFactory;
    }

    public static function createWithAutodiscoveredPsrFactories(): self
    {
        return new self(
            Psr17FactoryDiscovery::findStreamFactory()
        );
    }

    /**
     * @param array|null $data
     */
    public function __invoke(RequestInterface $request, $data): RequestInterface
    {
        return $request
            ->withAddedHeader('Content-Type', 'application/json')
            ->withAddedHeader('Accept', 'application/json')
            ->withBody($this->streamFactory->createStream(
                null !== $data ? Json\encode($data) : ''
            ));
    }
}
