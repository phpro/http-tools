<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Request;

/**
 * @template BodyType
 * @psalm-immutable
 */
interface RequestInterface
{
    /**
     * @return 'POST'|'GET'|'PUT'|'PATCH'|'DELETE'
     */
    public function method(): string;

    public function uri(): string;

    public function uriParameters(): array;

    /**
     * @return BodyType
     */
    public function body();
}
