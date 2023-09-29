<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Test;

use Http\Mock\Client;
use Webmozart\Assert\Assert;

trait UseMockClient
{
    use UseHttpFactories;

    /**
     * @param callable(Client $client): Client|null $configurator
     */
    private function mockClient(callable $configurator = null): Client
    {
        Assert::classExists(Client::class, 'Could not find a mock client. Please run: "composer require --dev php-http/mock-client"');
        $configurator ??= fn (Client $client) => $client;

        return $configurator(new Client());
    }
}
