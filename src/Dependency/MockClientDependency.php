<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Dependency;

use Webmozart\Assert\Assert;

final class MockClientDependency
{
    public static function guard(): void
    {
        Assert::classExists(
            \Http\Mock\Client::class,
            'Could not find a mock client. Please run: "composer require --dev php-http/mock-client"'
        );
    }
}
