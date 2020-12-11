<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Helper\Model;

final class SomeValueObject
{
    public string $x;
    public string $y;

    public function __construct(string $x, string $y)
    {
        $this->x = $x;
        $this->y = $y;
    }
}
