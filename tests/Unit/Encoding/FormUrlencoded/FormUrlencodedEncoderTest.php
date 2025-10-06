<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\FormUrlencoded;

use Phpro\HttpTools\Encoding\FormUrlencoded\FormUrlencodedEncoder;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\TestCase;

final class FormUrlencodedEncoderTest extends TestCase
{
    use UseHttpFactories;

    /** @test */
    public function it_can_encode_array_to_url_encoded(): void
    {
        $data = ['hello' => 'world'];
        $encoder = FormUrlencodedEncoder::createWithAutodiscoveredPsrFactories();
        $request = $this->createRequest('POST', '/hello');

        $actual = $encoder($request, $data);

        self::assertSame($request->getMethod(), $actual->getMethod());
        self::assertSame($request->getUri(), $actual->getUri());
        self::assertSame('hello=world', (string) $actual->getBody());
        self::assertSame(['application/x-www-form-urlencoded'], $actual->getHeader('Content-Type'));
    }

    /** @test */
    public function it_can_encode_null_to_empty_body(): void
    {
        $data = null;
        $encoder = FormUrlencodedEncoder::createWithAutodiscoveredPsrFactories();
        $request = $this->createRequest('POST', '/hello');

        $actual = $encoder($request, $data);

        self::assertSame($request->getMethod(), $actual->getMethod());
        self::assertSame($request->getUri(), $actual->getUri());
        self::assertSame('', (string) $actual->getBody());
        self::assertSame(['application/x-www-form-urlencoded'], $actual->getHeader('Content-Type'));
    }
}
