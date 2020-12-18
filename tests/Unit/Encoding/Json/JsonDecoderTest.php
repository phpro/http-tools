<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\Json;

use Phpro\HttpTools\Encoding\Json\JsonDecoder;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\TestCase;

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

    /** @test */
    public function it_can_decode_empty_body_to_empty_array(): void
    {
        $decoder = JsonDecoder::createWithAutodiscoveredPsrFactories();
        $response = $this->createResponse()->withBody($this->createStream(''));
        $decoded = $decoder($response);

        self::assertSame([], $decoded);
    }
}
