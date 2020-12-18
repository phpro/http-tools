<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Transport\IO\Input;

use Phpro\HttpTools\Request\RequestInterface;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;

/**
 * @template RequestType
 */
interface RequestConverterInterface
{
    /**
     * @param RequestInterface<RequestType> $request
     */
    public function __invoke(RequestInterface $request): PsrRequestInterface;
}
