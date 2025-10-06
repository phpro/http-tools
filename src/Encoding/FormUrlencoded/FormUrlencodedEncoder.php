<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\FormUrlencoded;

use Http\Discovery\Psr17FactoryDiscovery;

use const PHP_QUERY_RFC1738;

use Phpro\HttpTools\Encoding\EncoderInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * @implements EncoderInterface<array|null>
 */
final class FormUrlencodedEncoder implements EncoderInterface
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
            ->withAddedHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($this->streamFactory->createStream(
                null !== $data ? http_build_query($data, encoding_type: PHP_QUERY_RFC1738) : ''
            ));
    }
}
