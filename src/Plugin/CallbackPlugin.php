<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Plugin;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;

/**
 * @psalm-type Step = callable(RequestInterface): Promise
 * @psalm-type PluginCallback = callable(RequestInterface, Step, Step): Promise
 */
final class CallbackPlugin implements Plugin
{
    /**
     * @var PluginCallback
     */
    private $callback;

    /**
     * @param PluginCallback $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param Step $next
     * @param Step $first
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        return ($this->callback)($request, $next, $first);
    }
}
