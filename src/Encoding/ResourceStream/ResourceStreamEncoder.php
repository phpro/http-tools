<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\ResourceStream;

use Http\Discovery\Psr17FactoryDiscovery;
use Phpro\HttpTools\Encoding\EncoderInterface;
use Phpro\ResourceStream\ResourceStream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * @implements EncoderInterface<ResourceStream<resource>>
 */
final class ResourceStreamEncoder implements EncoderInterface
{
    public function __construct(
        private StreamFactoryInterface $streamFactory,
    ) {
    }

    public static function createWithAutodiscoveredPsrFactories(): self
    {
        return new self(
            Psr17FactoryDiscovery::findStreamFactory(),
        );
    }

    public function __invoke(RequestInterface $request, $data): RequestInterface
    {
        return $request->withBody(
            $this->streamFactory->createStreamFromResource($data->unwrap()),
        );
    }
}
