<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Transport\Presets;

use Phpro\HttpTools\Encoding\Json\JsonDecoder;
use Phpro\HttpTools\Test\UseHttpToolsFactories;
use Phpro\HttpTools\Test\UseMockClient;
use Phpro\HttpTools\Transport\Presets\FormUrlencodedPreset;
use Phpro\HttpTools\Uri\RawUriBuilder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psl\Json;

final class FormUrlencodedPresetTest extends TestCase
{
    use UseHttpToolsFactories;
    use UseMockClient;

    /** @test */
    public function it_can_create_transport(): void
    {
        $transport = FormUrlencodedPreset::create(
            $client = $this->mockClient(),
            RawUriBuilder::createWithAutodiscoveredPsrFactories()
        );

        $request = $this->createToolsRequest('GET', '/api', [], $expectedRequest = ['hello' => 'world']);

        $client->addResponse(
            $this->createResponse(200)
                ->withBody($this->createStream(
                    http_build_query($expectedResponse = ['foo' => 'bar']))
                )
        );

        $actualResponse = $transport($request);
        $lastRequest = $client->getLastRequest();

        self::assertSame($actualResponse, $expectedResponse);
        self::assertSame(http_build_query($expectedRequest), (string) $lastRequest->getBody());
    }

    #[Test]
    public function it_is_possible_to_override_specific_decoder(): void
    {
        $transport = FormUrlencodedPreset::create(
            $client = $this->mockClient(),
            RawUriBuilder::createWithAutodiscoveredPsrFactories(),
            JsonDecoder::createWithAutodiscoveredPsrFactories(),
        );

        $request = $this->createToolsRequest('GET', '/api', [], $expectedRequest = ['hello' => 'world']);

        $client->addResponse(
            $this->createResponse(200)
                ->withBody($this->createStream(
                    Json\encode($expectedResponse = ['foo' => 'bar']))
                )
        );

        $actualResponse = $transport($request);
        $lastRequest = $client->getLastRequest();

        self::assertSame($actualResponse, $expectedResponse);
        self::assertSame(http_build_query($expectedRequest), (string) $lastRequest->getBody());
    }
}
