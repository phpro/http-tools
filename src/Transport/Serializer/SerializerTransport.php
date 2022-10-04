<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport\Serializer;

use Phpro\HttpTools\Request\Request;
use Phpro\HttpTools\Request\RequestInterface;
use Phpro\HttpTools\Serializer\SerializerInterface;
use Phpro\HttpTools\Transport\TransportInterface;

/**
 * @template RequestType
 * @template ResponseType
 *
 * @implements TransportInterface<RequestType, ResponseType>
 */
final class SerializerTransport implements TransportInterface
{
    /**
     * @var class-string<ResponseType>|null
     */
    private ?string $outputType = null;
    private SerializerInterface $serializer;

    /**
     * @var TransportInterface<string, string>
     */
    private TransportInterface $transport;

    /**
     * @param TransportInterface<string, string> $transport
     */
    public function __construct(
        SerializerInterface $serializer,
        TransportInterface $transport
    ) {
        $this->transport = $transport;
        $this->serializer = $serializer;
    }

    /**
     * @template NewResponseType of object
     *
     * @param class-string<NewResponseType> $output
     *
     * @return SerializerTransport<RequestType, NewResponseType>
     */
    public function withOutputType(string $output): self
    {
        /** @var SerializerTransport<RequestType, NewResponseType> $new */
        $new = new SerializerTransport($this->serializer, $this->transport);
        $new->outputType = $output;

        return $new;
    }

    public function __invoke(RequestInterface $request)
    {
        $response = ($this->transport)(
            new Request(
                $request->method(),
                $request->uri(),
                $request->uriParameters(),
                null === $request->body() ? '' : $this->serializer->serialize($request->body())
            )
        );

        if (null === $this->outputType) {
            return;
        }

        return $this->serializer->deserialize($response, $this->outputType);
    }
}
