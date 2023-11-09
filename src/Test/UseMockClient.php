<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Test;

use Http\Mock\Client;
use Phpro\HttpTools\Dependency\MockClientDependency;

trait UseMockClient
{
    use UseHttpFactories;

    /**
     * @param callable(Client $client): Client|null $configurator
     */
    private function mockClient(callable $configurator = null): Client
    {
        MockClientDependency::guard();
        $configurator ??= fn (Client $client) => $client;

        return $configurator(new Client());
    }
}
