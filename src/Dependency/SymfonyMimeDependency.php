<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Dependency;

use Symfony\Component\Mime\MimeTypes;
use Webmozart\Assert\Assert;

final class SymfonyMimeDependency
{
    public static function guard(): void
    {
        Assert::classExists(
            MimeTypes::class,
            'Could not find symfony HTTP client. Please run: "composer require symfony/mime" and make sure the version >6.0'
        );
    }
}
