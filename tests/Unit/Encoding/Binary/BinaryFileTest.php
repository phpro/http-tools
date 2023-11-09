<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\Binary;

use Phpro\HttpTools\Encoding\Binary\BinaryFile;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\TestCase;

final class BinaryFileTest extends TestCase
{
    use UseHttpFactories;

    /** @test */
    public function it_is_a_dto(): void
    {
        $dto = new BinaryFile(
            $stream = $this->createStream(''),
            $fileInBytes = 5,
            $mimeType = 'mime',
            $fileName = 'hello.jpg',
            $extension = 'jpg',
            $hash = 'md5'
        );

        self::assertSame($stream, $dto->stream());
        self::assertSame($fileInBytes, $dto->fileSizeInBytes());
        self::assertSame($mimeType, $dto->mimeType());
        self::assertSame($fileName, $dto->fileName());
        self::assertSame($extension, $dto->extension());
        self::assertSame($hash, $dto->hash());
    }
}
