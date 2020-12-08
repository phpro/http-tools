<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Helper\Vcr;

use Http\Client\Plugin\Vcr\NamingStrategy\NamingStrategyInterface;
use Psr\Http\Message\RequestInterface;

final class FactoryAwareNamingStrategy implements NamingStrategyInterface
{
    private string $forFactory;
    private NamingStrategyInterface $namingStrategy;

    public function __construct(string $forFactory, NamingStrategyInterface $namingStrategy)
    {
        $this->forFactory = $forFactory;
        $this->namingStrategy = $namingStrategy;
    }

    public function name(RequestInterface $request): string
    {
        return $this->forFactory.'-'.$this->namingStrategy->name($request);
    }
}
