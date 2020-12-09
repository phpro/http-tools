<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Exception;

use Throwable;

abstract class RuntimeException extends \RuntimeException
{
    protected function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
