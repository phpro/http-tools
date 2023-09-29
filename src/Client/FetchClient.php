<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Client;

use Http\Client\Common\Plugin\ErrorPlugin;
use Http\Client\Common\Plugin\HeaderSetPlugin;
use Http\Client\Common\PluginClient;
use Phpro\HttpTools\Request\Request;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Webmozart\Assert\Assert;

/**
 * This class is inspired on the JS fetch() function:.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API/Using_Fetch
 *
 * It also contains aliases, just like axios does:
 * @see https://axios-http.com/docs/api_intro
 *
 * fetch(url[, config])
 * get(url[, config])
 * delete(url[, config])
 * head(url[, config])
 * options(url[, config])
 * post(url[, data[, config]])
 * put(url[, data[, config]])
 * patch(url[, data[, config]])
 *
 * It is linked to this package, so that you can use the transport features as well.
 * This makes it possible to e.g. directly parse JSON inside the fetch() function.
 *
 * @template InstanceData
 * @template InstanceTransportRequest
 * @template InstanceTransportResponse
 */
final class FetchClient
{
    /**
     * @var FetchConfig<InstanceData, InstanceTransportRequest, InstanceTransportResponse>|null
     */
    private ?FetchConfig $config;

    /**
     * @param FetchConfig<InstanceData, InstanceTransportRequest, InstanceTransportResponse>|null $config
     */
    private function __construct(
        ?FetchConfig $config = null
    ) {
        $this->config = $config;
    }

    /**
     * @pure
     *
     * @return self<null, never, never>
     */
    public static function default(): self
    {
        return new self();
    }

    /**
     * @pure
     *
     * @template NewInstanceData
     * @template NewInstanceTransportRequest
     * @template NewInstanceTransportResponse
     *
     * @param FetchConfig<InstanceData, InstanceTransportRequest, InstanceTransportResponse> $config
     *
     * @return self<NewInstanceData, NewInstanceTransportRequest, NewInstanceTransportResponse>
     */
    public static function configure(FetchConfig $config): self
    {
        return new self($config);
    }

    /**
     * @template CallTimeData
     * @template CallTimeTransportRequest
     * @template CallTimeTransportResponse
     *
     * @param FetchConfig<CallTimeData, CallTimeTransportRequest, CallTimeTransportResponse>|null $config
     *
     * @return (CallTimeTransportResponse is never
     *     ? (InstanceTransportResponse is never ? ResponseInterface : InstanceTransportResponse)
     *     : CallTimeTransportResponse
     * )
     */
    public function __invoke(string $uri, FetchConfig $config = null)
    {
        $allConfig = FetchConfig::defaults()->merge($this->config)->merge($config);

        Assert::notNull($allConfig->method, 'Expected an HTTP method to be configured during fetch.');
        Assert::notNull($allConfig->transport, 'Expected an HTTP transport factory to be configured during fetch.');

        $client = $this->configureClient($allConfig);
        $transport = ($allConfig->transport)($client);
        $request = new Request($allConfig->method, $uri, [], $allConfig->data);

        return $transport($request);
    }

    /**
     * @template CallTimeData
     * @template CallTimeTransportRequest
     * @template CallTimeTransportResponse
     *
     * @param FetchConfig<CallTimeData, CallTimeTransportRequest, CallTimeTransportResponse>|null $config
     *
     * @return (CallTimeTransportResponse is never
     *     ? (InstanceTransportResponse is never ? ResponseInterface : InstanceTransportResponse)
     *     : CallTimeTransportResponse
     * )
     */
    public function get(string $uri, ?FetchConfig $config = null)
    {
        return ($this)($uri, $config);
    }

    /**
     * @template CallTimeData
     * @template CallTimeTransportRequest
     * @template CallTimeTransportResponse
     *
     * @param FetchConfig<CallTimeData, CallTimeTransportRequest, CallTimeTransportResponse>|null $config
     *
     * @return (CallTimeTransportResponse is never
     *     ? (InstanceTransportResponse is never ? ResponseInterface : InstanceTransportResponse)
     *     : CallTimeTransportResponse
     * )
     */
    public function options(string $uri, ?FetchConfig $config = null)
    {
        return ($this)($uri, FetchConfig::of(method: 'OPTIONS')->merge($config));
    }

