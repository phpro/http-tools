<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Transport;

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
    public function it_can_send_and_receive_encoded(): void
    {
        $transport = $this->createTransport();
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
    public function it_can_handle_failure(): void
    {
        $transport = $this->createTransport();
        $request = $this->createToolsRequest('GET', '/some-endpoint', [], 'Hello');
        $this->client->addException(
            $exception = $this->createEmptyHttpClientException('could not load endpoint...')
        );

        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage($exception->getMessage());

        $transport($request);
    }

    private function createTransport(): TransportInterface
    {
        return EncodedTransportFactory::create(
            $this->client,
            RawUriBuilder::createWithAutodiscoveredPsrFactories(),
            RawEncoder::createWithAutodiscoveredPsrFactories(),
            RawDecoder::createWithAutodiscoveredPsrFactories()
        );
    }
}
