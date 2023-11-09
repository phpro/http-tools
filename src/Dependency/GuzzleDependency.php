<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Dependency;

use GuzzleHttp\Client;
use Webmozart\Assert\Assert;

final class GuzzleDependency
{
    public static function guard(): void
    {
        Assert::classExists(
            Client::class,
            'Could not find guzzle client. Please run: "composer require guzzlehttp/guzzle" and make sure the version >7.'
        );
    }
}
