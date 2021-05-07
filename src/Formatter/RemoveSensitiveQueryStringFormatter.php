<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Formatter;

use Http\Message\Formatter as HttpFormatter;
use function preg_quote;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function Safe\preg_replace;

final class RemoveSensitiveDataFormatter implements HttpFormatter
{
    /**
     * @param non-empty-list<string> $sensitiveKeys
     */
    public function __construct(
        private HttpFormatter $formatter,
        private array $sensitiveKeys
    ) {
    }

    public function formatRequest(RequestInterface $request): string
    {
        return $this->removeQueryString(
            $this->formatter->formatRequest($request)
        );
    }

    public function formatResponse(ResponseInterface $response): string
    {
        return $this->removeQueryString(
            $this->formatter->formatResponse($response)
        );
    }

    private function removeQueryString(string $info): string
    {
        foreach ($this->sensitiveKeys as $key) {
            /** @var string $info */
            $info = preg_replace(
                sprintf('/((\?|\&)(%1$s\=))[a-zA-Z0-9. ]+/', $key),
                '$1XXXX',
                $info
            );
        }

        return $info;
    }
}
