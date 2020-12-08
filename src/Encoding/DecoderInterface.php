<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding;

use Psr\Http\Message\ResponseInterface;

/**
 * @template DataType
 */
interface DecoderInterface
{
    /**
     * @return DataType
     */
    public function __invoke(ResponseInterface $response);
}
