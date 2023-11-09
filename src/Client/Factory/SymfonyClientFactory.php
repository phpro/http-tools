<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Client\Factory;

use Http\Client\Common\Plugin;
use Phpro\HttpTools\Client\Configurator\PluginsConfigurator;
use Phpro\HttpTools\Dependency\SymfonyClientDependency;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\HttplugClient;

final class SymfonyClientFactory implements FactoryInterface
{
    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     *
     * @param list<Plugin> $middlewares
     */
    public static function create(iterable $middlewares, array $options = []): ClientInterface
    {
        SymfonyClientDependency::guard();

        return PluginsConfigurator::configure(
            new HttplugClient(
                new CurlHttpClient($options)
            ),
            $middlewares
        );
    }
}
