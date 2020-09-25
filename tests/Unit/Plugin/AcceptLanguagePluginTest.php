<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Plugin;

use Http\Mock\Client;
use Phpro\HttpTools\Client\Configurator\PluginsConfigurator;
use Phpro\HttpTools\Plugin\AcceptLanguagePlugin;
use Phpro\HttpTools\Test\UseMockClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;

/**
 * @covers \Phpro\HttpTools\Plugin\AcceptLanguagePlugin
 *
 * @uses \Phpro\HttpTools\Test\UseMockClient
 * @uses \Phpro\HttpTools\Client\Configurator\PluginsConfigurator
 */
class AcceptLanguagePluginTest extends TestCase
{
    use UseMockClient;

    private Client $mockClient;
    private ClientInterface $client;

    protected function setUp(): void
    {
        $this->client = PluginsConfigurator::configure(
            $this->mockClient = $this->mockClient(function (Client $client): Client {
                $client->setDefaultResponse($this->createResponse(204));

                return $client;
            }),
            [new AcceptLanguagePlugin('nl-BE')]
        );
    }

    /** @test */
    public function it_can_set_the_accept_language(): void
    {
        $response = $this->client->sendRequest($this->createRequest('GET', '/something'));
        $lastRequest = $this->mockClient->getLastRequest();

        self::assertSame('nl-BE', $lastRequest->getHeaderLine('Accept-Language'));
        self::assertSame(204, $response->getStatusCode());
    }
}
