<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Sdk\Rest;

use Phpro\HttpTools\Request\Request;
use Phpro\HttpTools\Request\RequestInterface;
use Phpro\HttpTools\Transport\TransportInterface;

/**
 * @template ResponseType
 */
trait UpdateTrait
{
    abstract protected function transport(): TransportInterface;

    abstract protected function path(): string;

    /**
     * @return ResponseType
     */
    public function update(string $identifier, array $data)
    {
        /** @var RequestInterface<array|null> $request */
        $request = new Request('PUT', $this->path().'/'.$identifier, [], $data);

        return $this->transport()($request);
    }
}
