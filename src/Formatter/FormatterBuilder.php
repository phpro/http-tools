<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Formatter;

use Closure;
use Http\Message\Formatter;
use Phpro\HttpTools\Formatter\Factory\BasicFormatterFactory;
use Psl\Fun;

/**
 * @psalm-type Decorator = \Closure(Formatter): Formatter
 */
final class FormatterBuilder
{
    private bool $debug = false;
    private int $maxBodyLength = 1000;

    /**
     * @var list<Decorator>
     */
    private array $decorators = [];

    public static function default(
    ): FormatterBuilder {
        return new self();
    }

    public function withDebug(bool $debug = true): self
    {
        $this->debug = $debug;

        return $this;
    }

    public function withMaxBodyLength(int $maxBodyLength): self
    {
        $this->maxBodyLength = $maxBodyLength;

        return $this;
    }

    /**
     * @param Decorator $decorator
     */
    public function addDecorator(Closure $decorator): self
    {
        $this->decorators[] = $decorator;

        return $this;
    }

    public function build(): Formatter
    {
        return Fun\pipe(...$this->decorators)(
            BasicFormatterFactory::create($this->debug, $this->maxBodyLength)
        );
    }
}
