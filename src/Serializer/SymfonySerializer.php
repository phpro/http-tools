<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Serializer;

use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;

final class SymfonySerializer implements SerializerInterface
{
    private SymfonySerializerInterface $serializer;
    private string $format;

    public function __construct(SymfonySerializerInterface $serializer, string $format)
    {
        $this->serializer = $serializer;
        $this->format = $format;
    }

    /**
     * @param mixed $data
     */
    public function serialize($data): string
    {
        return $this->serializer->serialize($data, $this->format);
    }

    /**
     * @template C
     *
     * @param class-string<C> $class
     *
     * @return C
     */
    public function deserialize(string $data, string $class)
    {
        /** @var C $result */
        $result = $this->serializer->deserialize($data, $class, $this->format);

        return $result;
    }
}
