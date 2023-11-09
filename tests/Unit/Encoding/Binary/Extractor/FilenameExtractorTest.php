<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\Binary\Extractor;

use Phpro\HttpTools\Encoding\Binary\Extractor\FilenameExtractor;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

final class FilenameExtractorTest extends TestCase
{
    use UseHttpFactories;

    /**
     * @test
     *
     * @dataProvider provideCases
     */
    public function it_can_extract_filenames(ResponseInterface $response, ?string $expected): void
    {
        $extractor = new FilenameExtractor();
        $actual = $extractor($response);

        self::assertSame($actual, $expected);
    }

    public function provideCases()
    {
        yield 'single-content-disposition' => [
            $this->createResponse()
                ->withHeader('Content-Disposition', 'inline; filename=hello.jpg'),
            'hello.jpg',
        ];
        yield 'multiple-content-disposition' => [
            $this->createResponse()
                ->withHeader('Content-Disposition', [
                    'inline; filename=hello.jpg',
                    'inline; filename=goodbye.jpg',
                ]),
            'hello.jpg',
        ];
        yield 'filename-ext-content-disposition' => [
            $this->createResponse()
                ->withHeader('Content-Disposition', 'attachment; filename="notthis.jpg"; filename*=UTF-8\'\'hello.jpg'),
            'hello.jpg',
        ];
        yield 'invalid-disposition' => [
            $this->createResponse()
                ->withHeader('Content-Disposition', 'qsdfqsdfqsdf'),
            null,
        ];
        yield 'none' => [
            $this->createResponse(),
            null,
        ];
    }
}
