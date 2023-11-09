<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\Binary\Extractor;

use Phpro\HttpTools\Encoding\Binary\Extractor\ExtensionExtractor;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

final class ExtensionExtractorTest extends TestCase
{
    use UseHttpFactories;

    /**
     * @test
     *
     * @dataProvider provideCases
     */
    public function it_can_extract_extension(ResponseInterface $response, ?string $expected): void
    {
        $extractor = new ExtensionExtractor();
        $actual = $extractor($response);

        self::assertSame($actual, $expected);
    }

    public function provideCases()
    {
        yield 'from-valid-content-type' => [
            $this->createResponse()
                ->withHeader('Content-Type', 'image/jpeg'),
            'jpg',
        ];
        yield 'from-invalid-content-type' => [
            $this->createResponse()
                ->withHeader('Content-Type', ['unknown/unkown']),
            null,
        ];
        yield 'filename-with-extension' => [
            $this->createResponse()
                ->withHeader('Content-Disposition', 'attachment; filename="hello.jpg"'),
            'jpg',
        ];
        yield 'filename-without-extension-mime-type' => [
            $this->createResponse()
                ->withHeader('Content-Disposition', 'attachment; filename="hello"'),
            null,
        ];
        yield 'none' => [
            $this->createResponse(),
            null,
        ];
    }
}
