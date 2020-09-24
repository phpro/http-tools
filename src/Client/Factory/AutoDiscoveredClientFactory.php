<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Client\Factory;

use Http\Client\Common\Plugin;
use Http\Discovery\Psr18ClientDiscovery;
use Phpro\HttpTools\Client\Configurator\PluginsConfigurator;
use Psr\Http\Client\ClientInterface;

final class AutoDiscoveredClientFactory implements FactoryInterface
{
    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     *
     * @param array<empty,empty> $options
     * @param list<Plugin> $middlewares
     */
    public static function create(iterable $middlewares, array $options = []): ClientInterface
    {
        return PluginsConfigurator::configure(Psr18ClientDiscovery::find(), $middlewares);
    }
}
