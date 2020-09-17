<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Client\Factory;

use Psr\Http\Client\ClientInterface;

interface ClientFactoryInterface
{
    public function __invoke(array $options): ClientInterface;
}
