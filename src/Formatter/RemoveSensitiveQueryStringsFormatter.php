<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Formatter;

use Http\Message\Formatter as HttpFormatter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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

    /** @psalm-suppress DeprecatedMethod */
    public function formatResponse(ResponseInterface $response): string
    {
        return $this->formatter->formatResponse($response);
    }

    public function formatResponseForRequest(ResponseInterface $response, RequestInterface $request): string
    {
        if (!method_exists($this->formatter, 'formatResponseForRequest')) {
            return $this->formatResponse($response);
        }

        return $this->formatter->formatResponseForRequest($response, $request);
    }

    private function removeQueryStrings(RequestInterface $request): string
    {
        $uri = $request->getUri();
        $query = $uri->getQuery();

        $result = [];
        parse_str($query, $result);

        foreach ($this->sensitiveKeys as $key) {
            if (!array_key_exists($key, $result)) {
                continue;
            }

            $result[$key] = 'xxxx';
        }

        return $this->formatter->formatRequest(
            $request->withUri(
                $uri->withQuery(http_build_query($result))
            )
        );
    }
}
