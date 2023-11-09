<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Client\Factory;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Phpro\HttpTools\Dependency\GuzzleDependency;
use Psr\Http\Client\ClientInterface;
use Webmozart\Assert\Assert;

final class GuzzleClientFactory implements FactoryInterface
{
    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     *
     * @param list<callable(callable):callable> $middlewares
     */
    public static function create(iterable $middlewares, array $options = []): ClientInterface
    {
        Assert::allIsCallable($middlewares);
        GuzzleDependency::guard();

        $stack = HandlerStack::create();

        foreach ($middlewares as $middleware) {
            $stack->push($middleware);
        }

        return new Client(
            array_merge($options, ['handler' => $stack])
        );
    }
}
