<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Plugin;

use Phpro\HttpTools\Request\Request;
use Phpro\HttpTools\Request\RequestInterface;
use Phpro\HttpTools\Test\UseHttpToolsFactories;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    use UseHttpToolsFactories;

    /** @test */
    public function it_can_implement_requests(): void
    {
        $request = $this->createToolsRequest('GET', '/endpoint', ['param' => 'value'], 'body');

        self::assertInstanceOf(RequestInterface::class, $request);
        self::assertSame('GET', $request->method());
        self::assertSame('/endpoint', $request->uri());
        self::assertSame(['param' => 'value'], $request->uriParameters());
        self::assertSame('body', $request->body());
    }

    /** @test */
    public function it_can_use_a_preset_value_object(): void
    {
        $request = new Request('GET', '/endpoint', ['param' => 'value'], 'body');

        self::assertInstanceOf(RequestInterface::class, $request);
        self::assertSame('GET', $request->method());
        self::assertSame('/endpoint', $request->uri());
        self::assertSame(['param' => 'value'], $request->uriParameters());
        self::assertSame('body', $request->body());
    }
}
