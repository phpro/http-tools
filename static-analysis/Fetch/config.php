<?php

use Phpro\HttpTools\Client\FetchConfig;
use Phpro\HttpTools\Transport\Presets\JsonPreset;
use Phpro\HttpTools\Transport\TransportInterface;
use Phpro\HttpTools\Uri\RawUriBuilder;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

$jsonTransport = static fn (ClientInterface $client): TransportInterface =>
    JsonPreset::sync($client, RawUriBuilder::createWithAutodiscoveredPsrFactories());
$rawTransport = FetchConfig::defaultTransport();

/**
 * @return FetchConfig<null, never, never>
 */
function emptyConfig(): FetchConfig {
    return FetchConfig::of();
}

/**
 * @return FetchConfig<null, never, never>
 */
function mergedEmptyConfig(): FetchConfig {
    return FetchConfig::of()->merge(FetchConfig::of());
}

/**
 * @return FetchConfig<null, string|null, ResponseInterface>
 */
function defaultTransport(): FetchConfig
{
    global $rawTransport;

    return FetchConfig::of(
        transport: $rawTransport,
    );
}

/**
 * @return FetchConfig<string, string|null, ResponseInterface>
 */
function defaultTransportWithData(): FetchConfig
{
    global $rawTransport;

    return FetchConfig::of(
        data: 'hello',
        transport: $rawTransport,
    );
}

/**
 * @return FetchConfig<array, array|null, array>
 */
function mergedWithEmpty(): FetchConfig
{
    global $jsonTransport;

    return FetchConfig::of()->merge(FetchConfig::of(
        data: [],
        transport: $jsonTransport,
    ));
}

/**
 * @return FetchConfig<array, array|null, array>
 */
function mergedWithPreviousType(): FetchConfig
{
    global $jsonTransport, $rawTransport;

    return FetchConfig::of(
        transport: $rawTransport,
    )->merge(FetchConfig::of(
        data: [],
        transport: $jsonTransport,
    ));
}
