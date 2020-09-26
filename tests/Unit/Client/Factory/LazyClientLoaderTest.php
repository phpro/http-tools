<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Client\Factory;

use InvalidArgumentException;
use Phpro\HttpTools\Client\Factory\AutoDiscoveredClientFactory;
use Phpro\HttpTools\Client\Factory\FactoryInterface;
use Phpro\HttpTools\Client\Factory\LazyClientLoader;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Phpro\HttpTools\Client\Factory\LazyClientLoader
 *
 * @uses \Phpro\HttpTools\Client\Configurator\PluginsConfigurator
 * @uses \Phpro\HttpTools\Client\Factory\AutoDiscoveredClientFactory
 */
class LazyClientLoaderTest extends TestCase
{
    /** @test */
    public function it_can_not_load_invalid_class(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a sub-class of "'.FactoryInterface::class.'"');

        $loader = new LazyClientLoader(stdClass::class, [], []);
        $loader->load();
    }

    /** @test */
    public function it_only_loads_the_client_once(): void
    {
        $loader = new LazyClientLoader(AutoDiscoveredClientFactory::class, [], []);
        $client1 = $loader->load();
        $client2 = $loader->load();

        self::assertSame($client1, $client2);
    }
}
