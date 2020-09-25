<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Uri;

use Http\Discovery\Psr17FactoryDiscovery;
use Phpro\HttpTools\Tests\Helper\Request\SampleRequest;
use Phpro\HttpTools\Uri\RawUriBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

/**
 * @covers \Phpro\HttpTools\Uri\RawUriBuilder
 */
class RawUriBuilderTest extends TestCase
{
    private RawUriBuilder $uriBuilder;

    protected function setUp(): void
    {
        $this->uriBuilder = new RawUriBuilder(Psr17FactoryDiscovery::findUrlFactory());
    }

    /** @test */
    public function it_can_build_a_raw_uri(): void
    {
        $request = SampleRequest::createWithUri('/hello/world', []);
        $uri = ($this->uriBuilder)($request);

        self::assertInstanceOf(UriInterface::class, $uri);
        self::assertSame($request->uri(), $uri->__toString());
    }
}
