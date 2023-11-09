<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Dependency;

use Symfony\Component\HttpClient\CurlHttpClient;
use Webmozart\Assert\Assert;

final class SymfonyClientDependency
{
    public static function guard(): void
    {
        Assert::classExists(
            CurlHttpClient::class,
            'Could not find symfony HTTP client. Please run: "composer require symfony/http-client" and make sure the version >5.4'
        );
    }
}
