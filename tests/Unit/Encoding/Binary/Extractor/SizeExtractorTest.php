<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\Binary\Extractor;

use Phpro\HttpTools\Encoding\Binary\Extractor\SizeExtractor;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class SizeExtractorTest extends TestCase
{
    use UseHttpFactories;

    /**
     * @test
     *
     * @param callable<ResponseInterface> $response
     *
     * @dataProvider provideCases
     */
    public function it_can_extract_size(callable $response, ?int $expected): void
    {
        $extractor = new SizeExtractor();
        $actual = $extractor($response($this));

        self::assertSame($actual, $expected);
    }

    public static function provideCases()
    {
        yield 'from-empty-stream-size' => [
            static fn (self $testCase) => self::createResponse(),
            0,
        ];

        yield 'from-stream-size' => [
            static fn (self $testCase) => self::createResponse()
                ->withBody(self::createStream('12345')),
            5,
        ];

        yield 'from-single-content-length' => [
            static fn (self $testCase) => self::createResponse()
                ->withBody($testCase->notSizeableStreamMock())
                ->withHeader('Content-Length', '500'),
            500,
        ];
        yield 'from-multiple-content-length' => [
            static fn (self $testCase) => self::createResponse()
                ->withBody($testCase->notSizeableStreamMock())
                ->withHeader('Content-Length', [
                    '500',
                    '600',
                ]),
            500,
        ];
        yield 'from-invalid-content-length' => [
            static fn (self $testCase) => self::createResponse()
                ->withBody($testCase->notSizeableStreamMock())
                ->withHeader('Content-Length', 'thisisnotanint'),
            null,
        ];
        yield 'from-no-info-whatsoever' => [
            static fn (self $testCase) => self::createResponse()
                ->withBody($testCase->notSizeableStreamMock()),
            null,
        ];
    }

    private function notSizeableStreamMock(): MockObject
    {
        $notSizeableStream = $this->createMock(StreamInterface::class);
        $notSizeableStream->method('getSize')->willReturn(null);

        return $notSizeableStream;
    }
}
