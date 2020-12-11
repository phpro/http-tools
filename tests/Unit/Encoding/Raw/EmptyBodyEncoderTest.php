<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\Raw;

use Phpro\HttpTools\Encoding\Raw\EmptyBodyEncoder;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\TestCase;

final class EmptyBodyEncoderTest extends TestCase
{
    use UseHttpFactories;

    /** @test */
    public function it_can_encode_raw_string(): void
    {
        $data = null;
        $encoder = EmptyBodyEncoder::createWithAutodiscoveredPsrFactories();
        $request = $this->createRequest('POST', '/hello');

        $actual = $encoder($request, $data);

        self::assertSame($request->getMethod(), $actual->getMethod());
        self::assertSame($request->getUri(), $actual->getUri());
        self::assertSame('', (string) $actual->getBody());
    }
}
