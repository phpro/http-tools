<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Transport;

use function Amp\Promise\wait;

use Http\Promise\FulfilledPromise;
use Phpro\HttpTools\Test\UseHttpFactories;
use Phpro\HttpTools\Test\UseHttpToolsFactories;
use Phpro\HttpTools\Transport\CallbackTransport;
use PHPUnit\Framework\TestCase;

final class CallbackTransportTest extends TestCase
{
    use UseHttpFactories;
    use UseHttpToolsFactories;

    /** @test */
    public function it_can_send_sync_requests(): void
    {
        $request = $this->createToolsRequest('GET', '/users');
        $psrRequest = $this->createRequest('GET', '/users');
        $psrReponse = $this->createResponse();
        $expected = ['hello' => 'world'];

        $transport = new CallbackTransport(
            $this->when($request, $psrRequest),
            $this->when($psrRequest, $psrReponse),
            $this->when($psrReponse, $expected),
        );

        $actual = $transport($request);
        self::assertSame($expected, $actual);
    }

    /** @test */
    public function it_can_send_async_requests(): void
    {
        $request = $this->createToolsRequest('GET', '/users');
        $psrRequest = $this->createRequest('GET', '/users');
        $psrReponse = $this->createResponse();
        $promise = new FulfilledPromise($psrReponse);
        $expected = ['hello' => 'world'];

        $transport = new CallbackTransport(
            $this->when($request, $psrRequest),
            $this->when($psrRequest, $promise),
            $this->when($psrReponse, $expected),
        );

        $actual = wait($transport($request));
        self::assertSame($expected, $actual);
    }

    private function when($expected, $result)
    {
        return static function ($actual) use ($expected, $result) {
            self::assertSame($expected, $actual);

            return $result;
        };
    }
}
