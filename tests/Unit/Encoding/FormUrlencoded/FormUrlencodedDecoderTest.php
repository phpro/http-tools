<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\FormUrlencoded;

use Phpro\HttpTools\Encoding\FormUrlencoded\FormUrlencodedDecoder;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\TestCase;

final class FormUrlencodedDecoderTest extends TestCase
{
    use UseHttpFactories;

    /** @test */
    public function it_can_decode_form_url_encoded_to_array(): void
    {
        $decoder = FormUrlencodedDecoder::createWithAutodiscoveredPsrFactories();
        $response = $this->createResponse()
            ->withBody($this->createStream('hello=world&foo=bar'));
        $decoded = $decoder($response);

        self::assertSame(['hello' => 'world', 'foo' => 'bar'], $decoded);
    }

    /** @test */
    public function it_can_decode_empty_body_to_empty_array(): void
    {
        $decoder = FormUrlencodedDecoder::createWithAutodiscoveredPsrFactories();
        $response = $this->createResponse()->withBody($this->createStream(''));
        $decoded = $decoder($response);

        self::assertSame([], $decoded);
    }
}
