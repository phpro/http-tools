<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Client\Factory;

use Psr\Http\Client\ClientInterface;

interface FactoryInterface
{
    public static function create(iterable $middlewares, array $options = []): ClientInterface;
}
