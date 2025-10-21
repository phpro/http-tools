<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\Psr7;

use Phpro\HttpTools\Encoding\Psr7\ResponseDecoder;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResponseDecoderTest extends TestCase
{
    use UseHttpFactories;

    #[Test]
    public function it_can_decode_response(): void
    {
        $decoder = ResponseDecoder::createWithAutodiscoveredPsrFactories();
        $response = $this->createResponse();
        $decoded = $decoder($response);

        self::assertSame($response, $decoded);
    }
}
