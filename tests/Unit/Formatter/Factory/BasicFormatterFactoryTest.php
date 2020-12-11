<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Formatter\Factory;

use Http\Message\Formatter\FullHttpMessageFormatter;
use Http\Message\Formatter\SimpleFormatter;
use Phpro\HttpTools\Formatter\Factory\BasicFormatterFactory;
use PHPUnit\Framework\TestCase;

final class BasicFormatterFactoryTest extends TestCase
{
    /** @test */
    public function it_creates_a_simple_formatter_when_not_in_debug_mode(): void
    {
        $formatter = BasicFormatterFactory::create(false);
        self::assertInstanceOf(SimpleFormatter::class, $formatter);
    }

    /** @test */
    public function it_creates_a_full_formatter_when_in_debug_mode(): void
    {
        $formatter = BasicFormatterFactory::create(true, 2000);
        self::assertInstanceOf(FullHttpMessageFormatter::class, $formatter);
    }
}
