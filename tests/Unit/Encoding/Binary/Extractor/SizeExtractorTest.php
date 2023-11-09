<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\Binary\Extractor;

use Phpro\HttpTools\Encoding\Binary\Extractor\SizeExtractor;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class SizeExtractorTest extends TestCase
{
    use UseHttpFactories;

    /**
     * @test
     *
     * @dataProvider provideCases
     */
    public function it_can_extract_size(ResponseInterface $response, ?int $expected): void
    {
        $extractor = new SizeExtractor();
        $actual = $extractor($response);

        self::assertSame($actual, $expected);
    }

    public function provideCases()
    {
        $notSizeableStream = $this->createMock(StreamInterface::class);
        $notSizeableStream->method('getSize')->willReturn(null);

        yield 'from-empty-stream-size' => [
            $this->createResponse(),
            0,
        ];

        yield 'from-stream-size' => [
            $this->createResponse()
                ->withBody($this->createStream('12345')),
            5,
        ];

        yield 'from-single-content-length' => [
            $this->createResponse()
                ->withBody($notSizeableStream)
                ->withHeader('Content-Length', '500'),
            500,
        ];
        yield 'from-multiple-content-length' => [
            $this->createResponse()
                ->withBody($notSizeableStream)
                ->withHeader('Content-Length', [
                    '500',
                    '600',
                ]),
            500,
        ];
        yield 'from-invalid-content-length' => [
            $this->createResponse()
                ->withBody($notSizeableStream)
                ->withHeader('Content-Length', 'thisisnotanint'),
            null,
        ];
        yield 'from-no-info-whatsoever' => [
            $this->createResponse()
                ->withBody($notSizeableStream),
            null,
        ];
    }
}
