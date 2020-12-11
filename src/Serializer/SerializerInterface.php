<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Serializer;

interface SerializerInterface
{
    /**
     * @param mixed $data
     */
    public function serialize($data): string;

    /**
     * @template C
     *
     * @param class-string<C> $class
     *
     * @return C
     */
    public function deserialize(string $data, string $class);
}
