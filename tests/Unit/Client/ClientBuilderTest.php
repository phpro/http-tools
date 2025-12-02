<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Client;

use Http\Client\Common\Plugin;
use Http\Client\Common\PluginClient;
use Http\Client\Plugin\Vcr\NamingStrategy\PathNamingStrategy;
use Http\Client\Plugin\Vcr\Recorder\FilesystemRecorder;
use Http\Message\Authentication\BasicAuth;
use Http\Mock\Client;
use Phpro\HttpTools\Client\ClientBuilder;
use Phpro\HttpTools\Plugin\CallbackPlugin;
use Phpro\HttpTools\Test\UseMockClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psl\Ref;

final class ClientBuilderTest extends TestCase
{
    use UseMockClient;

    #[Test]
    public function it_can_construct_with_default_client(): void
    {
        $builder = new ClientBuilder();
        $client = $builder->build();

        self::assertInstanceOf(PluginClient::class, $client);
    }

    #[Test]
    public function it_can_construct_with_custom_client_and_middlewares(): void
    {
        $mockClient = $this->mockClient(function (Client $client): Client {
            $client->setDefaultResponse($this->createResponse(204));

            return $client;
        });

        $plugin = new Plugin\HeaderSetPlugin(['X-Custom' => 'test']);
        $builder = new ClientBuilder($mockClient, [$plugin]);
        $client = $builder->build();

        $response = $client->sendRequest($this->createRequest('GET', '/test'));
        $lastRequest = $mockClient->getLastRequest();

        self::assertSame('test', $lastRequest->getHeaderLine('X-Custom'));
        self::assertSame(204, $response->getStatusCode());
    }

    #[Test]
    public function it_can_create_default_builder(): void
    {
        $mockClient = $this->mockClient(function (Client $client): Client {
            $client->setDefaultException($this->createEmptyHttpClientException('Error'));

            return $client;
        });

        $builder = ClientBuilder::default($mockClient);
        $client = $builder->build();

        self::expectException(\Psr\Http\Client\ClientExceptionInterface::class);
        $client->sendRequest($this->createRequest('GET', '/test'));
    }

    #[Test]
    public function it_can_add_decorator(): void
    {
        $mockClient = $this->mockClient(function (Client $client): Client {
            $client->setDefaultResponse($this->createResponse(200));

            return $client;
        });

        $decoratorCalled = new Ref(false);
        $builder = new ClientBuilder($mockClient);
        $builder->addDecorator(function ($client) use ($decoratorCalled) {
            $decoratorCalled->value = true;

            return $client;
        });

        $client = $builder->build();
        $client->sendRequest($this->createRequest('GET', '/test'));

        self::assertTrue($decoratorCalled->value);
    }

    #[Test]
    public function it_can_add_plugin(): void
    {
        $mockClient = $this->mockClient(function (Client $client): Client {
            $client->setDefaultResponse($this->createResponse(200));

            return $client;
        });

        $builder = new ClientBuilder($mockClient);
        $builder->addPlugin(new Plugin\HeaderSetPlugin(['X-Test' => 'value']));
        $client = $builder->build();

        $client->sendRequest($this->createRequest('GET', '/test'));
        $lastRequest = $mockClient->getLastRequest();

        self::assertSame('value', $lastRequest->getHeaderLine('X-Test'));
    }

