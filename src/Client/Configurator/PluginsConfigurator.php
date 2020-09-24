<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Client\Configurator;

use Http\Client\Common\Plugin;
use Http\Client\Common\PluginClient;
use Psr\Http\Client\ClientInterface;
use Webmozart\Assert\Assert;

final class PluginsConfigurator
{
    /**
     * @param list<Plugin> $plugins
     *
     * @return PluginClient
     */
    public static function configure(ClientInterface $client, iterable $plugins): ClientInterface
    {
        Assert::allIsInstanceOf($plugins, Plugin::class);

        return new PluginClient(
            $client,
            [...$plugins],
            []
        );
    }
}
