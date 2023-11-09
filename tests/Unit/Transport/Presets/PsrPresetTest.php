<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Transport\Presets;

use Phpro\HttpTools\Test\UseHttpToolsFactories;
use Phpro\HttpTools\Test\UseMockClient;
use Phpro\HttpTools\Transport\Presets\PsrPreset;
use Phpro\HttpTools\Uri\RawUriBuilder;
use PHPUnit\Framework\TestCase;

final class PsrPresetTest extends TestCase
{
    use UseHttpToolsFactories;
    use UseMockClient;

    /** @test */
    public function it_can_create_sync_transport(): void
    {
        $transport = PsrPreset::create(
            $client = $this->mockClient(),
            RawUriBuilder::createWithAutodiscoveredPsrFactories()
        );

        $request = $this->createToolsRequest('GET', '/api', [], $expectedRequest = 'Hello');

        $client->addResponse(
            $expectedResponse = $this->createResponse(200)
                ->withBody($this->createStream('world'))
        );

        $actualResponse = $transport($request);
        $lastRequest = $client->getLastRequest();

        self::assertSame($actualResponse, $expectedResponse);
        self::assertSame($expectedRequest, (string) $lastRequest->getBody());
    }
}
