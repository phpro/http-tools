<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Transport;

use Http\Mock\Client;
use Phpro\HttpTools\Encoding\Raw\RawDecoder;
use Phpro\HttpTools\Encoding\Raw\RawEncoder;
use Phpro\HttpTools\Test\UseHttpToolsFactories;
use Phpro\HttpTools\Test\UseMockClient;
use Phpro\HttpTools\Transport\EncodedTransport;
use Phpro\HttpTools\Uri\RawUriBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Phpro\HttpTools\Test\UseHttpFactories
 * @covers \Phpro\HttpTools\Test\UseHttpToolsFactories
 * @covers \Phpro\HttpTools\Test\UseMockClient
 * @covers \Phpro\HttpTools\Transport\EncodedTransport
 *
 * @uses \Phpro\HttpTools\Uri\RawUriBuilder
 * @uses \Phpro\HttpTools\Encoding\Raw\RawEncoder
 * @uses \Phpro\HttpTools\Encoding\Raw\RawDecoder
 */
final class EncodedTransportTest extends TestCase
{
    use UseMockClient;
    use UseHttpToolsFactories;

    private EncodedTransport $transport;
    private Client $client;

    protected function setUp(): void
    {
        $this->client = $this->mockClient();
        $this->transport = EncodedTransport::createWithAutodiscoveredPsrFactories(
            $this->client,
            RawUriBuilder::createWithAutodiscoveredPsrFactories(),
            RawEncoder::createWithAutodiscoveredPsrFactories(),
            RawDecoder::createWithAutodiscoveredPsrFactories()
        );
    }

    /** @test */
    public function it_can_send_and_receive_encoded(): void
    {
        $request = $this->createToolsRequest('GET', '/some-endpoint', [], 'Hello');
        $this->client->addResponse(
            $this->createResponse(200)
                ->withBody($this->createStream($expectedResponse = 'World'))
        );

        $actualResponse = ($this->transport)($request);
        $sentRequest = $this->client->getLastRequest();

        self::assertSame($expectedResponse, $actualResponse);
        self::assertSame($request->method(), $sentRequest->getMethod());
        self::assertSame($request->uri(), $sentRequest->getUri()->__toString());
        self::assertSame((string) $request->body(), (string) $sentRequest->getBody());
    }
}
