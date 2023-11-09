<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\Binary;

use Psr\Http\Message\StreamInterface;

final class BinaryFile
{
    public function __construct(
        private readonly StreamInterface $stream,
        private readonly ?int $fileSizeInBytes,
        private readonly ?string $mimeType,
        private readonly ?string $fileName,
        private readonly ?string $extension,
        private readonly ?string $hash
    ) {
    }

    public function stream(): StreamInterface
    {
        return $this->stream;
    }

    public function fileSizeInBytes(): ?int
    {
        return $this->fileSizeInBytes;
    }

    public function mimeType(): ?string
    {
        return $this->mimeType;
    }

    public function fileName(): ?string
    {
        return $this->fileName;
    }

    public function extension(): ?string
    {
        return $this->extension;
    }

    public function hash(): ?string
    {
        return $this->hash;
    }
}
