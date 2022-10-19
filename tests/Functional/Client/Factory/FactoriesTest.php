<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Functional\Client\Factory;

use Http\Client\Plugin\Vcr\NamingStrategy\PathNamingStrategy;
use Phpro\HttpTools\Client\Configurator\PluginsConfigurator;
use Phpro\HttpTools\Client\Factory\AutoDiscoveredClientFactory;
use Phpro\HttpTools\Client\Factory\GuzzleClientFactory;
use Phpro\HttpTools\Client\Factory\LazyClientLoader;
use Phpro\HttpTools\Client\Factory\SymfonyClientFactory;
use Phpro\HttpTools\Test\UseVcrClient;
use Phpro\HttpTools\Tests\Helper\Vcr\FactoryAwareNamingStrategy;
use PHPUnit\Framework\TestCase;
use Psl\Json;
use Psr\Http\Message\RequestInterface;

final class FactoriesTest extends TestCase
{
    use UseVcrClient;

    /**
     * @test
     *
     * @dataProvider provideFactories
     */
    public function it_can_use_http_factories(string $factoryName, callable $factory): void
    {
        $client = PluginsConfigurator::configure($factory(), [
            ...$this->useRecording(
                FIXTURES_DIR.'/functional/client-factory',
                new FactoryAwareNamingStrategy($factoryName, new PathNamingStrategy())
            ),
        ]);

        $response = $client->sendRequest(
            $this->createRequest('GET', 'http://127.0.0.1:8000/success.json')
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(['success' => true], Json\decode($response->getBody()->__toString(), true));
    }

    public function provideFactories()
    {
        yield 'autodiscover' => [
            'AutoDiscoveredClientFactory',
            fn () => AutoDiscoveredClientFactory::create([]),
        ];
        yield 'guzzle' => [
            'GuzzleClientFactory',
            fn () => GuzzleClientFactory::create([
                fn (callable $handler) => fn (RequestInterface $request, array $options) => $handler($request, $options),
            ]),
        ];
        yield 'httplug' => [
            'SymfonyClientFactory',
            fn () => SymfonyClientFactory::create([]),
        ];
        yield 'lazy' => [
            'LazyClientLoader',
            fn () => (new LazyClientLoader(AutoDiscoveredClientFactory::class, [], []))->load(),
        ];
    }
}
