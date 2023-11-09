<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\Mime;

use Http\Discovery\Psr17FactoryDiscovery;
use Phpro\HttpTools\Dependency\SymfonyMimeDependency;
use Phpro\HttpTools\Encoding\EncoderInterface;

use function Psl\Type\string;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\Mime\Part\AbstractMultipartPart as MultiPart;

/**
 * @implements EncoderInterface<MultiPart>
 */
final class MultiPartEncoder implements EncoderInterface
{
    private StreamFactoryInterface $streamFactory;

    public function __construct(StreamFactoryInterface $streamFactory)
    {
        SymfonyMimeDependency::guard();

        $this->streamFactory = $streamFactory;
    }

    public static function createWithAutodiscoveredPsrFactories(): self
    {
        return new self(
            Psr17FactoryDiscovery::findStreamFactory()
        );
    }

    /**
     * @param MultiPart $data
     */
    public function __invoke(RequestInterface $request, $data): RequestInterface
    {
        return $request
            ->withAddedHeader(
                'Content-Type',
                string()->assert($data->getPreparedHeaders()->get('content-type')?->getBodyAsString())
            )
            ->withBody($this->streamFactory->createStream(
                $data->bodyToString()
            ));
    }
}
