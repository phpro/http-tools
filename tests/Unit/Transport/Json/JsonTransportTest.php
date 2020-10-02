<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Transport\Json;

use Http\Mock\Client;
use Phpro\HttpTools\Test\UseMockClient;
use Phpro\HttpTools\Tests\Helper\Request\SampleRequest;
use Phpro\HttpTools\Transport\Json\JsonTransport;
use Phpro\HttpTools\Uri\RawUriBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Phpro\HttpTools\Test\UseHttpFactories
 * @covers \Phpro\HttpTools\Test\UseMockClient
 * @covers \Phpro\HttpTools\Transport\Json\JsonTransport
 *
 * @uses \Phpro\HttpTools\Uri\RawUriBuilder
 */
class JsonTransportTest extends TestCase
{
    use UseMockClient;

    private JsonTransport $transport;
    private Client $client;

    protected function setUp(): void
    {
        $this->client = $this->mockClient();
        $this->transport = JsonTransport::createWithAutodiscoveredPsrFactories(
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

        $actualResponse = ($this->transport)($request);
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
        $request = new SampleRequest('GET', '/some-endpoint', [], null);
        $this->client->addResponse(
            $this->createResponse(200)
                ->withAddedHeader('Content-Type', 'application/json')
                ->withBody($this->createStream(json_encode($expectedResponse = ['response' => 'ok'])))
        );

        $actualResponse = ($this->transport)($request);
        $sentRequest = $this->client->getLastRequest();

        self::assertSame($expectedResponse, $actualResponse);
        self::assertSame($request->method(), $sentRequest->getMethod());
        self::assertSame($request->uri(), $sentRequest->getUri()->__toString());
        self::assertSame('', $sentRequest->getBody()->__toString());
        self::assertSame(['application/json'], $sentRequest->getHeader('Content-Type'));
        self::assertSame(['application/json'], $sentRequest->getHeader('Accept'));
    }
}
