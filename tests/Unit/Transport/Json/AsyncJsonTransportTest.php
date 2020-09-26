<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Transport\Json;

use Http\Mock\Client;
use Phpro\HttpTools\Client\Factory\SymfonyClientFactory;
use Phpro\HttpTools\Test\UseMockClient;
use Phpro\HttpTools\Tests\Helper\Request\SampleRequest;
use Phpro\HttpTools\Transport\Json\AsyncJsonTransport;
use Phpro\HttpTools\Uri\RawUriBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;
use function Amp\Promise\wait;
use function Safe\json_encode;

/**
 * @covers \Phpro\HttpTools\Test\UseHttpFactories
 * @covers \Phpro\HttpTools\Test\UseMockClient
 * @covers \Phpro\HttpTools\Transport\Json\AsyncJsonTransport
 *
 * @uses \Phpro\HttpTools\Uri\RawUriBuilder
 * @uses \Phpro\HttpTools\Async\HttplugPromiseAdapter
 */
class AsyncJsonTransportTest extends TestCase
{
    use UseMockClient;

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
        $request = new SampleRequest('GET', '/some-endpoint', [], ['hello' => 'world']);
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
    public function it_can_handle_failure(): void
    {
        $request = new SampleRequest('GET', '/some-endpoint', [], ['hello' => 'world']);
        $this->client->addException(
            $exception = new class('could not load endpoint...')
                extends RuntimeException implements ClientExceptionInterface
                {}
        );

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage($exception->getMessage());

        wait(($this->transport)($request));
    }
}
