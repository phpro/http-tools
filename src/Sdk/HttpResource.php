<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Sdk;

use Phpro\HttpTools\Transport\TransportInterface;

/**
 * @template ResponseType
 */
abstract class HttpResource
{
    /**
     * @var TransportInterface<array|null, ResponseType>
     */
    private TransportInterface $transport;

    /**
     * @param TransportInterface<array|null, ResponseType> $transport
     */
    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    abstract protected function path(): string;

    /**
     * @return TransportInterface<array|null, ResponseType>
     */
    protected function transport(): TransportInterface
    {
        return $this->transport;
    }
}
