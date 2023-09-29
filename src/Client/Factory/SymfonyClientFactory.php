<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Client\Factory;

use Http\Client\Common\Plugin;
use Phpro\HttpTools\Client\Configurator\PluginsConfigurator;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\HttplugClient;
use Webmozart\Assert\Assert;

final class SymfonyClientFactory implements FactoryInterface
{
    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     *
     * @param list<Plugin> $middlewares
     */
    public static function create(iterable $middlewares, array $options = []): ClientInterface
    {
        Assert::classExists(
            CurlHttpClient::class,
            'Could not find symfony HTTP client. Please run: "composer require symfony/http-client:^5.4"'
        );

        return PluginsConfigurator::configure(
            new HttplugClient(
                new CurlHttpClient($options)
            ),
            $middlewares
        );
    }
}
