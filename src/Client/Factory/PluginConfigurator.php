<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Client\Factory;

use Http\Client\Common\Plugin;
use Http\Client\Common\PluginClient;
use Psr\Http\Client\ClientInterface;

final class PluginConfigurator implements ClientFactoryInterface
{
    private ClientFactoryInterface $factory;

    /** @var list<Plugin> */
    private iterable $plugins;

    /**
     * @param list<Plugin> $plugins
     */
    public function __construct(ClientFactoryInterface $factory, iterable $plugins)
    {
        $this->factory = $factory;
        $this->plugins = $plugins;
    }

    public function __invoke(array $options): ClientInterface
    {
        return new PluginClient(
            ($this->factory)($options),
            [...$this->plugins],
            []
        );
    }
}
