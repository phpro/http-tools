<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Client\Factory;

use Psr\Http\Client\ClientInterface;
use Webmozart\Assert\Assert;

/**
 * This class can be used to lazyily load a client.
 * Especially useful in frameworks like Magento, which don't know the concept of factories!
 * For symfony based projects, this class might be overkill!
 */
final class LazyFactoryLoader
{
    /**
     * @var class-string<FactoryInterface>
     */
    private string $factory;
    private iterable $middlewares;
    private array $options;
    private ?ClientInterface $loaded = null;

    /**
     * @param class-string<FactoryInterface> $factory
     */
    public function __construct(string $factory, iterable $middlewares, array $options = [])
    {
        $this->factory = $factory;
        $this->middlewares = $middlewares;
        $this->options = $options;
    }

    public function load(): ClientInterface
    {
        if (!$this->loaded) {
            /** @psalm-suppress DocblockTypeContradiction */
            Assert::subclassOf($this->factory, FactoryInterface::class);
            $this->loaded = ($this->factory)::create($this->middlewares, $this->options);
        }

        return $this->loaded;
    }
}
