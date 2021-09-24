<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Plugin;

use Http\Mock\Client;
use Http\Promise\Promise;
use Phpro\HttpTools\Client\Configurator\PluginsConfigurator;
use Phpro\HttpTools\Plugin\CallbackPlugin;
use Phpro\HttpTools\Test\UseMockClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

final class CallbackPluginTest extends TestCase
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
            [
                new CallbackPlugin(
                    static function (RequestInterface $request, callable $next, callable $first): Promise {
                        return $next($request->withAddedHeader('Hello', 'World'));
                    }
                ),
            ]
        );
    }

    /** @test */
    public function it_can_run_a_callback_as_plugin(): void
    {
        $response = $this->client->sendRequest($this->createRequest('GET', '/something'));
        $lastRequest = $this->mockClient->getLastRequest();

        self::assertSame('World', $lastRequest->getHeaderLine('Hello'));
        self::assertSame(204, $response->getStatusCode());
    }
}
