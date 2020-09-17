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
    private array $sensitiveJsonKeyks;

    /**
     * @param non-empty-list<string> $sensitiveJsonKeyks
     */
    public function __construct(HttpFormatter $formatter, array $sensitiveJsonKeyks)
    {
        $this->formatter = $formatter;
        $this->sensitiveJsonKeyks = $sensitiveJsonKeyks;
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
            $this->sensitiveJsonKeyks,
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
