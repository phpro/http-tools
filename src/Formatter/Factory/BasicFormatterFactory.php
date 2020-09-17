<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Formatter\Factory;

use Http\Message\Formatter;
use Http\Message\Formatter\FullHttpMessageFormatter;
use Http\Message\Formatter\SimpleFormatter;

final class BasicFormatterFactory
{
    private int $maxBodyLength;

    public function __construct(int $maxBodyLength = 1000)
    {
        $this->maxBodyLength = $maxBodyLength;
    }

    public function __invoke(bool $debug): Formatter
    {
        return $debug ? new SimpleFormatter() : new FullHttpMessageFormatter($this->maxBodyLength);
    }
}
