<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Dependency;

use Http\Client\Plugin\Vcr\RecordPlugin;
use Webmozart\Assert\Assert;

final class VcrPluginDependency
{
    public static function guard(): void
    {
        Assert::classExists(
            RecordPlugin::class,
            'Could not find the VCR plugin. Please run: "composer require --dev php-http/vcr-plugin"'
        );
    }
}
