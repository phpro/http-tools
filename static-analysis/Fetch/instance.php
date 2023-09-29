<?php

use Phpro\HttpTools\Client\FetchClient;
use Phpro\HttpTools\Client\FetchConfig;
use Phpro\HttpTools\Transport\Presets\JsonPreset;
use Phpro\HttpTools\Transport\TransportInterface;
use Phpro\HttpTools\Uri\RawUriBuilder;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

$uri = 'https://swapi.dev/api/people';
$jsonTransport = static fn (ClientInterface $client): TransportInterface =>
    JsonPreset::sync($client, RawUriBuilder::createWithAutodiscoveredPsrFactories());
$rawTransport = FetchConfig::defaultTransport();

/**
 * @template Data
 * @template TransportRequest
 * @template TransportResponse
 *
 * @return FetchClient<null, never, never>
 */
function checkDefaultInstanceSignature()
{
    return FetchClient::default();
}

/**
 * @template Data
 * @template TransportRequest
 * @template TransportResponse
 *
 * @param FetchConfig<Data, TransportRequest, TransportResponse> $config
 *
 * @return FetchClient<Data, TransportRequest, TransportResponse>
 */
function checkInstanceSignatureWithTransport(FetchConfig $config)
{
    return FetchClient::configure($config);
}

/**
 * @return FetchClient<array, array|null, array>
 */
function checkInstanceSignatureWithConfiguredTransport()
{
    global $jsonTransport;
    return FetchClient::configure(FetchConfig::of(
        transport: $jsonTransport,
        data: []
    ));
}

/**
 * @return FetchClient<null, never, never>
 */
function checkInstanceSignatureWithEmptyConfig()
{
    return FetchClient::configure(FetchConfig::of());
}

function urlOnly(): ResponseInterface
{
    global $uri;
    return FetchClient::default()($uri);
}

function configWithoutDataOrTransport(): ResponseInterface
{
    global $uri;
    return FetchClient::default()($uri, FetchConfig::of(method: 'OPTIONS'));
}

function configWithTransport(): array
{
    global $uri, $jsonTransport;
    return FetchClient::default()($uri, FetchConfig::of(
        transport: $jsonTransport,
    ));
}

function configWithTransportAndMatchingData(): array
{
    global $uri, $jsonTransport;
    return FetchClient::default()($uri, FetchConfig::of(
        data: [],
        transport: $jsonTransport,
    ));
}

function configWithTransportAndInvalidData(): array
{
    global $uri, $jsonTransport;
    return FetchClient::default()($uri, FetchConfig::of(
        data: 'This is not supported',
        transport: $jsonTransport,
    ));
}

function instanceTransport(): array
{
    global $uri, $jsonTransport;
    return FetchClient::configure(
        FetchConfig::of(
            transport: $jsonTransport,
        )
    )($uri, FetchConfig::of(
        data: [],
    ));
}

function instanceAndCallTimeTransport(): array
{
    global $uri, $rawTransport, $jsonTransport;
    return FetchClient::configure(
        FetchConfig::of(
            transport: $rawTransport,
        )
    )($uri, FetchConfig::of(
        data: [],
        transport: $jsonTransport,
    ));
}
