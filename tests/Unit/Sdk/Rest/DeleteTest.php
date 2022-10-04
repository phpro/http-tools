<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Sdk\Rest;

use Http\Message\RequestMatcher\CallbackRequestMatcher;
use Http\Mock\Client;
use Phpro\HttpTools\Sdk\HttpResource;
use Phpro\HttpTools\Sdk\Rest\DeleteTrait;
use Phpro\HttpTools\Test\UseMockClient;
use Phpro\HttpTools\Transport\Presets\JsonPreset;
use Phpro\HttpTools\Uri\RawUriBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class DeleteTest extends TestCase
{
    use UseMockClient;

    private Client $client;
    private HttpResource $resource;

    protected function setUp(): void
    {
        $this->client = $this->mockClient();
        $transport = JsonPreset::sync($this->client, RawUriBuilder::createWithAutodiscoveredPsrFactories());
        $this->resource = new class($transport) extends HttpResource {
            use DeleteTrait;

            protected function path(): string
            {
                return '/users';
            }
        };
    }

    /** @test */
    public function it_can_delete_a_resource(): void
    {
        $responseData = [];

        $this->client->on(
            new CallbackRequestMatcher(
                static fn (RequestInterface $request) => 'DELETE' === $request->getMethod()
                        && '/users/1' === (string) $request->getUri()
                        && '' === (string) $request->getBody()
            ),
            $this->createResponse()
                ->withBody(
                    $this->createStream('')
                )
        );

        $actual = $this->resource->delete('1');

        self::assertSame($responseData, $actual);
    }
}
