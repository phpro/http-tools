<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Transport\Presets;

use Phpro\HttpTools\Test\UseHttpToolsFactories;
use Phpro\HttpTools\Test\UseMockClient;
use Phpro\HttpTools\Transport\Presets\JsonPreset;
use Phpro\HttpTools\Uri\RawUriBuilder;
use PHPUnit\Framework\TestCase;
use Psl\Json;

final class JsonPresetTest extends TestCase
{
    use UseHttpToolsFactories;
    use UseMockClient;

    /** @test */
    public function it_can_create_transport(): void
    {
        $transport = JsonPreset::create(
            $client = $this->mockClient(),
            RawUriBuilder::createWithAutodiscoveredPsrFactories()
        );

        $request = $this->createToolsRequest('GET', '/api', [], $expectedRequest = ['Hello']);

        $client->addResponse(
            $this->createResponse(200)
                ->withBody($this->createStream(
                    Json\encode($expectedResponse = ['world']))
                )
        );

        $actualResponse = $transport($request);
        $lastRequest = $client->getLastRequest();

        self::assertSame($actualResponse, $expectedResponse);
        self::assertSame(Json\encode($expectedRequest), (string) $lastRequest->getBody());
    }
}
