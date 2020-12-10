<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Test;

use Http\Client\Plugin\Vcr\NamingStrategy\NamingStrategyInterface;
use Http\Client\Plugin\Vcr\NamingStrategy\PathNamingStrategy;
use Http\Client\Plugin\Vcr\Recorder\FilesystemRecorder;
use Http\Client\Plugin\Vcr\RecordPlugin;
use Http\Client\Plugin\Vcr\ReplayPlugin;
use Webmozart\Assert\Assert;

trait UseVcrClient
{
    use UseHttpFactories;

    /**
     * @return array{RecordPlugin, ReplayPlugin}
     */
    private function useRecording(string $path, NamingStrategyInterface $namingStrategy = null): array
    {
        Assert::classExists(RecordPlugin::class, 'Could not find the VCR plugin. Please run: "composer require --dev php-http/vcr-plugin"');

        Assert::directory($path);
        $recorder = new FilesystemRecorder($path);
        $namingStrategy ??= new PathNamingStrategy();

        return [
            new RecordPlugin($namingStrategy, $recorder),
            new ReplayPlugin($namingStrategy, $recorder, false),
        ];
    }
}
