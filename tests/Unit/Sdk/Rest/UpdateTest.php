<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Sdk\Rest;

use Http\Message\RequestMatcher\CallbackRequestMatcher;
use Http\Mock\Client;
use Phpro\HttpTools\Sdk\HttpResource;
use Phpro\HttpTools\Sdk\Rest\UpdateTrait;
use Phpro\HttpTools\Test\UseMockClient;
use Phpro\HttpTools\Transport\Presets\JsonPreset;
use Phpro\HttpTools\Uri\RawUriBuilder;
use PHPUnit\Framework\TestCase;
use Psl\Json;
use Psr\Http\Message\RequestInterface;

final class UpdateTest extends TestCase
{
    use UseMockClient;

    private Client $client;
    private HttpResource $resource;

    protected function setUp(): void
    {
        $this->client = $this->mockClient();
        $transport = JsonPreset::sync($this->client, RawUriBuilder::createWithAutodiscoveredPsrFactories());
        $this->resource = new class($transport) extends HttpResource {
            use UpdateTrait;

            protected function path(): string
            {
                return '/users';
            }
        };
    }

    /** @test */
    public function it_can_patch_a_resource(): void
    {
        $requestData = ['user' => 'a'];
        $responseData = ['id' => 1];

        $this->client->on(
            new CallbackRequestMatcher(
                static fn (RequestInterface $request) => 'PUT' === $request->getMethod()
                        && '/users/1' === (string) $request->getUri()
                        && (string) $request->getBody() === Json\encode($requestData)
            ),
            $this->createResponse()
                ->withBody(
                     $this->createStream(Json\encode($responseData))
                 )
        );

        $actual = $this->resource->update('1', $requestData);

        self::assertSame($responseData, $actual);
    }
}
