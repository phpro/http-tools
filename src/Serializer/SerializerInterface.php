<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Serializer;

interface SerializerInterface
{
    public function serialize(mixed $data): string;

    /**
     * @template C
     *
     * @param class-string<C> $class
     *
     * @return C
     */
    public function deserialize(string $data, string $class);
}
