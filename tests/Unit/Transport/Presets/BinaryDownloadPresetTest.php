<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Transport\Presets;

use Phpro\HttpTools\Encoding\Binary\BinaryFile;
use Phpro\HttpTools\Test\UseHttpToolsFactories;
use Phpro\HttpTools\Test\UseMockClient;
use Phpro\HttpTools\Transport\Presets\BinaryDownloadPreset;
use Phpro\HttpTools\Uri\RawUriBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

final class BinaryDownloadPresetTest extends TestCase
{
    use UseHttpToolsFactories;
    use UseMockClient;

    /** @test */
    public function it_can_create_a_default_transport(): void
    {
        $transport = BinaryDownloadPreset::create(
            $client = $this->mockClient(),
            RawUriBuilder::createWithAutodiscoveredPsrFactories()
        );

        $request = $this->createToolsRequest('GET', '/api', [], $expectedRequest = '');

        $client->addResponse(
            $this->createResponse()
                ->withHeader('Content-Type', $mimeType = 'image/jpeg')
                ->withHeader('Content-Disposition', 'inline; filename="hello.jpg"')
                ->withBody($stream = $this->createStream($content = 'world'))
        );

        $actualResponse = $transport($request);
        $lastRequest = $client->getLastRequest();

        self::assertSame($expectedRequest, (string) $lastRequest->getBody());

        self::assertInstanceOf(BinaryFile::class, $actualResponse);
        self::assertSame($stream, $actualResponse->stream());
        self::assertSame($mimeType, $actualResponse->mimeType());
        self::assertSame('hello.jpg', $actualResponse->fileName());
        self::assertSame('jpg', $actualResponse->extension());
        self::assertSame(md5($content), $actualResponse->hash());
    }

    /** @test */
    public function it_can_create_from_from_data_transport(): void
    {
        $transport = BinaryDownloadPreset::fromFormData(
            $client = $this->mockClient(),
            RawUriBuilder::createWithAutodiscoveredPsrFactories()
        );

        $request = $this->createToolsRequest('GET', '/api', [], new FormDataPart(['name' => 'Jos Bos']));

        $client->addResponse(
            $this->createResponse()
                ->withHeader('Content-Type', $mimeType = 'application/pdf')
                ->withHeader('Content-Disposition', 'attachment; filename="profile-export.pdf"')
                ->withBody($stream = $this->createStream($content = 'export-body'))
        );

        $actualResponse = $transport($request);
        $lastRequest = $client->getLastRequest();

        self::assertStringContainsString('Jos Bos', (string) $lastRequest->getBody());

        self::assertInstanceOf(BinaryFile::class, $actualResponse);
        self::assertSame($stream, $actualResponse->stream());
        self::assertSame($mimeType, $actualResponse->mimeType());
        self::assertSame('profile-export.pdf', $actualResponse->fileName());
        self::assertSame('pdf', $actualResponse->extension());
        self::assertSame(md5($content), $actualResponse->hash());
    }
}
