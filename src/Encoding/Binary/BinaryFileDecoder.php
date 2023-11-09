<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\Binary;

use Phpro\HttpTools\Encoding\DecoderInterface;
use Psl\Hash\Algorithm;
use Psr\Http\Message\ResponseInterface;

/**
 * @implements DecoderInterface<BinaryFile>
 */
final class BinaryFileDecoder implements DecoderInterface
{
    /**
     * @var callable(ResponseInterface): ?int
     */
    private $sizeExtractor;

    /**
     * @var callable(ResponseInterface): ?string
     */
    private $mimeTypeExtractor;

    /**
     * @var callable(ResponseInterface): ?string
     */
    private $fileNameExtractor;

    /**
     * @var callable(ResponseInterface): ?string
     */
    private $extensionExtractor;

    /**
     * @var callable(ResponseInterface): ?string
     */
    private $hashExtractor;

    /**
     * @param callable(ResponseInterface): ?int $sizeExtractor
     * @param callable(ResponseInterface): ?string $mimeTypeExtractor
     * @param callable(ResponseInterface): ?string $fileNameExtractor
     * @param callable(ResponseInterface): ?string $extensionExtractor
     * @param callable(ResponseInterface): ?string $hashExtractor
     */
    public function __construct(
        callable $sizeExtractor,
        callable $mimeTypeExtractor,
        callable $fileNameExtractor,
        callable $extensionExtractor,
        callable $hashExtractor
    ) {
        $this->sizeExtractor = $sizeExtractor;
        $this->mimeTypeExtractor = $mimeTypeExtractor;
        $this->fileNameExtractor = $fileNameExtractor;
        $this->extensionExtractor = $extensionExtractor;
        $this->hashExtractor = $hashExtractor;
    }

    public static function createWithAutodiscoveredPsrFactories(): self
    {
        return new self(
            new Extractor\SizeExtractor(),
            new Extractor\MimeTypeExtractor(),
            new Extractor\FilenameExtractor(),
            new Extractor\ExtensionExtractor(),
            new Extractor\HashExtractor(Algorithm::MD5),
        );
    }

    public function __invoke(ResponseInterface $response): BinaryFile
    {
        return new BinaryFile(
            $response->getBody(),
            ($this->sizeExtractor)($response),
            ($this->mimeTypeExtractor)($response),
            ($this->fileNameExtractor)($response),
            ($this->extensionExtractor)($response),
            ($this->hashExtractor)($response),
        );
    }
}
