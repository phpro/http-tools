<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Transport\Presets;

use Phpro\HttpTools\Test\UseHttpToolsFactories;
use Phpro\HttpTools\Test\UseMockClient;
use Phpro\HttpTools\Transport\Presets\RawPreset;
use Phpro\HttpTools\Uri\RawUriBuilder;
use PHPUnit\Framework\TestCase;

use function Amp\Promise\wait;

/**
 * @covers \Phpro\HttpTools\Transport\Presets\RawPreset
 * @covers \Phpro\HttpTools\Encoding\Raw\RawEncoder
 * @covers \Phpro\HttpTools\Encoding\Raw\RawDecoder
 * @covers \Phpro\HttpTools\Transport\EncodedTransport
 * @covers \Phpro\HttpTools\Transport\AsyncEncodedTransport
 * @covers \Phpro\HttpTools\Uri\RawUriBuilder
 * @uses \Phpro\HttpTools\Test\UseHttpToolsFactories
 * @uses \Phpro\HttpTools\Test\UseMockClient
 * @uses \Phpro\HttpTools\Test\UseHttpFactories
 * @uses \Phpro\HttpTools\Async\HttplugPromiseAdapter
 */
final class RawPresetTest extends TestCase
{
    use UseMockClient;
    use UseHttpToolsFactories;

    /** @test */
    public function it_can_create_sync_transport(): void
    {
        $transport = RawPreset::sync(
            $client = $this->mockClient(),
            RawUriBuilder::createWithAutodiscoveredPsrFactories()
        );

        $request = $this->createToolsRequest('GET', '/api', [], $expectedRequest = 'Hello');

        $client->addResponse(
            $this->createResponse(200)
                 ->withBody($this->createStream(
                     $expectedResponse = 'world'
                 )
             )
        );

        $actualResponse = $transport($request);
        $lastRequest = $client->getLastRequest();

        self::assertSame($actualResponse, $expectedResponse);
        self::assertSame($expectedRequest, (string) $lastRequest->getBody());
    }

    /** @test */
    public function it_can_create_async_transport(): void
    {
        $transport = RawPreset::async(
            $client = $this->mockClient(),
            RawUriBuilder::createWithAutodiscoveredPsrFactories()
        );

        $request = $this->createToolsRequest('GET', '/api', [], $expectedRequest = 'Hello');

        $client->addResponse(
            $this->createResponse(200)
                 ->withBody($this->createStream(
                     $expectedResponse = 'world'
                 )
            )
        );

        $actualResponse = wait($transport($request));
        $lastRequest = $client->getLastRequest();

        self::assertSame($actualResponse, $expectedResponse);
        self::assertSame($expectedRequest, (string) $lastRequest->getBody());
    }
}
