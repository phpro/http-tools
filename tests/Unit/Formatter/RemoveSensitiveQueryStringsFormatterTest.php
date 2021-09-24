<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Formatter;

use Http\Message\Formatter\SimpleFormatter;
use Phpro\HttpTools\Formatter\RemoveSensitiveQueryStringsFormatter;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\TestCase;

final class RemoveSensitiveQueryStringsFormatterTest extends TestCase
{
    use UseHttpFactories;

    private RemoveSensitiveQueryStringsFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new RemoveSensitiveQueryStringsFormatter(
            new SimpleFormatter(),
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

    /**
     * @test
     */
    public function it_can_format_a_response(): void
    {
        $response = $this->createResponse();

        self::assertIsString(
            $this->formatter->formatResponse($response)
        );
    }

    public function provideJsonExpectations(): iterable
    {
        yield 'regular' => [
            'https://testapi.com/api/v1/products?query=string',
            'https://testapi.com/api/v1/products?query=string',
        ];
        yield 'apiKey' => [
            'https://testapi.com/api/v1/products?apiKey=ABCDEFGH123',
            'https://testapi.com/api/v1/products?apiKey=xxxx',
        ];
        yield 'apiKeyAndToken' => [
            'https://testapi.com/api/v1/products?apiKey=ABCDEFGH123&token=eyJzdWIiOiIxMjM0NTY3ODkwIiwibm',
            'https://testapi.com/api/v1/products?apiKey=xxxx&token=xxxx',
        ];
    }
}
