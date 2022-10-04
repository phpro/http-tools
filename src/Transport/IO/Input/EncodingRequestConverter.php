<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport\IO\Input;

use Http\Discovery\Psr17FactoryDiscovery;
use Phpro\HttpTools\Encoding\EncoderInterface;
use Phpro\HttpTools\Request\RequestInterface;
use Phpro\HttpTools\Uri\UriBuilderInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;

/**
 * @template RequestType
 *
 * @implements RequestConverterInterface<RequestType>
 */
final class EncodingRequestConverter implements RequestConverterInterface
{
    private RequestFactoryInterface $requestFactory;
    private UriBuilderInterface $uriBuilder;
    /**
     * @var EncoderInterface<RequestType>
     */
    private EncoderInterface $encoder;

    /**
     * @param EncoderInterface<RequestType> $encoder
     */
    public function __construct(
        RequestFactoryInterface $requestFactory,
        UriBuilderInterface $uriBuilder,
        EncoderInterface $encoder
    ) {
        $this->requestFactory = $requestFactory;
        $this->encoder = $encoder;
        $this->uriBuilder = $uriBuilder;
    }

    public static function createWithAutodiscoveredPsrFactories(
        UriBuilderInterface $uriBuilder,
        EncoderInterface $encoder
    ): self {
        return new self(
            Psr17FactoryDiscovery::findRequestFactory(),
            $uriBuilder,
            $encoder
        );
    }

    public function __invoke(RequestInterface $request): PsrRequestInterface
    {
        return ($this->encoder)(
            $this->requestFactory->createRequest(
                $request->method(),
                ($this->uriBuilder)($request)
            ),
            $request->body()
        );
    }
}