    /**
     * @template CallTimeData
     * @template CallTimeTransportRequest
     * @template CallTimeTransportResponse
     *
     * @param FetchConfig<CallTimeData, CallTimeTransportRequest, CallTimeTransportResponse>|null $config
     *
     * @return (CallTimeTransportResponse is never
     *     ? (InstanceTransportResponse is never ? ResponseInterface : InstanceTransportResponse)
     *     : CallTimeTransportResponse
     * )
     */
    public function head(string $uri, ?FetchConfig $config = null)
    {
        return ($this)($uri, FetchConfig::of(method: 'HEAD')->merge($config));
    }

    /**
     * @template CallTimeData
     * @template CallTimeTransportRequest
     * @template CallTimeTransportResponse
     *
     * @param FetchConfig<CallTimeData, CallTimeTransportRequest, CallTimeTransportResponse>|null $config
     *
     * @return (CallTimeTransportResponse is never
     *     ? (InstanceTransportResponse is never ? ResponseInterface : InstanceTransportResponse)
     *     : CallTimeTransportResponse
     * )
     */
    public function delete(string $uri, ?FetchConfig $config = null)
    {
        return ($this)($uri, FetchConfig::of(method: 'DELETE')->merge($config));
    }

    /**
     * @template CallTimeData
     * @template CallTimeTransportRequest
     * @template CallTimeTransportResponse
     *
     * @param CallTimeData $data
     * @param FetchConfig<CallTimeData, CallTimeTransportRequest, CallTimeTransportResponse>|null $config
     *
     * @return (CallTimeTransportResponse is never
     *     ? (InstanceTransportResponse is never ? ResponseInterface : InstanceTransportResponse)
     *     : CallTimeTransportResponse
     * )
     */
    public function post(string $uri, mixed $data = null, ?FetchConfig $config = null)
    {
        return ($this)($uri, FetchConfig::of(
            method: 'POST',
            data: $data
        )->merge($config));
    }

    /**
     * @template CallTimeData
     * @template CallTimeTransportRequest
     * @template CallTimeTransportResponse
     *
     * @param CallTimeData $data
     * @param FetchConfig<CallTimeData, CallTimeTransportRequest, CallTimeTransportResponse>|null $config
     *
     * @return (CallTimeTransportResponse is never
     *     ? (InstanceTransportResponse is never ? ResponseInterface : InstanceTransportResponse)
     *     : CallTimeTransportResponse
     * )
     */
    public function put(string $uri, mixed $data = null, ?FetchConfig $config = null)
    {
        return ($this)($uri, FetchConfig::of(
            method: 'PUT',
            data: $data
        )->merge($config));
    }

    /**
     * @template CallTimeData
     * @template CallTimeTransportRequest
     * @template CallTimeTransportResponse
     *
     * @param CallTimeData $data
     * @param FetchConfig<CallTimeData, CallTimeTransportRequest, CallTimeTransportResponse>|null $config
     *
     * @return (CallTimeTransportResponse is never
     *     ? (InstanceTransportResponse is never ? ResponseInterface : InstanceTransportResponse)
     *     : CallTimeTransportResponse
     * )
     */
    public function patch(string $uri, mixed $data = null, ?FetchConfig $config = null)
    {
        return ($this)($uri, FetchConfig::of(
            method: 'PATCH',
            data: $data
        )->merge($config));
    }

    private function configureClient(FetchConfig $config): ClientInterface
    {
        Assert::notNull($config->client, 'Expected an HTTP client to be configured during fetch.');

        return new PluginClient(
            $config->client,
            [
                new ErrorPlugin(),
                ...($config->headers ? [new HeaderSetPlugin($config->headers)] : []),
                ...$config->plugins,
            ]
        );
    }
}
