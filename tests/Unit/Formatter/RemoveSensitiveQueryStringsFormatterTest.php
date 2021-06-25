<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Formatter;

use Phpro\HttpTools\Formatter\RemoveSensitiveQueryStringsFormatter;
use Phpro\HttpTools\Test\UseHttpFactories;
use Phpro\HttpTools\Tests\Helper\Formatter\CallbackFormatter;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;

final class RemoveSensitiveQueryStringsFormatterTest extends TestCase
{
    use UseHttpFactories;

    private RemoveSensitiveQueryStringsFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new RemoveSensitiveQueryStringsFormatter(
            new CallbackFormatter(
                fn (MessageInterface $message): string => $message->getBody()->__toString()
            ),
            ['apiKey', 'token']
        );
    }

    /**
     * @test
     * @dataProvider provideJsonExpectations
     */
    public function it_can_remove_sensitive_query_strings_from_request(
        string $actual,
        string $expected
    ): void {
        $request = $this->createRequest('GET', $actual);
        $formatted = $this->formatter->formatRequest($request);

        self::assertStringContainsString(
            $expected,
            $formatted
        );
    }

    public function provideJsonExpectations(): iterable
    {
        yield 'sample' => [
            'https://testapi.com/api/v1/products?query=string',
            'https://testapi.com/api/v1/products?query=string',
            'https://testapi.com/api/v1/products?apiKey=ABCDEFGH123',
            'https://testapi.com/api/v1/products?apiKey=xxxx',
            'https://testapi.com/api/v1/products?apiKey=ABCDEFGH123&token=eyJzdWIiOiIxMjM0NTY3ODkwIiwibm',
            'https://testapi.com/api/v1/products?apiKey=xxxx&token=xxxx',
        ];
    }
}
