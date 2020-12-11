<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\Stream;

use Phpro\HttpTools\Encoding\Stream\StreamDecoder;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\TestCase;

final class StreamDecoderTest extends TestCase
{
    use UseHttpFactories;

    /** @test */
    public function it_can_decode_stream_string(): void
    {
        $decoder = StreamDecoder::createWithAutodiscoveredPsrFactories();
        $response = $this->createResponse()
            ->withBody($expected = $this->createStream('{"hello": "world"}'));
        $decoded = $decoder($response);

        self::assertSame($expected, $decoded);
    }
}
