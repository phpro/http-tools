<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Transport;

use function Amp\Promise\wait;

use Http\Mock\Client;
use Phpro\HttpTools\Encoding\Raw\RawDecoder;
use Phpro\HttpTools\Encoding\Raw\RawEncoder;
use Phpro\HttpTools\Test\UseHttpToolsFactories;
use Phpro\HttpTools\Test\UseMockClient;
use Phpro\HttpTools\Transport\EncodedTransportFactory;
use Phpro\HttpTools\Transport\TransportInterface;
use Phpro\HttpTools\Uri\RawUriBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;

final class EncodedTransportTest extends TestCase
{
    use UseHttpToolsFactories;
    use UseMockClient;

    private Client $client;

    protected function setUp(): void
    {
        $this->client = $this->mockClient();
    }

    /** @test */
    public function it_can_send_and_receive_encoded_in_sync(): void
    {
        $transport = $this->createSyncTransport();
        $request = $this->createToolsRequest('GET', '/some-endpoint', [], 'Hello');
        $this->client->addResponse(
            $this->createResponse(200)
                ->withBody($this->createStream($expectedResponse = 'World'))
        );

        $actualResponse = $transport($request);
        $sentRequest = $this->client->getLastRequest();

        self::assertSame($expectedResponse, $actualResponse);
        self::assertSame($request->method(), $sentRequest->getMethod());
        self::assertSame($request->uri(), $sentRequest->getUri()->__toString());
        self::assertSame((string) $request->body(), (string) $sentRequest->getBody());
    }

    /** @test */
    public function it_can_send_and_receive_encoded_in_async(): void
    {
        $transport = $this->createAsyncTransport();
        $request = $this->createToolsRequest('GET', '/some-endpoint', [], 'Hello');
        $this->client->addResponse(
            $this->createResponse(200)
                ->withBody($this->createStream($expectedResponse = 'World'))
        );

        $actualResponse = wait($transport($request));
        $sentRequest = $this->client->getLastRequest();

        self::assertSame($expectedResponse, $actualResponse);
        self::assertSame($request->method(), $sentRequest->getMethod());
        self::assertSame($request->uri(), $sentRequest->getUri()->__toString());
        self::assertSame((string) $request->body(), (string) $sentRequest->getBody());
    }

    /** @test */
    public function it_can_handle_failure_in_async(): void
    {
        $transport = $this->createAsyncTransport();
        $request = $this->createToolsRequest('GET', '/some-endpoint', [], 'Hello');
        $this->client->addException(
            $exception = $this->createEmptyHttpClientException('could not load endpoint...')
        );

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage($exception->getMessage());

        wait($transport($request));
    }

    private function createSyncTransport(): TransportInterface
    {
        return EncodedTransportFactory::sync(
            $this->client,
            RawUriBuilder::createWithAutodiscoveredPsrFactories(),
            RawEncoder::createWithAutodiscoveredPsrFactories(),
            RawDecoder::createWithAutodiscoveredPsrFactories()
        );
    }

    private function createAsyncTransport(): TransportInterface
    {
        return EncodedTransportFactory::async(
            $this->client,
            RawUriBuilder::createWithAutodiscoveredPsrFactories(),
            RawEncoder::createWithAutodiscoveredPsrFactories(),
            RawDecoder::createWithAutodiscoveredPsrFactories()
        );
    }
}
