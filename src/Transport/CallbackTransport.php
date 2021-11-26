<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport;

use function Amp\call;
use Amp\Promise as AmpPromise;
use Generator;
use Http\Promise\Promise as HttpPromise;
use Phpro\HttpTools\Async\HttplugPromiseAdapter;
use Phpro\HttpTools\Request\RequestInterface;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * @template RequestType
 * @template ResponseType
 * @template AsyncSender of bool
 *
 * @implements TransportInterface<RequestType, (AsyncSender is true ? AmpPromise<ResponseType> : ResponseType)>
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
     * @var callable(PsrRequestInterface): (AsyncSender is true ? HttpPromise : PsrResponseInterface)
     */
    private $sender;

    /**
     * @param callable(RequestInterface<RequestType>): PsrRequestInterface $requestConverter
     * @param callable(PsrRequestInterface): (AsyncSender is true ? HttpPromise : PsrResponseInterface) $sender
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
     * @return (AsyncSender is true ? AmpPromise<ResponseType> : ResponseType)
     */
    public function __invoke(RequestInterface $request)
    {
        $httpRequest = ($this->requestConverter)($request);
        $httpResponse = ($this->sender)($httpRequest);

        return $this->handleHttpResponse($httpResponse);
    }

    /**
     * @param (AsyncSender is true ? HttpPromise : PsrResponseInterface) $httpResponse
     *
     * @return (AsyncSender is true ? AmpPromise<ResponseType> : ResponseType)
     */
    private function handleHttpResponse($httpResponse)
    {
        if ($httpResponse instanceof HttpPromise) {
            return call(
                function () use ($httpResponse): Generator {
                    $response = yield HttplugPromiseAdapter::adapt($httpResponse);

                    return ($this->responseConverter)($response);
                }
            );
        }

        return ($this->responseConverter)($httpResponse);
    }
}
