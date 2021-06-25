<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Helper\Formatter;

use Http\Message\Formatter as HttpFormatter;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class CallbackFormatter implements HttpFormatter
{
    /**
     * @var callable(MessageInterface): string
     */
    private $callback;

    /**
     * @param callable(MessageInterface): string $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function formatRequest(RequestInterface $request): string
    {
        return ($this->callback)($request);
    }

    public function formatResponse(ResponseInterface $response): string
    {
        return ($this->callback)($response);
    }
}