    #[Test]
    public function it_can_add_plugin_with_currently_configured_client(): void
    {
        $mockClient = $this->mockClient(function (Client $client): Client {
            $client->addResponse($this->createResponse(202, 'Internal'));
            $client->setDefaultResponse($this->createResponse(200));

            return $client;
        });

        $builder = new ClientBuilder($mockClient);
        $builder->addPlugin(new Plugin\HeaderSetPlugin(['X-First' => 'first']));

        $builder->addPluginWithCurrentlyConfiguredClient(
            function ($client) {
                // Make an internal request and use its response
                $testRequest = $this->createRequest('GET', '/internal-test');
                $internalResponse = $client->sendRequest($testRequest);
                $statusFromInternal = (string) $internalResponse->getStatusCode();

                return new Plugin\HeaderSetPlugin(['X-Second' => 'status-'.$statusFromInternal]);
            }
        );

        $client = $builder->build();
        $client->sendRequest($this->createRequest('GET', '/test'));
        $lastRequest = $mockClient->getLastRequest();

        // Verify both requests were made
        self::assertCount(2, $mockClient->getRequests(), 'Should have made 2 requests: internal test + actual request');
        self::assertSame('/internal-test', $mockClient->getRequests()[0]->getUri()->getPath());
        self::assertSame('/test', $mockClient->getRequests()[1]->getUri()->getPath());

        // Verify the second header contains the status from the internal request
        self::assertSame('first', $lastRequest->getHeaderLine('X-First'));
        self::assertSame('status-202', $lastRequest->getHeaderLine('X-Second'));
    }

    #[Test]
    public function it_can_add_authentication(): void
    {
        $mockClient = $this->mockClient(function (Client $client): Client {
            $client->setDefaultResponse($this->createResponse(200));

            return $client;
        });

        $builder = new ClientBuilder($mockClient);
        $builder->addAuthentication(new BasicAuth('user', 'pass'));
        $client = $builder->build();

        $client->sendRequest($this->createRequest('GET', '/test'));
        $lastRequest = $mockClient->getLastRequest();

        self::assertStringStartsWith('Basic ', $lastRequest->getHeaderLine('Authorization'));
    }

