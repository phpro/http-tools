<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Transport\Json;

use Phpro\HttpTools\Test\UseHttpToolsFactories;
use function Amp\Promise\wait;
use Http\Mock\Client;
use Phpro\HttpTools\Test\UseMockClient;
use Phpro\HttpTools\Transport\Json\AsyncJsonTransport;
use Phpro\HttpTools\Uri\RawUriBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use function Safe\json_encode;

/**
 * @covers \Phpro\HttpTools\Test\UseHttpFactories
 * @covers \Phpro\HttpTools\Test\UseHttpToolsFactories
 * @covers \Phpro\HttpTools\Test\UseMockClient
 * @covers \Phpro\HttpTools\Transport\Json\AsyncJsonTransport
 *
 * @uses \Phpro\HttpTools\Uri\RawUriBuilder
 * @uses \Phpro\HttpTools\Async\HttplugPromiseAdapter
 */
class AsyncJsonTransportTest extends TestCase
{
    use UseMockClient;
    use UseHttpToolsFactories;

    private AsyncJsonTransport $transport;
    private Client $client;

    protected function setUp(): void
    {
        $this->client = $this->mockClient();
        $this->transport = AsyncJsonTransport::createWithAutodiscoveredPsrFactories(
            $this->client,
            RawUriBuilder::createWithAutodiscoveredPsrFactories()
        );
    }

    /** @test */
    public function it_can_send_and_receive_json(): void
    {
        $request = $this->createToolsRequest('GET', '/some-endpoint', [], ['hello' => 'world']);
        $this->client->addResponse(
            $this->createResponse(200)
                ->withAddedHeader('Content-Type', 'application/json')
                ->withBody($this->createStream(json_encode($expectedResponse = ['response' => 'ok'])))
        );

        $actualResponse = wait(($this->transport)($request));
        $sentRequest = $this->client->getLastRequest();

        self::assertSame($expectedResponse, $actualResponse);
        self::assertSame($request->method(), $sentRequest->getMethod());
        self::assertSame($request->uri(), $sentRequest->getUri()->__toString());
        self::assertSame(json_encode($request->body()), $sentRequest->getBody()->__toString());
        self::assertSame(['application/json'], $sentRequest->getHeader('Content-Type'));
        self::assertSame(['application/json'], $sentRequest->getHeader('Accept'));
    }

    /** @test */
    public function it_can_send_with_empty_body(): void
    {
        $request = $this->createToolsRequest('GET', '/some-endpoint', [], null);
        $this->client->addResponse(
            $this->createResponse(200)
                ->withAddedHeader('Content-Type', 'application/json')
                ->withBody($this->createStream(json_encode($expectedResponse = ['response' => 'ok'])))
        );

        $actualResponse = wait(($this->transport)($request));
        $sentRequest = $this->client->getLastRequest();

        self::assertSame($expectedResponse, $actualResponse);
        self::assertSame($request->method(), $sentRequest->getMethod());
        self::assertSame($request->uri(), $sentRequest->getUri()->__toString());
        self::assertSame('', $sentRequest->getBody()->__toString());
        self::assertSame(['application/json'], $sentRequest->getHeader('Content-Type'));
        self::assertSame(['application/json'], $sentRequest->getHeader('Accept'));
    }

    /** @test */
    public function it_can_handle_failure(): void
    {
        $request = $this->createToolsRequest('GET', '/some-endpoint', [], ['hello' => 'world']);
        $this->client->addException(
            $exception = $this->createEmptyHttpClientException('could not load endpoint...')
        );

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage($exception->getMessage());

        wait(($this->transport)($request));
    }
}
