<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\ResourceStream;

use Phpro\HttpTools\Encoding\ResourceStream\ResourceStreamDecoder;
use Phpro\HttpTools\Test\UseHttpFactories;
use Phpro\ResourceStream\ResourceStream;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResourceStreamDecoderTest extends TestCase
{
    use UseHttpFactories;

    #[Test]
    public function it_can_decode_response_to_resource_stream(): void
    {
        $decoder = ResourceStreamDecoder::createWithAutodiscoveredPsrFactories();
        $response = $this->createResponse()
            ->withBody($this->createStream($content = '{"hello": "world"}'));

        $decoded = $decoder($response);

        self::assertInstanceOf(ResourceStream::class, $decoded);
        self::assertSame($content, $decoded->getContents());
    }
}
