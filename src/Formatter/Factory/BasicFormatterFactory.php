<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Formatter\Factory;

use Http\Message\Formatter;
use Http\Message\Formatter\FullHttpMessageFormatter;
use Http\Message\Formatter\SimpleFormatter;

final class BasicFormatterFactory
{
    public static function create(bool $debug, int $maxBodyLength = 1000): Formatter
    {
        return $debug ? new SimpleFormatter() : new FullHttpMessageFormatter($maxBodyLength);
    }
}
