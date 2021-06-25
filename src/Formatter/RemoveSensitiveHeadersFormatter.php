<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Formatter;

use Http\Message\Formatter as HttpFormatter;
use function preg_quote;
use Psl\Regex;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class RemoveSensitiveHeadersFormatter implements HttpFormatter
{
    private HttpFormatter $formatter;

    /**
     * @var non-empty-list<string>
     */
    private array $sensitiveHeaders;

    /**
     * @param non-empty-list<string> $sensitiveHeaders
     */
    public function __construct(HttpFormatter $formatter, array $sensitiveHeaders)
    {
        $this->formatter = $formatter;
        $this->sensitiveHeaders = $sensitiveHeaders;
    }

    public function formatRequest(RequestInterface $request): string
    {
        return $this->removeCredentials(
            $this->formatter->formatRequest($request)
        );
    }

    public function formatResponse(ResponseInterface $response): string
    {
        return $this->removeCredentials(
            $this->formatter->formatResponse($response)
        );
    }

    private function removeCredentials(string $info): string
    {
        return array_reduce(
            $this->sensitiveHeaders,
            /** @psalm-suppress InvalidReturnStatement, InvalidReturnType */
            fn (string $sensitiveData, string $header): string => Regex\replace(
                $sensitiveData,
                '{^('.preg_quote($header, '{').')\:(.*)}im',
                '$1: xxxx',
            ),
            $info
        );
    }
}
