<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\Json;

use Phpro\HttpTools\Encoding\Json\JsonDecoder;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Phpro\HttpTools\Encoding\Json\JsonDecoder
 * @uses \Phpro\HttpTools\Test\UseHttpFactories
 */
final class JsonDecoderTest extends TestCase
{
    use UseHttpFactories;

    /** @test */
    public function it_can_decode_json_to_array(): void
    {
        $decoder = JsonDecoder::createWithAutodiscoveredPsrFactories();
        $response = $this->createResponse()
            ->withBody($this->createStream('{"hello": "world"}'));
        $decoded = $decoder($response);

        self::assertSame(['hello' => 'world'], $decoded);
    }
}
