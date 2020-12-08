<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Uri;

use Phpro\HttpTools\Test\UseHttpToolsFactories;
use Phpro\HttpTools\Uri\TemplatedUriBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

/**
 * @covers \Phpro\HttpTools\Uri\TemplatedUriBuilder
 * @covers \Phpro\HttpTools\Test\UseHttpToolsFactories
 */
final class TemplatedUriBuilderTest extends TestCase
{
    use UseHttpToolsFactories;

    private TemplatedUriBuilder $uriBuilder;

    protected function setUp(): void
    {
        $this->uriBuilder = new TemplatedUriBuilder(['default' => 'yes']);
    }

    /** @test */
    public function it_can_build_a_templated_uri(): void
    {
        $request = $this->createToolsRequest('GET', '/hello/{name}', ['name' => 'world']);
        $uri = ($this->uriBuilder)($request);

        self::assertInstanceOf(UriInterface::class, $uri);
        self::assertSame('/hello/world', $uri->__toString());
    }

    /** @test */
    public function it_can_build_a_templated_uri_with_default_params(): void
    {
        $request = $this->createToolsRequest('GET', '/hello/{default}', ['name' => 'world']);
        $uri = ($this->uriBuilder)($request);

        self::assertInstanceOf(UriInterface::class, $uri);
        self::assertSame('/hello/yes', $uri->__toString());
    }
}
