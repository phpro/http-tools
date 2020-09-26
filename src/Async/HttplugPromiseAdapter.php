<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Async;

use Amp\Deferred;
use Amp\Loop;
use Amp\Promise as AmpPromise;
use Http\Promise\Promise as HttpPromise;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class HttplugPromiseAdapter
{
    /**
     * @return AmpPromise<ResponseInterface>
     */
    public static function adapt(HttpPromise $httpPromise): AmpPromise
    {
        /** @var Deferred<ResponseInterface> $deferred */
        $deferred = new Deferred();
        $httpPromise->then(
            static function (ResponseInterface $response) use ($deferred) {
                $deferred->resolve($response);

                return $response;
            },
            static function (Throwable $exception) use ($deferred): void {
                $deferred->fail($exception);
                throw $exception;
            },
        );

        // Await the HTTP promise in the next free tick:
        Loop::defer(static function () use ($httpPromise): void {
            $httpPromise->wait(false);
        });

        return $deferred->promise();
    }
}
