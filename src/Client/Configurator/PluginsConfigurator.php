<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Client\Configurator;

use Http\Client\Common\Plugin;
use Http\Client\Common\PluginClient;

use function is_array;
use function iterator_to_array;

use Psr\Http\Client\ClientInterface;
use Webmozart\Assert\Assert;

final class PluginsConfigurator
{
    /**
     * @param iterable<Plugin> $plugins
     *
     * @return PluginClient
     */
    public static function configure(ClientInterface $client, iterable $plugins): ClientInterface
    {
        Assert::allIsInstanceOf($plugins, Plugin::class);

        return new PluginClient(
            $client,
            is_array($plugins) ? $plugins : iterator_to_array($plugins, false),
            []
        );
    }
}
