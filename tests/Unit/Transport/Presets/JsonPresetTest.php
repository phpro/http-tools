<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Transport\Presets;

use Phpro\HttpTools\Test\UseHttpToolsFactories;
use Phpro\HttpTools\Test\UseMockClient;
use Phpro\HttpTools\Transport\Presets\JsonPreset;
use Phpro\HttpTools\Uri\RawUriBuilder;
use PHPUnit\Framework\TestCase;

use function Amp\Promise\wait;
use function Safe\json_encode;

/**
 * @covers \Phpro\HttpTools\Transport\Presets\JsonPreset
 * @covers \Phpro\HttpTools\Encoding\Json\JsonEncoder
 * @covers \Phpro\HttpTools\Encoding\Json\JsonDecoder
 * @covers \Phpro\HttpTools\Transport\EncodedTransport
 * @covers \Phpro\HttpTools\Transport\AsyncEncodedTransport
 * @covers \Phpro\HttpTools\Uri\RawUriBuilder
 * @uses \Phpro\HttpTools\Test\UseHttpToolsFactories
 * @uses \Phpro\HttpTools\Test\UseMockClient
 * @uses \Phpro\HttpTools\Test\UseHttpFactories
 * @uses \Phpro\HttpTools\Async\HttplugPromiseAdapter
 */
final class JsonPresetTest extends TestCase
{
    use UseMockClient;
    use UseHttpToolsFactories;

    /** @test */
    public function it_can_create_sync_transport(): void
    {
        $transport = JsonPreset::sync(
            $client = $this->mockClient(),
            RawUriBuilder::createWithAutodiscoveredPsrFactories()
        );

        $request = $this->createToolsRequest('GET', '/api', [], $expectedRequest = ['Hello']);

        $client->addResponse(
            $this->createResponse(200)
                 ->withBody($this->createStream(
                     json_encode($expectedResponse = ['world']))
                 )
        );

        $actualResponse = $transport($request);
        $lastRequest = $client->getLastRequest();

        self::assertSame($actualResponse, $expectedResponse);
        self::assertSame(json_encode($expectedRequest), (string) $lastRequest->getBody());
    }

    /** @test */
    public function it_can_create_async_transport(): void
    {
        $transport = JsonPreset::async(
            $client = $this->mockClient(),
            RawUriBuilder::createWithAutodiscoveredPsrFactories()
        );

        $request = $this->createToolsRequest('GET', '/api', [], $expectedRequest = ['Hello']);

        $client->addResponse(
            $this->createResponse(200)
                 ->withBody($this->createStream(
                     json_encode($expectedResponse = ['world']))
                 )
        );

        $actualResponse = wait($transport($request));
        $lastRequest = $client->getLastRequest();

        self::assertSame($actualResponse, $expectedResponse);
        self::assertSame(json_encode($expectedRequest), (string) $lastRequest->getBody());
    }
}
