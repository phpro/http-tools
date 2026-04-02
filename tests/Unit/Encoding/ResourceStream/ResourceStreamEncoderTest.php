<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\ResourceStream;

use Phpro\HttpTools\Encoding\ResourceStream\ResourceStreamEncoder;
use Phpro\HttpTools\Test\UseHttpFactories;
use Phpro\ResourceStream\Factory\MemoryStream;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResourceStreamEncoderTest extends TestCase
{
    use UseHttpFactories;

    #[Test]
    public function it_can_encode_resource_stream(): void
    {
        $resourceStream = MemoryStream::create();
        $resourceStream->write($content = 'Hello world');
        $resourceStream->rewind();

        $encoder = ResourceStreamEncoder::createWithAutodiscoveredPsrFactories();
        $request = $this->createRequest('POST', '/hello');

        $actual = $encoder($request, $resourceStream);

        self::assertSame($request->getMethod(), $actual->getMethod());
        self::assertSame($request->getUri(), $actual->getUri());
        self::assertSame($content, (string) $actual->getBody());
        self::assertFalse($actual->hasHeader('Content-Type'));
    }

    #[Test]
    public function it_can_encode_resource_stream_without_rewinding(): void
    {
        $resourceStream = MemoryStream::create();
        $resourceStream->write($content = 'Hello world');

        $encoder = ResourceStreamEncoder::createWithAutodiscoveredPsrFactories();
        $request = $this->createRequest('POST', '/hello');

        $actual = $encoder($request, $resourceStream);

        self::assertSame($content, (string) $actual->getBody());
    }
}
