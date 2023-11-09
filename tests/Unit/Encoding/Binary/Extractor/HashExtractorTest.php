<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\Binary\Extractor;

use Phpro\HttpTools\Encoding\Binary\Extractor\HashExtractor;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\TestCase;
use Psl\Hash\Algorithm;

use function Psl\Hash\hash;

use Psr\Http\Message\ResponseInterface;

final class HashExtractorTest extends TestCase
{
    use UseHttpFactories;

    /**
     * @test
     *
     * @dataProvider provideCases
     */
    public function it_can_extract_hash(ResponseInterface $response, string $expected, int $endPosition = 0): void
    {
        $extractor = new HashExtractor(Algorithm::MD5);
        $actual = $extractor($response);

        self::assertSame($actual, $expected);
        self::assertSame($endPosition, $response->getBody()->tell());
    }

    public function provideCases()
    {
        yield 'from-empty-stream-size' => [
            $this->createResponse(),
            hash('', Algorithm::MD5),
        ];

        yield 'from-stream-size' => [
            $this->createResponse()
                ->withBody(
                    $this->createStream('12345')
                ),
            hash('12345', Algorithm::MD5),
        ];

        $stream = $this->createStream('12345');
        $stream->seek(3);
        yield 'from-partially-read-stream' => [
            $this->createResponse()
                ->withBody($stream),
            hash('12345', Algorithm::MD5),
            3,
        ];
    }
}