    #[Test]
    public function it_can_add_logger(): void
    {
        $mockClient = $this->mockClient(function (Client $client): Client {
            $client->setDefaultResponse($this->createResponse(200));

            return $client;
        });

        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $logger->expects(self::exactly(2))
            ->method('info')
            ->with(
                self::callback(fn (string $message): bool => str_contains($message, 'GET') || str_contains($message, '200'))
            );

        $builder = new ClientBuilder($mockClient);
        $builder->addLogger($logger);
        $client = $builder->build();

        $response = $client->sendRequest($this->createRequest('GET', '/test'));

        self::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function it_can_add_headers(): void
    {
        $mockClient = $this->mockClient(function (Client $client): Client {
            $client->setDefaultResponse($this->createResponse(200));

            return $client;
        });

        $builder = new ClientBuilder($mockClient);
        $builder->addHeaders([
            'X-Custom-Header' => 'custom-value',
            'X-Another-Header' => 'another-value',
        ]);
        $client = $builder->build();

        $client->sendRequest($this->createRequest('GET', '/test'));
        $lastRequest = $mockClient->getLastRequest();

        self::assertSame('custom-value', $lastRequest->getHeaderLine('X-Custom-Header'));
        self::assertSame('another-value', $lastRequest->getHeaderLine('X-Another-Header'));
    }

    #[Test]
    public function it_can_add_base_uri_with_string(): void
    {
        $mockClient = $this->mockClient(function (Client $client): Client {
            $client->setDefaultResponse($this->createResponse(200));

            return $client;
        });

        $builder = new ClientBuilder($mockClient);
        $builder->addBaseUri('https://example.com');
        $client = $builder->build();

        $client->sendRequest($this->createRequest('GET', '/test'));
        $lastRequest = $mockClient->getLastRequest();

        self::assertSame('example.com', $lastRequest->getUri()->getHost());
        self::assertSame('https', $lastRequest->getUri()->getScheme());
    }

    #[Test]
    public function it_can_add_base_uri_with_uri_interface(): void
    {
        $mockClient = $this->mockClient(function (Client $client): Client {
            $client->setDefaultResponse($this->createResponse(200));

            return $client;
        });

        $uri = \Http\Discovery\Psr17FactoryDiscovery::findUriFactory()
            ->createUri('https://api.example.com');

        $builder = new ClientBuilder($mockClient);
        $builder->addBaseUri($uri);
        $client = $builder->build();

        $client->sendRequest($this->createRequest('GET', '/test'));
        $lastRequest = $mockClient->getLastRequest();

        self::assertSame('api.example.com', $lastRequest->getUri()->getHost());
        self::assertSame('https', $lastRequest->getUri()->getScheme());
    }

    #[Test]
    public function it_can_add_recording(): void
    {
        $mockClient = $this->mockClient(function (Client $client): Client {
            $client->setDefaultResponse($this->createResponse(200, 'OK'));

            return $client;
        });

        $tempDir = sys_get_temp_dir().'/phpro-http-tools-test-'.uniqid();
        mkdir($tempDir, 0777, true);

        try {
            $namingStrategy = new PathNamingStrategy();
            $recorder = new FilesystemRecorder($tempDir);

            $builder = new ClientBuilder($mockClient);
            $builder->addRecording($namingStrategy, $recorder);
            $client = $builder->build();

            // First request - should record
            $response = $client->sendRequest($this->createRequest('GET', '/test'));
            self::assertSame(200, $response->getStatusCode());
            self::assertCount(1, $mockClient->getRequests());

            // Verify recording file was created
            $recordingFiles = glob($tempDir.'/*');
            self::assertCount(1, $recordingFiles, 'Recording file should be created');
            self::assertFileExists($recordingFiles[0]);

            // Second request - should replay from recording, not hit the mock client
            $response2 = $client->sendRequest($this->createRequest('GET', '/test'));
            self::assertSame(200, $response2->getStatusCode());
            self::assertCount(1, $mockClient->getRequests(), 'Second request should use recording, not hit the client');
        } finally {
            // Cleanup
            array_map('unlink', glob($tempDir.'/*') ?: []);
            rmdir($tempDir);
        }
    }

    #[Test]
    public function it_respects_plugin_priority_order(): void
    {
        $mockClient = $this->mockClient(function (Client $client): Client {
            $client->setDefaultResponse($this->createResponse(200));

            return $client;
        });

        $executionOrder = new Ref([]);
        $builder = new ClientBuilder($mockClient);

        // Add plugins with different priorities
        $builder->addPlugin(
            new CallbackPlugin(function ($request, $next, $first) use ($executionOrder) {
                $executionOrder->value[] = 'default';

                return $next($request);
            }),
            ClientBuilder::PRIORITY_LEVEL_DEFAULT
        );

        $builder->addPlugin(
            new CallbackPlugin(function ($request, $next, $first) use ($executionOrder) {
                $executionOrder->value[] = 'security';

                return $next($request);
            }),
            ClientBuilder::PRIORITY_LEVEL_SECURITY
        );

        $builder->addPlugin(
            new CallbackPlugin(function ($request, $next, $first) use ($executionOrder) {
                $executionOrder->value[] = 'logging';

                return $next($request);
            }),
            ClientBuilder::PRIORITY_LEVEL_LOGGING
        );

        $client = $builder->build();
        $client->sendRequest($this->createRequest('GET', '/test'));

        // Higher priority values execute first
        self::assertSame(['logging', 'security', 'default'], $executionOrder->value);
    }

    #[Test]
    public function it_can_add_callback(): void
    {
        $mockClient = $this->mockClient(function (Client $client): Client {
            $client->setDefaultResponse($this->createResponse(200));

            return $client;
        });

        $callbackExecuted = new Ref(false);
        $builder = new ClientBuilder($mockClient);

        $builder->addCallback(function ($request, $next, $first) use ($callbackExecuted) {
            $callbackExecuted->value = true;

            return $next($request->withAddedHeader('X-Callback', 'executed'));
        });

        $client = $builder->build();
        $client->sendRequest($this->createRequest('GET', '/test'));
        $lastRequest = $mockClient->getLastRequest();

        self::assertTrue($callbackExecuted->value);
        self::assertSame('executed', $lastRequest->getHeaderLine('X-Callback'));
    }
}
