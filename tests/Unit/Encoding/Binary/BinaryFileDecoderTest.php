<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\Binary;

use Phpro\HttpTools\Encoding\Binary\BinaryFile;
use Phpro\HttpTools\Encoding\Binary\BinaryFileDecoder;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

final class BinaryFileDecoderTest extends TestCase
{
    use UseHttpFactories;

    /**
     * @test
     *
     * @dataProvider provideCases
     */
    public function it_can_decode_binary_files(
        BinaryFileDecoder $decoder,
        ResponseInterface $response,
        BinaryFile $expected
    ): void {
        $actual = $decoder($response);

        self::assertSame($actual->stream(), $expected->stream());
        self::assertSame($actual->fileSizeInBytes(), $expected->fileSizeInBytes());
        self::assertSame($actual->mimeType(), $expected->mimeType());
        self::assertSame($actual->fileName(), $expected->fileName());
        self::assertSame($actual->extension(), $expected->extension());
    }

    public function provideCases()
    {
        $defaultDecoder = BinaryFileDecoder::createWithAutodiscoveredPsrFactories();

        yield 'from-empty-response' => [
            $defaultDecoder,
            $response = $this->createResponse(),
            new BinaryFile(
                $response->getBody(), 0, null, null, null, md5('')
            ),
        ];
        yield 'from-full-response' => [
            $defaultDecoder,
            $response = $this->createResponse()
                ->withBody($this->createStream('12345'))
                ->withHeader('Content-Type', 'image/jpeg')
                ->withHeader('Content-Disposition', 'inline; filename="hello.jpg"'),
            new BinaryFile(
                $response->getBody(), 5, 'image/jpeg', 'hello.jpg', 'jpg', md5('12345')
            ),
        ];
        yield 'from-configurable-extractors' => [
            new BinaryFileDecoder(
                fn () => 5,
                fn () => 'image/jpeg',
                fn () => 'hello.jpg',
                fn () => 'jpg',
                fn () => 'md5',
            ),
            $response = $this->createResponse(),
            new BinaryFile(
                $response->getBody(), 5, 'image/jpeg', 'hello.jpg', 'jpg', 'md5'
            ),
        ];
    }
}
