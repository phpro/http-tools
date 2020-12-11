<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\Stream;

use Phpro\HttpTools\Encoding\Stream\StreamEncoder;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Phpro\HttpTools\Encoding\Stream\StreamEncoder
 * @uses \Phpro\HttpTools\Test\UseHttpFactories
 */
final class StreamEncoderTest extends TestCase
{
    use UseHttpFactories;

    /** @test */
    public function it_can_encode_stream_string(): void
    {
        $data = $this->createStream($content = 'Hello world');
        $encoder = StreamEncoder::createWithAutodiscoveredPsrFactories();
        $request = $this->createRequest('POST', '/hello');

        $actual = $encoder($request, $data);

        self::assertSame($request->getMethod(), $actual->getMethod());
        self::assertSame($request->getUri(), $actual->getUri());
        self::assertSame($content, (string) $actual->getBody());
    }
}
