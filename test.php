<?php

use GuzzleHttp\Client;
use Http\Client\Common\Plugin\LoggerPlugin;
use Http\Message\Formatter\FullHttpMessageFormatter;
use Phpro\HttpTools\Client\FetchConfig;
use Phpro\HttpTools\Transport\Presets\JsonPreset;
use Phpro\HttpTools\Uri\RawUriBuilder;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use function Phpro\HttpTools\config;
use function Phpro\HttpTools\fetch;

require_once 'vendor/autoload.php';

$logger = new class() implements LoggerInterface {
    public function emergency($message, array $context = [])
    {
        echo $message;
    }

    public function alert($message, array $context = [])
    {
        echo $message;
    }

    public function critical($message, array $context = [])
    {
        echo $message;
    }

    public function error($message, array $context = [])
    {
        echo $message;
    }

    public function warning($message, array $context = [])
    {
        echo $message;
    }

    public function notice($message, array $context = [])
    {
        echo $message;
    }

    public function info($message, array $context = [])
    {
        echo $message;
    }

    public function debug($message, array $context = [])
    {
        echo $message;
    }

    public function log($level, $message, array $context = [])
    {
        echo $message;
    }
};


$response = fetch('https://swapi.dev/api/people', FetchConfig::of(
    headers: [
        'Accept-Language' => 'nl_BE'
    ],
    client: new Client([
        'verify' => false,
    ]),
    transport: fn(ClientInterface $client) =>
        JsonPreset::sync($client, RawUriBuilder::createWithAutodiscoveredPsrFactories()),
    plugins: [
        new LoggerPlugin($logger, new FullHttpMessageFormatter())
    ]
));

var_dump($response);