<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Uri;

use Phpro\HttpTools\Test\UseHttpToolsFactories;
use Phpro\HttpTools\Uri\RawUriBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

final class RawUriBuilderTest extends TestCase
{
    use UseHttpToolsFactories;

    private RawUriBuilder $uriBuilder;

    protected function setUp(): void
    {
        $this->uriBuilder = RawUriBuilder::createWithAutodiscoveredPsrFactories();
    }

    /** @test */
    public function it_can_build_a_raw_uri(): void
    {
        $request = $this->createToolsRequest('GET', '/hello/world', []);
        $uri = ($this->uriBuilder)($request);

        self::assertInstanceOf(UriInterface::class, $uri);
        self::assertSame($request->uri(), $uri->__toString());
    }
}
