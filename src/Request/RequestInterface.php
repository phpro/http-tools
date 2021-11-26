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
     * @return 'DELETE'|'GET'|'PATCH'|'POST'|'PUT'
     */
    public function method(): string;

    public function uri(): string;

    public function uriParameters(): array;

    /**
     * @return BodyType
     */
    public function body();
}
