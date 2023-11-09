<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\Binary\Extractor;

use Phpro\HttpTools\Encoding\Binary\Extractor\MimeTypeExtractor;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

final class MimeTypeExtractorTest extends TestCase
{
    use UseHttpFactories;

    /**
     * @test
     *
     * @dataProvider provideCases
     */
    public function it_can_extract_mime_type(ResponseInterface $response, ?string $expected): void
    {
        $extractor = new MimeTypeExtractor();
        $actual = $extractor($response);

        self::assertSame($actual, $expected);
    }

    public function provideCases()
    {
        yield 'single-content-type' => [
            $this->createResponse()
                ->withHeader('Content-Type', 'image/jpeg'),
            'image/jpeg',
        ];
        yield 'multiple-content-type' => [
            $this->createResponse()
                ->withHeader('Content-Type', [
                    'image/jpeg',
                    'image/png',
                ]),
            'image/jpeg',
        ];
        yield 'filename-with-extension-mime-type' => [
            $this->createResponse()
                ->withHeader('Content-Disposition', 'attachment; filename="hello.jpg"'),
            'image/jpeg',
        ];
        yield 'filename-without-extension-mime-type' => [
            $this->createResponse()
                ->withHeader('Content-Disposition', 'attachment; filename="hello"'),
            null,
        ];
        yield 'filename-with-unknown-extension-mime-type' => [
            $this->createResponse()
                ->withHeader('Content-Disposition', 'attachment; filename="hello.thisextensiondoesnotexist";'),
            null,
        ];
        yield 'none' => [
            $this->createResponse(),
            null,
        ];
    }
}
