<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Formatter;

use Http\Message\Formatter;
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
     *
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

    /**
     * @test
     *
     * @dataProvider provideJsonExpectations
     */
    public function it_can_format_a_response_with_request_context(): void
    {
        $request = $this->createRequest('GET', '/something');
        $response = $this->createResponse();

        self::assertIsString(
            $this->formatter->formatResponseForRequest($response, $request)
        );
    }

    /**
     * @test
     *
     * @dataProvider provideJsonExpectations
     */
    public function it_can_format_a_response_with_request_context_if_base_method_does_not_exist(): void
    {
        $request = $this->createRequest('GET', '/something');
        $response = $this->createResponse();

        $baseFormatter = $this->getMockBuilder(Formatter::class)
            ->onlyMethods(['formatResponse', 'formatRequest'])
            ->getMock();

        $baseFormatter
            ->method('formatResponse')
            ->with($response)
            ->willReturn($response->getBody()->__toString());

        $formatter = new RemoveSensitiveQueryStringsFormatter(
            $baseFormatter,
            ['apiKey', 'token']
        );

        self::assertIsString(
            $formatter->formatResponseForRequest($response, $request)
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
