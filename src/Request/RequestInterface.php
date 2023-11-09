<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Request;

/**
 * @template BodyType
 *
 * @psalm-immutable
 *
 * @psalm-type Method = 'POST'|'GET'|'DELETE'|'PATCH'|'PUT'|'OPTIONS'|'HEAD';
 */
interface RequestInterface
{
    /**
     * @return Method
     */
    public function method(): string;

    public function uri(): string;

    public function uriParameters(): array;

    /**
     * @return BodyType
     */
    public function body();
}
