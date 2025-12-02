<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Client;

use Closure;
use Http\Client\Common\Plugin;
use Http\Client\Common\PluginClient;
use Http\Client\Plugin\Vcr\NamingStrategy\NamingStrategyInterface;
use Http\Client\Plugin\Vcr\Recorder\PlayerInterface;
use Http\Client\Plugin\Vcr\Recorder\RecorderInterface;
use Http\Client\Plugin\Vcr\RecordPlugin;
use Http\Client\Plugin\Vcr\ReplayPlugin;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Message\Authentication;
use Http\Message\Formatter;
use Phpro\HttpTools\Client\Configurator\PluginsConfigurator;
use Phpro\HttpTools\Plugin\CallbackPlugin;
use Psl\Fun;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use SplPriorityQueue;

/**
 * @psalm-type Decorator = \Closure(ClientInterface): ClientInterface
 *
 * @psalm-import-type PluginCallback from CallbackPlugin
 */
final class ClientBuilder
{
    public const int PRIORITY_LEVEL_DEFAULT = 0;
    public const int PRIORITY_LEVEL_SECURITY = 1000;
    public const int PRIORITY_LEVEL_LOGGING = 2000;

    private ClientInterface $client;

    /**
     * @var SplPriorityQueue<int, Plugin>
     */
    private SplPriorityQueue $plugins;
    /**
     * @var list<Decorator>
     */
    private array $decorators = [];

    /**
     * @param iterable<array-key, Plugin> $middlewares
     */
    public function __construct(
        ?ClientInterface $client = null,
        iterable $middlewares = [],
    ) {
        $this->client = $client ?? Psr18ClientDiscovery::find();
        /** @var SplPriorityQueue<int, Plugin> $plugins */
        $plugins = new SplPriorityQueue();
        $this->plugins = $plugins;

        foreach ($middlewares as $middleware) {
            $plugins->insert($middleware, self::PRIORITY_LEVEL_DEFAULT);
        }
    }

    public static function default(
        ?ClientInterface $client = null,
    ): self {
        return new self($client, [
            new Plugin\ErrorPlugin(),
        ]);
    }

    /**
     * @param Decorator $decorator
     */
    public function addDecorator(Closure $decorator): self
    {
        $this->decorators[] = $decorator;

        return $this;
    }

    public function addPlugin(
        Plugin $plugin,
        int $priority = self::PRIORITY_LEVEL_DEFAULT,
    ): self {
        $this->plugins->insert($plugin, $priority);

        return $this;
    }

    /**
     * @param Closure(ClientInterface): Plugin $pluginBuilder
     */
    public function addPluginWithCurrentlyConfiguredClient(
        Closure $pluginBuilder,
        int $priority = self::PRIORITY_LEVEL_DEFAULT,
    ): self {
        return $this->addPlugin(
            $pluginBuilder($this->build()),
            $priority,
        );
    }

    public function addAuthentication(
        Authentication $authentication,
        int $priority = self::PRIORITY_LEVEL_SECURITY,
    ): self {
        return $this->addPlugin(new Plugin\AuthenticationPlugin($authentication), $priority);
    }

    public function addLogger(
        LoggerInterface $logger,
        ?Formatter $formatter = null,
        int $priority = self::PRIORITY_LEVEL_LOGGING,
    ): self {
        return $this->addPlugin(new Plugin\LoggerPlugin($logger, $formatter), $priority);
    }

    /**
     * @param array<string, string | string[]> $headers
     *
     * @return $this
     */
    public function addHeaders(
        array $headers,
        int $priority = self::PRIORITY_LEVEL_DEFAULT,
    ): self {
        return $this->addPlugin(new Plugin\HeaderSetPlugin($headers), $priority);
    }

    public function addBaseUri(
        UriInterface|string $baseUri,
        bool $replaceHost = true,
        int $priority = self::PRIORITY_LEVEL_DEFAULT,
    ): self {
        $baseUri = match (true) {
            is_string($baseUri) => Psr17FactoryDiscovery::findUriFactory()->createUri($baseUri),
            default => $baseUri,
        };

        return $this->addPlugin(new Plugin\BaseUriPlugin($baseUri, ['replace' => $replaceHost]), $priority);
    }

    public function addRecording(
        NamingStrategyInterface $namingStrategy,
        RecorderInterface&PlayerInterface $recorder,
        int $priority = self::PRIORITY_LEVEL_LOGGING,
    ): self {
        return $this
            ->addPlugin(new RecordPlugin($namingStrategy, $recorder), $priority)
            ->addPlugin(new ReplayPlugin($namingStrategy, $recorder, false), $priority);
    }

    /**
     * @param PluginCallback $callback
     */
    public function addCallback(callable $callback, int $priority = self::PRIORITY_LEVEL_DEFAULT): self
    {
        return $this->addPlugin(new CallbackPlugin($callback), $priority);
    }

    public function build(): PluginClient
    {
        return PluginsConfigurator::configure(
            Fun\pipe(...$this->decorators)($this->client),
            [...clone $this->plugins]
        );
    }
}
