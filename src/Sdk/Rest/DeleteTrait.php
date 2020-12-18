<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Sdk\Rest;

use Phpro\HttpTools\Request\Request;
use Phpro\HttpTools\Request\RequestInterface;
use Phpro\HttpTools\Transport\TransportInterface;

/**
 * @template ResponseType
 */
trait DeleteTrait
{
    abstract protected function transport(): TransportInterface;

    abstract protected function path(): string;

    /**
     * @return ResponseType
     */
    public function delete(string $identifier)
    {
        /** @var RequestInterface<array|null> $request */
        $request = new Request('DELETE', $this->path().'/'.$identifier, [], null);

        return $this->transport()($request);
    }
}
