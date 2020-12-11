<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Serializer;

use Phpro\HttpTools\Exception\RuntimeException;
use Phpro\HttpTools\Serializer\SerializerException;
use PHPUnit\Framework\TestCase;

final class SerializerExceptionTest extends TestCase
{
    /** @test */
    public function it_can_throw_exception_on_unkown_type(): void
    {
        $exception = SerializerException::noDeserializeTypeSpecified();
        self::assertInstanceOf(RuntimeException::class, $exception);

        $this->expectExceptionObject($exception);
        throw $exception;
    }
}
