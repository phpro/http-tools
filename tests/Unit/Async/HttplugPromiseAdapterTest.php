<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Async;

use function Amp\call;

use Amp\Promise;

use function Amp\Promise\wait;

use Http\Promise\FulfilledPromise;
use Http\Promise\RejectedPromise;
use Phpro\HttpTools\Async\HttplugPromiseAdapter;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;

final class HttplugPromiseAdapterTest extends TestCase
{
    use UseHttpFactories;

    /** @test */
    public function it_can_wrap_successfull_promises(): void
    {
        $response = $this->createResponse(200);
        $success = new FulfilledPromise($response);
        $promise = HttplugPromiseAdapter::adapt($success);

        self::assertInstanceOf(Promise::class, $promise);
        $promise->onResolve(fn ($throwable, $result) => $result);
        $actual = wait($promise);

        self::assertSame($response, $actual);
    }

    /** @test */
    public function it_can_wrap_failing_promises(): void
    {
        $error = $this->createEmptyHttpClientException('nope');
        $success = new RejectedPromise($error);
        $promise = HttplugPromiseAdapter::adapt($success);

        self::assertInstanceOf(Promise::class, $promise);

        $result = wait(call(static function () use ($promise, $error) {
            try {
                yield $promise;
                self::assertFalse(true, 'The promise did not fail!');
            } catch (ClientExceptionInterface $actual) {
                self::assertSame($error, $actual);
            }

            return 'ok';
        }));

        self::assertSame('ok', $result);
    }
}
