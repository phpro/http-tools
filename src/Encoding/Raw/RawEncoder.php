<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\Raw;

use Http\Discovery\Psr17FactoryDiscovery;
use Phpro\HttpTools\Encoding\EncoderInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * @implements EncoderInterface<string|null>
 */
final class RawEncoder implements EncoderInterface
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
     * @param string|null $data
     */
    public function __invoke(RequestInterface $request, $data): RequestInterface
    {
        return $request->withBody($this->streamFactory->createStream($data ?? ''));
    }
}
