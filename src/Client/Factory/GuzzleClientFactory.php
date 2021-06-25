<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Client\Factory;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
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
        Assert::classExists(
            Client::class,
            'Could not find guzzle client. Please run: "composer require guzzlehttp/guzzle:^7.0"'
        );

        $stack = HandlerStack::create();

        foreach ($middlewares as $middleware) {
            $stack->push($middleware);
        }

        return new Client(
            array_merge($options, ['handler' => $stack])
        );
    }
}
