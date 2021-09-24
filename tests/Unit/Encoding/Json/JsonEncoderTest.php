<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\Json;

use Phpro\HttpTools\Encoding\Json\JsonEncoder;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\TestCase;
use Psl\Json;

final class JsonEncoderTest extends TestCase
{
    use UseHttpFactories;

    /** @test */
    public function it_can_encode_array_to_json(): void
    {
        $data = ['hello' => 'world'];
        $encoder = JsonEncoder::createWithAutodiscoveredPsrFactories();
        $request = $this->createRequest('POST', '/hello');

        $actual = $encoder($request, $data);

        self::assertSame($request->getMethod(), $actual->getMethod());
        self::assertSame($request->getUri(), $actual->getUri());
        self::assertSame(Json\encode(['hello' => 'world']), (string) $actual->getBody());
        self::assertSame(['application/json'], $actual->getHeader('Accept'));
        self::assertSame(['application/json'], $actual->getHeader('Content-Type'));
    }

    /** @test */
    public function it_can_encode_null_to_empty_body(): void
    {
        $data = null;
        $encoder = JsonEncoder::createWithAutodiscoveredPsrFactories();
        $request = $this->createRequest('POST', '/hello');

        $actual = $encoder($request, $data);

        self::assertSame($request->getMethod(), $actual->getMethod());
        self::assertSame($request->getUri(), $actual->getUri());
        self::assertSame('', (string) $actual->getBody());
        self::assertSame(['application/json'], $actual->getHeader('Accept'));
        self::assertSame(['application/json'], $actual->getHeader('Content-Type'));
    }
}
