<?php

declare(strict_types=1);

namespace Phpro\HttpTools\StaticAnalysis\Transport\Serializer;

use Phpro\HttpTools\Request\Request;
use Phpro\HttpTools\Serializer\SerializerInterface;
use Phpro\HttpTools\Transport\Serializer\SerializerTransport;
use Phpro\HttpTools\Transport\TransportInterface;

final class Foo
{
}

/**
 * @param SerializerTransport<Foo, null> $x
 *
 * @return null
 */
function testEmptySerializer(SerializerTransport $x)
{
    return $x(new Request('GET', '/', [], new Foo()));
}

/**
 * @param SerializerTransport<Foo, Foo> $x
 */
function testTargetSerializer(SerializerTransport $x): Foo
{
    return $x(new Request('GET', '/', [], new Foo()));
}

/**
 * @param TransportInterface<string, string> $transport
 */
function test(SerializerInterface $serializer, TransportInterface $transport): void
{
    /** @var SerializerTransport<Foo, null> $serializerTransport */
    $serializerTransport = new SerializerTransport($serializer, $transport);

    testEmptySerializer($serializerTransport);

    testTargetSerializer($serializerTransport->withOutputType(Foo::class));
}
