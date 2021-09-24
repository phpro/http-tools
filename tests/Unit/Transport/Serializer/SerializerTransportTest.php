<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Transport\Serializer;

use Http\Message\RequestMatcher\CallbackRequestMatcher;
use Http\Mock\Client;
use Phpro\HttpTools\Serializer\SymfonySerializer;
use Phpro\HttpTools\Test\UseHttpToolsFactories;
use Phpro\HttpTools\Test\UseMockClient;
use Phpro\HttpTools\Tests\Helper\Model\SomeValueObject;
use Phpro\HttpTools\Transport\Presets\RawPreset;
use Phpro\HttpTools\Transport\Serializer\SerializerTransport;
use Phpro\HttpTools\Uri\RawUriBuilder;
use PHPUnit\Framework\TestCase;
use Psl\Json;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class SerializerTransportTest extends TestCase
{
    use UseHttpToolsFactories;
    use UseMockClient;

    private SerializerTransport $transport;
    private Client $client;

    protected function setUp(): void
    {
        $normalizers = [new ObjectNormalizer()];
        $encoders = [new JsonEncoder()];

        $this->client = $this->mockClient();
        $this->transport = new SerializerTransport(
            new SymfonySerializer(
                new Serializer($normalizers, $encoders),
                'json'
            ),
            RawPreset::sync(
                $this->client,
                RawUriBuilder::createWithAutodiscoveredPsrFactories(),
            )
        );
    }

    /** @test */
    public function it_can_serialize_request_and_deserialize_response_body_and_transport_it(): void
    {
        $valueObject = new SomeValueObject('Hello', 'World');
        $jsonData = Json\encode($data = ['x' => 'Hello', 'y' => 'World']);
        $request = $this->createToolsRequest('GET', '/', [], $valueObject);

        $this->client->on(
            new CallbackRequestMatcher(
                fn (RequestInterface $httpRequest): bool => (string) $httpRequest->getBody() === $jsonData
            ),
            $this->createResponse()->withBody($this->createStream($jsonData))
        );

        $transport = $this->transport->withOutputType(SomeValueObject::class);
        $result = $transport($request);

        self::assertEquals($valueObject, $result);
    }

    /** @test */
    public function it_can_handle_requests_without_request_object(): void
    {
        $valueObject = new SomeValueObject('Hello', 'World');
        $jsonData = Json\encode($data = ['x' => 'Hello', 'y' => 'World']);
        $request = $this->createToolsRequest('GET', '/', []);

        $this->client->on(
            new CallbackRequestMatcher(
                fn (RequestInterface $httpRequest): bool => '' === (string) $httpRequest->getBody()
            ),
            $this->createResponse()->withBody($this->createStream($jsonData))
        );

        $transport = $this->transport->withOutputType(SomeValueObject::class);
        $result = $transport($request);

        self::assertEquals($valueObject, $result);
    }

    /** @test */
    public function it_can_handle_requests_without_response_type(): void
    {
        $valueObject = new SomeValueObject('Hello', 'World');
        $jsonData = Json\encode($data = ['x' => 'Hello', 'y' => 'World']);
        $request = $this->createToolsRequest('GET', '/', [], $valueObject);

        $this->client->on(
            new CallbackRequestMatcher(
                fn (RequestInterface $httpRequest): bool => (string) $httpRequest->getBody() === $jsonData
            ),
            $this->createResponse()->withBody($this->createStream($jsonData))
        );

        $transport = $this->transport;
        $result = $transport($request);

        self::assertEquals(null, $result);
    }
}
