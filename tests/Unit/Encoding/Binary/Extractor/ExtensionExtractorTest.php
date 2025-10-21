<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\Binary\Extractor;

use Phpro\HttpTools\Encoding\Binary\Extractor\ExtensionExtractor;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

final class ExtensionExtractorTest extends TestCase
{
    use UseHttpFactories;

    #[DataProvider('provideCases')]
    #[Test]
    public function it_can_extract_extension(ResponseInterface $response, ?string $expected): void
    {
        $extractor = new ExtensionExtractor();
        $actual = $extractor($response);

        self::assertSame($actual, $expected);
    }

    public static function provideCases(): iterable
    {
        yield 'from-valid-content-type' => [
            self::createResponse()
                ->withHeader('Content-Type', 'image/jpeg'),
            'jpg',
        ];
        yield 'from-invalid-content-type' => [
            self::createResponse()
                ->withHeader('Content-Type', ['unknown/unkown']),
            null,
        ];
        yield 'filename-with-extension' => [
            self::createResponse()
                ->withHeader('Content-Disposition', 'attachment; filename="hello.jpg"'),
            'jpg',
        ];
        yield 'filename-without-extension-mime-type' => [
            self::createResponse()
                ->withHeader('Content-Disposition', 'attachment; filename="hello"'),
            null,
        ];
        yield 'none' => [
            self::createResponse(),
            null,
        ];
    }
}
