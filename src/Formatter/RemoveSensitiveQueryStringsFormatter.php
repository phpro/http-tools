<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Formatter;

use Http\Message\Formatter as HttpFormatter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function Safe\preg_replace;

final class RemoveSensitiveQueryStringsFormatter implements HttpFormatter
{
    private HttpFormatter $formatter;

    /**
     * @var non-empty-list<string>
     */
    private array $sensitiveKeys;

    /**
     * @param non-empty-list<string> $sensitiveKeys
     */
    public function __construct(
        HttpFormatter $formatter,
        array $sensitiveKeys
    ) {
        $this->formatter = $formatter;
        $this->sensitiveKeys = $sensitiveKeys;
    }

    public function formatRequest(RequestInterface $request): string
    {
        return $this->removeQueryStrings($request);
    }

    public function formatResponse(ResponseInterface $response): string
    {
        return $this->formatter->formatResponse($response);
    }

    private function removeQueryStrings(RequestInterface $request): string
    {
        $uri = $request->getUri();
        $query = $uri->getQuery();

        foreach ($this->sensitiveKeys as $key) {
            /** @var string $query */
            $query = preg_replace(
                sprintf('/(%1$s\=)[a-zA-Z0-9. ]+/', $key),
                '$1xxxx',
                $query
            );
        }

        return $this->formatter->formatRequest(
            $request->withUri(
                $uri->withQuery($query)
            )
        );
    }
}
