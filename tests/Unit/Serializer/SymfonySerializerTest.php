<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Serializer;

use Phpro\HttpTools\Serializer\SerializerInterface;
use Phpro\HttpTools\Serializer\SymfonySerializer;
use Phpro\HttpTools\Tests\Helper\Model\SomeValueObject;
use PHPUnit\Framework\TestCase;
use Psl\Json;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class SymfonySerializerTest extends TestCase
{
    private SymfonySerializer $serializer;

    protected function setUp(): void
    {
        $normalizers = [new ObjectNormalizer()];
        $encoders = [new JsonEncoder()];

        $this->serializer = new SymfonySerializer(
            new Serializer($normalizers, $encoders),
            'json'
        );
    }

    /** @test */
    public function it_is_a_serializer(): void
    {
        self::assertInstanceOf(SerializerInterface::class, $this->serializer);
    }

    /** @test */
    public function it_can_serialize_value_object_to_string(): void
    {
        $valueObject = new SomeValueObject('Hello', 'World');

        self::assertSame(
            Json\encode(['x' => 'Hello', 'y' => 'World']),
            $this->serializer->serialize($valueObject)
        );
    }

    /** @test */
    public function it_can_deserialize_string_to_value_object(): void
    {
        $data = Json\encode(['x' => 'Hello', 'y' => 'World']);

        $result = $this->serializer->deserialize($data, SomeValueObject::class);

        self::assertEquals(
            new SomeValueObject('Hello', 'World'),
            $result
        );
    }
}
