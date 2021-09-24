<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Formatter;

use Phpro\HttpTools\Formatter\RemoveSensitiveHeadersFormatter;
use Phpro\HttpTools\Test\UseHttpFactories;
use Phpro\HttpTools\Tests\Helper\Formatter\CallbackFormatter;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;

final class RemoveSensitiveHeaderKeysFormatterTest extends TestCase
{
    use UseHttpFactories;

    private RemoveSensitiveHeadersFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new RemoveSensitiveHeadersFormatter(
            new CallbackFormatter(
                fn (MessageInterface $message): string => $this->formatHeaders($message->getHeaders())
            ),
            ['X-API-Key', 'X-API-Secret']
        );
    }

    /**
     * @test
     * @dataProvider provideJsonExpectations
     */
    public function it_can_remove_sensitive_keys_from_request(array $headers, array $expected): void
    {
        $request = $this->createRequest('GET', 'something');
        foreach ($headers as $name => $value) {
            $request = $request->withAddedHeader($name, $value);
        }
        $formatted = $this->formatter->formatRequest($request);

        self::assertSame($this->formatHeaders($expected), $formatted);
    }

    /**
     * @test
     * @dataProvider provideJsonExpectations
     */
    public function it_can_remove_sensitive_keys_from_response(array $headers, array $expected): void
    {
        $response = $this->createResponse(200);
        foreach ($headers as $name => $value) {
            $response = $response->withAddedHeader($name, $value);
        }
        $formatted = $this->formatter->formatResponse($response);

        self::assertSame($this->formatHeaders($expected), $formatted);
    }

    public function provideJsonExpectations()
    {
        yield 'sample1' => [
            [
                'Hello' => 'World',
                'Hi' => 'Toon',
                'X-API-Key' => 'secret',
                'X-API-Secret' => 'also-secret',
            ],
            [
                'Hello' => 'World',
                'Hi' => 'Toon',
                'X-API-Key' => 'xxxx',
                'X-API-Secret' => 'xxxx',
            ],
        ];
    }

    private function formatHeaders(array $headers): string
    {
        return implode(PHP_EOL, array_map(
            fn (string $key, $values): string => implode(PHP_EOL, array_map(
                fn (string $value): string => $key.': '.$value,
                is_array($values) ? $values : [$values]
            )),
            array_keys($headers),
            $headers
        ));
    }
}
