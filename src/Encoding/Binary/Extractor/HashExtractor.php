<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Encoding\Binary\Extractor;

use Psl\Hash\Algorithm;
use Psl\Hash\Context;
use Psr\Http\Message\ResponseInterface;

final class HashExtractor
{
    public function __construct(
        private readonly Algorithm $algorithm
    ) {
    }

    public function __invoke(ResponseInterface $response): string
    {
        $body = $response->getBody();

        $pos = $body->tell();
        if ($pos > 0) {
            $body->rewind();
        }

        $context = Context::forAlgorithm($this->algorithm);
        while (!$body->eof()) {
            $context = $context->update($body->read(1048576));
        }

        $body->seek($pos);

        return $context->finalize();
    }
}
