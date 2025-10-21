<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\Binary\Extractor;

use Phpro\HttpTools\Encoding\Binary\Extractor\HashExtractor;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psl\Hash\Algorithm;

use function Psl\Hash\hash;

use Psr\Http\Message\ResponseInterface;

final class HashExtractorTest extends TestCase
{
    use UseHttpFactories;

    #[DataProvider('provideCases')]
    #[Test]
    public function it_can_extract_hash(ResponseInterface $response, string $expected, int $endPosition = 0): void
    {
        $extractor = new HashExtractor(Algorithm::Md5);
        $actual = $extractor($response);

        self::assertSame($actual, $expected);
        self::assertSame($endPosition, $response->getBody()->tell());
    }

    public static function provideCases(): iterable
    {
        yield 'from-empty-stream-size' => [
            self::createResponse(),
            hash('', Algorithm::Md5),
        ];

        yield 'from-stream-size' => [
            self::createResponse()
                ->withBody(
                    self::createStream('12345')
                ),
            hash('12345', Algorithm::Md5),
        ];

        $stream = self::createStream('12345');
        $stream->seek(3);
        yield 'from-partially-read-stream' => [
            self::createResponse()
                ->withBody($stream),
            hash('12345', Algorithm::Md5),
            3,
        ];
    }
}
