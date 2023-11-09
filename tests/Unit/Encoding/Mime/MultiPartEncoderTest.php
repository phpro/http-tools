<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\Mime;

use Phpro\HttpTools\Encoding\Mime\MultiPartEncoder;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

final class MultiPartEncoderTest extends TestCase
{
    use UseHttpFactories;

    /** @test */
    public function it_can_encode_multi_part(): void
    {
        $data = new FormDataPart([
            'name' => 'Jos Bos',
            'profilePic' => DataPart::fromPath(__FILE__, 'rce-pic.png'),
        ]);

        $encoder = MultiPartEncoder::createWithAutodiscoveredPsrFactories();
        $request = $this->createRequest('POST', '/hello');

        $actual = $encoder($request, $data);

        $expectedContentType = $data->getPreparedHeaders()->get('Content-Type')->toString();

        self::assertSame($request->getMethod(), $actual->getMethod());
        self::assertSame($request->getUri(), $actual->getUri());
        self::assertSame($data->bodyToString(), (string) $actual->getBody());
        self::assertSame(
            [$expectedContentType],
            $actual->getHeader('Content-Type')
        );
        self::assertStringContainsString('boundary', $expectedContentType);
    }
}
