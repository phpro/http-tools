<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Sdk\Rest;

use Http\Message\RequestMatcher\CallbackRequestMatcher;
use Http\Mock\Client;
use Phpro\HttpTools\Sdk\HttpResource;
use Phpro\HttpTools\Sdk\Rest\GetTrait;
use Phpro\HttpTools\Test\UseMockClient;
use Phpro\HttpTools\Transport\Presets\JsonPreset;
use Phpro\HttpTools\Uri\RawUriBuilder;
use PHPUnit\Framework\TestCase;
use Psl\Json;
use Psr\Http\Message\RequestInterface;

final class GetTest extends TestCase
{
    use UseMockClient;

    private Client $client;
    private HttpResource $resource;

    protected function setUp(): void
    {
        $this->client = $this->mockClient();
        $transport = JsonPreset::sync($this->client, RawUriBuilder::createWithAutodiscoveredPsrFactories());
        $this->resource = new class($transport) extends HttpResource {
            use GetTrait;

            protected function path(): string
            {
                return '/users';
            }
        };
    }

    /** @test */
    public function it_can_get_a_resource(): void
    {
        $responseData = [['id' => 1], ['id' => 2]];

        $this->client->on(
            new CallbackRequestMatcher(
                static fn (RequestInterface $request) => 'GET' === $request->getMethod()
                    && '/users' === (string) $request->getUri()
                    && '' === (string) $request->getBody()
            ),
            $this->createResponse()
                ->withBody(
                     $this->createStream(Json\encode($responseData))
                 )
        );

        $actual = $this->resource->get();

        self::assertSame($responseData, $actual);
    }

    /** @test */
    public function it_can_get_a_resource_with_query_params(): void
    {
        $responseData = [['id' => 1], ['id' => 2]];

        $this->client->on(
            new CallbackRequestMatcher(
                static fn (RequestInterface $request) => 'GET' === $request->getMethod()
                     && '/users?param1=value1' === (string) $request->getUri()
                     && '' === (string) $request->getBody()
            ),
            $this->createResponse()
                ->withBody(
                     $this->createStream(Json\encode($responseData))
                 )
        );

        $actual = $this->resource->get(['param1' => 'value1']);

        self::assertSame($responseData, $actual);
    }
}
