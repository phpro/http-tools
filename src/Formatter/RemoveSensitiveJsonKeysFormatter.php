<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Formatter;

use Http\Message\Formatter as HttpFormatter;
use function preg_quote;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function Safe\preg_replace;

class RemoveSensitiveJsonKeysFormatter implements HttpFormatter
{
    private HttpFormatter $formatter;

    /**
     * @var non-empty-list<string>
     */
    private array $sensitiveJsonKeys;

    /**
     * @param non-empty-list<string> $sensitiveJsonKeys
     */
    public function __construct(HttpFormatter $formatter, array $sensitiveJsonKeys)
    {
        $this->formatter = $formatter;
        $this->sensitiveJsonKeys = $sensitiveJsonKeys;
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
        return (string) array_reduce(
            $this->sensitiveJsonKeys,
            /** @psalm-suppress InvalidReturnStatement, InvalidReturnType */
            fn (string $sensitiveData, string $jsonKey): string => preg_replace(
                '{"('.preg_quote($jsonKey, '{').')":\s*"([^"]*)"}i',
                '"$1": "xxxx"',
                $sensitiveData
            ),
            $info
        );
    }
}
