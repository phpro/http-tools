<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Client;

use Http\Client\Common\Plugin;
use Http\Discovery\Psr18ClientDiscovery;
use Phpro\HttpTools\Request\RequestInterface;
use Phpro\HttpTools\Transport\Presets\PsrPreset;
use Phpro\HttpTools\Transport\TransportInterface;
use Phpro\HttpTools\Uri\RawUriBuilder;

use function Psl\Dict\merge;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @template Data
 * @template TransportRequest
 * @template TransportResponse
 *
 * @psalm-import-type Method from RequestInterface
 */
final class FetchConfig
{
    /**
     * @psalm-readonly
     *
     * @var Method|null
     */
    public ?string $method = null;

    /**
     * @psalm-readonly
     *
     * @var array<string, string>
     */
    public array $headers;

    /**
     * @psalm-readonly
     *
     * @var Data
     */
    public mixed $data;

    /**
     * @psalm-readonly
     *
     * @var list<Plugin>
     */
    public array $plugins;

    /**
     * @psalm-readonly
     */
    public ?ClientInterface $client;

    /**
     * @var (callable(ClientInterface): TransportInterface<TransportRequest, TransportResponse>)|null
     */
    public $transport;

    /**
     * @param Method|null $method
     * @param array<string, string> $headers
     * @param Data $data
     * @param list<Plugin> $plugins
     * @param (callable(ClientInterface): TransportInterface<TransportRequest, TransportResponse>)|null $transport
     */
    private function __construct(
        ?string $method = null,
        array $headers = [],
        mixed $data = null,
        array $plugins = [],
        ?ClientInterface $client = null,
        $transport = null
    ) {
        $this->method = $method;
        $this->headers = $headers;
        $this->data = $data;
        $this->plugins = $plugins;
        $this->client = $client;
        $this->transport = $transport;
    }

    /**
     * @template NewData
     * @template NewTransportRequest
     * @template NewTransportResponse
     *
     * @param Method|null $method
     * @param array<string, string> $headers
     * @param NewData $data
     * @param list<Plugin> $plugins
     * @param (callable(ClientInterface): TransportInterface<NewTransportRequest, NewTransportResponse>)|null $transport
     *
     * @return FetchConfig<NewData, NewTransportRequest, NewTransportResponse>
     */
    public static function of(
        ?string $method = null,
        array $headers = [],
        mixed $data = null,
        array $plugins = [],
        ?ClientInterface $client = null,
        $transport = null
    ): self {
        return new self($method, $headers, $data, $plugins, $client, $transport);
    }

    /**
     * @psalm-suppress MixedReturnTypeCoercion
     *
     * @return FetchConfig<null, string|null, ResponseInterface>
     */
    public static function defaults(): self
    {
        return new self(
            method: 'GET',
            client: Psr18ClientDiscovery::find(),
            transport: self::defaultTransport()
        );
    }

    /**
     * @return callable(ClientInterface): TransportInterface<string|null, ResponseInterface>
     */
    public static function defaultTransport()
    {
        return static fn (ClientInterface $client) => PsrPreset::sync(
            $client,
            RawUriBuilder::createWithAutodiscoveredPsrFactories()
        );
    }

    /**
     * @template MergedData
     * @template MergedTransportRequest
     * @template MergedTransportResponse
     *
     * @param FetchConfig<MergedData, MergedTransportRequest, MergedTransportResponse>|null $config
     *
     * @return FetchConfig<
     *     ($config is null ? Data : (MergedData is null ? Data : MergedData)),
     *     ($config is null ? TransportRequest : (MergedTransportRequest is mixed ? MergedTransportRequest : TransportRequest)),
     *     ($config is null ? TransportResponse : (MergedTransportResponse is mixed ? MergedTransportResponse : TransportResponse))
     * >
     */
    public function merge(?FetchConfig $config): self
    {
        return new self(
            method: $config?->method ?? $this->method,
            headers: merge($this->headers, $config?->headers ?? []),
            data: $config?->data ?? $this->data,
            plugins: [...$this->plugins, ...($config?->plugins ?? [])],
            client: $config?->client ?? $this->client,
            transport: $config?->transport ?? $this->transport,
        );
    }
}
