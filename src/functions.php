<?php

declare(strict_types=1);

namespace Phpro\HttpTools;

use Phpro\HttpTools\Client\FetchClient;
use Phpro\HttpTools\Client\FetchConfig;
use Psr\Http\Message\ResponseInterface;

/**
 * @template Data
 * @template TransportRequest
 * @template TransportResponse
 *
 * @param FetchConfig<Data, TransportRequest, TransportResponse>|null $config
 *
 * @return (TransportResponse is never ? ResponseInterface : TransportResponse)
 */
function fetch(string $uri, ?FetchConfig $config = null)
{
    return FetchClient::default()($uri, $config);
}

/**
 * @template Data
 * @template TransportRequest
 * @template TransportResponse
 *
 * @param FetchConfig<Data, TransportRequest, TransportResponse>|null $config
 *
 * @return (TransportResponse is never ? ResponseInterface : TransportResponse)
 */
function get(string $uri, ?FetchConfig $config = null)
{
    return FetchClient::default()->get($uri, $config);
}

/**
 * @template Data
 * @template TransportRequest
 * @template TransportResponse
 *
 * @param FetchConfig<Data, TransportRequest, TransportResponse>|null $config
 *
 * @return (TransportResponse is never ? ResponseInterface : TransportResponse)
 */
function options(string $uri, ?FetchConfig $config = null)
{
    return FetchClient::default()->options($uri, $config);
}

/**
 * @template Data
 * @template TransportRequest
 * @template TransportResponse
 *
 * @param FetchConfig<Data, TransportRequest, TransportResponse>|null $config
 *
 * @return (TransportResponse is never ? ResponseInterface : TransportResponse)
 */
function head(string $uri, ?FetchConfig $config = null)
{
    return FetchClient::default()->head($uri, $config);
}

/**
 * @template Data
 * @template TransportRequest
 * @template TransportResponse
 *
 * @param FetchConfig<Data, TransportRequest, TransportResponse>|null $config
 *
 * @return (TransportResponse is never ? ResponseInterface : TransportResponse)
 */
function delete(string $uri, ?FetchConfig $config = null)
{
    return FetchClient::default()->delete($uri, $config);
}

/**
 * @template Data
 * @template TransportRequest
 * @template TransportResponse
 *
 * @param Data $data
 * @param FetchConfig<Data, TransportRequest, TransportResponse>|null $config
 *
 * @return (TransportResponse is never ? ResponseInterface : TransportResponse)
 */
function post(string $uri, mixed $data, ?FetchConfig $config = null)
{
    return FetchClient::default()->post($uri, $data, $config);
}

/**
 * @template Data
 * @template TransportRequest
 * @template TransportResponse
 *
 * @param Data $data
 * @param FetchConfig<Data, TransportRequest, TransportResponse>|null $config
 *
 * @return (TransportResponse is never ? ResponseInterface : TransportResponse)
 */
function put(string $uri, mixed $data, ?FetchConfig $config = null)
{
    return FetchClient::default()->put($uri, $data, $config);
}

/**
 * @template Data
 * @template TransportRequest
 * @template TransportResponse
 *
 * @param Data $data
 * @param FetchConfig<Data, TransportRequest, TransportResponse>|null $config
 *
 * @return (TransportResponse is never ? ResponseInterface : TransportResponse)
 */
function patch(string $uri, mixed $data, ?FetchConfig $config = null)
{
    return FetchClient::default()->patch($uri, $data, $config);
}
