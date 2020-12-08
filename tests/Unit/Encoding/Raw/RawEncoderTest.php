<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\Raw;

use Phpro\HttpTools\Encoding\Raw\RawEncoder;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Phpro\HttpTools\Encoding\Raw\RawEncoder
 * @uses \Phpro\HttpTools\Test\UseHttpFactories
 */
final class RawEncoderTest extends TestCase
{
    use UseHttpFactories;

    /** @test */
    public function it_can_encode_raw_string(): void
    {
        $data = 'Hello world';
        $encoder = RawEncoder::createWithAutodiscoveredPsrFactories();
        $request = $this->createRequest('POST', '/hello');

        $actual = $encoder($request, $data);

        self::assertSame($request->getMethod(), $actual->getMethod());
        self::assertSame($request->getUri(), $actual->getUri());
        self::assertSame($data, (string) $actual->getBody());
    }
}
