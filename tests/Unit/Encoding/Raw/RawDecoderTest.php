<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\Raw;

use Phpro\HttpTools\Encoding\Raw\RawDecoder;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\TestCase;

final class RawDecoderTest extends TestCase
{
    use UseHttpFactories;

    /** @test */
    public function it_can_decode_raw_string(): void
    {
        $decoder = RawDecoder::createWithAutodiscoveredPsrFactories();
        $response = $this->createResponse()
            ->withBody($this->createStream($expected = '{"hello": "world"}'));
        $decoded = $decoder($response);

        self::assertSame($expected, $decoded);
    }
}
