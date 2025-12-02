<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Formatter;

use Http\Message\Formatter;
use Http\Message\Formatter\FullHttpMessageFormatter;
use Http\Message\Formatter\SimpleFormatter;
use Phpro\HttpTools\Formatter\FormatterBuilder;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psl\Ref;

final class FormatterBuilderTest extends TestCase
{
    use UseHttpFactories;

    #[Test]
    public function it_can_construct_default_builder(): void
    {
        $builder = new FormatterBuilder();
        $formatter = $builder->build();

        self::assertInstanceOf(Formatter::class, $formatter);
        self::assertInstanceOf(SimpleFormatter::class, $formatter);
    }

    #[Test]
    public function it_can_create_default_builder_via_static_method(): void
    {
        $builder = FormatterBuilder::default();
        $formatter = $builder->build();

        self::assertInstanceOf(Formatter::class, $formatter);
        self::assertInstanceOf(SimpleFormatter::class, $formatter);
    }

    #[Test]
    public function it_can_enable_debug_mode(): void
    {
        $builder = new FormatterBuilder();
        $builder->withDebug(true);
        $formatter = $builder->build();

        self::assertInstanceOf(FullHttpMessageFormatter::class, $formatter);
    }

    #[Test]
    public function it_can_disable_debug_mode(): void
    {
        $builder = new FormatterBuilder();
        $builder->withDebug(false);
        $formatter = $builder->build();

        self::assertInstanceOf(SimpleFormatter::class, $formatter);
    }

    #[Test]
    public function it_can_set_max_body_length(): void
    {
        $builder = new FormatterBuilder();
        $builder->withDebug(true)->withMaxBodyLength(500);
        $formatter = $builder->build();

        self::assertInstanceOf(FullHttpMessageFormatter::class, $formatter);

        // Create a request with a body longer than 500 characters
        $longBody = str_repeat('A', 600);
        $request = $this->createRequest('POST', '/test')
            ->withBody($this->createStream($longBody));

        $formatted = $formatter->formatRequest($request);

        // The formatted output should truncate the body
        self::assertStringNotContainsString(str_repeat('A', 600), $formatted);
    }

    #[Test]
    public function it_can_add_decorator(): void
    {
        $decoratorCalled = new Ref(false);
        $builder = new FormatterBuilder();

        $builder->addDecorator(function (Formatter $formatter) use ($decoratorCalled): Formatter {
            $decoratorCalled->value = true;

            return $formatter;
        });

        $formatter = $builder->build();

        self::assertTrue($decoratorCalled->value);
        self::assertInstanceOf(Formatter::class, $formatter);
    }

    #[Test]
    public function it_can_add_multiple_decorators(): void
    {
        $callOrder = new Ref([]);
        $builder = new FormatterBuilder();

        $builder->addDecorator(function (Formatter $formatter) use ($callOrder): Formatter {
            $callOrder->value[] = 'first';

            return $formatter;
        });

        $builder->addDecorator(function (Formatter $formatter) use ($callOrder): Formatter {
            $callOrder->value[] = 'second';

            return $formatter;
        });

        $formatter = $builder->build();

        self::assertSame(['first', 'second'], $callOrder->value);
        self::assertInstanceOf(Formatter::class, $formatter);
    }

    #[Test]
    public function it_can_decorate_formatter_with_custom_wrapper(): void
    {
        $builder = new FormatterBuilder();

        $builder->addDecorator(function (Formatter $baseFormatter): Formatter {
            $mockFormatter = $this->createMock(Formatter::class);

            $mockFormatter->expects(self::once())
                ->method('formatRequest')
                ->willReturnCallback(fn ($request) => '[CUSTOM] '.$baseFormatter->formatRequest($request));

            return $mockFormatter;
        });

        $formatter = $builder->build();
        $request = $this->createRequest('GET', '/test');
        $formatted = $formatter->formatRequest($request);

        self::assertStringStartsWith('[CUSTOM]', $formatted);
    }
}
