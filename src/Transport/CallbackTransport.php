<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport;

use Phpro\HttpTools\Request\RequestInterface;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * @template RequestType
 * @template ResponseType
 *
 * @implements TransportInterface<RequestType, ResponseType>
 */
final class CallbackTransport implements TransportInterface
{
    /**
     * @var callable(RequestInterface<RequestType>): PsrRequestInterface
     */
    private $requestConverter;

    /**
     * @var callable(PsrResponseInterface): ResponseType
     */
    private $responseConverter;

    /**
     * @var callable(PsrRequestInterface): PsrResponseInterface
     */
    private $sender;

    /**
     * @param callable(RequestInterface<RequestType>): PsrRequestInterface $requestConverter
     * @param callable(PsrRequestInterface): PsrResponseInterface $sender
     * @param callable(PsrResponseInterface): ResponseType $responseConverter
     */
    public function __construct(
        callable $requestConverter,
        callable $sender,
        callable $responseConverter
    ) {
        $this->requestConverter = $requestConverter;
        $this->sender = $sender;
        $this->responseConverter = $responseConverter;
    }

    /**
     * @param RequestInterface<RequestType> $request
     *
     * @return ResponseType
     */
    public function __invoke(RequestInterface $request)
    {
        $httpRequest = ($this->requestConverter)($request);
        $httpResponse = ($this->sender)($httpRequest);

        return ($this->responseConverter)($httpResponse);
    }
}
