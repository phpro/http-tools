<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Formatter;

use Http\Message\Formatter;
use Phpro\HttpTools\Formatter\RemoveSensitiveJsonKeysFormatter;
use Phpro\HttpTools\Test\UseHttpFactories;
use Phpro\HttpTools\Tests\Helper\Formatter\CallbackFormatter;
use PHPUnit\Framework\TestCase;
use Psl\Json;
use Psr\Http\Message\MessageInterface;

final class RemoveSensitiveJsonKeysFormatterTest extends TestCase
{
    use UseHttpFactories;

    private RemoveSensitiveJsonKeysFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new RemoveSensitiveJsonKeysFormatter(
            new CallbackFormatter(fn (MessageInterface $message) => $message->getBody()->__toString()),
            ['password', 'refreshToken']
        );
    }

    /**
     * @test
     *
     * @dataProvider provideJsonExpectations
     */
    public function it_can_remove_sensitive_json_keys_from_request(array $content, array $expected): void
    {
        $request = $this->createRequest('GET', 'something')
            ->withBody(
                $this->createStream(Json\encode($content))
            );

        $formatted = $this->formatter->formatRequest($request);

        self::assertSame($expected, Json\decode($formatted, true));
    }

    /**
     * @test
     *
     * @dataProvider provideJsonExpectations
     */
    public function it_can_remove_sensitive_json_keys_from_response(array $content, array $expected): void
    {
        $response = $this->createResponse(200)
            ->withBody(
                $this->createStream(Json\encode($content))
            );

        $formatted = $this->formatter->formatResponse($response);

        self::assertSame($expected, Json\decode($formatted, true));
    }

    /**
     * @test
     *
     * @dataProvider provideJsonExpectations
     */
    public function it_can_remove_sensitive_json_keys_from_response_with_request_context(
        array $content,
        array $expected
    ): void {
        $request = $this->createRequest('GET', 'something')
            ->withBody(
                $this->createStream(Json\encode($content))
            );

        $response = $this->createResponse(200)
            ->withBody(
                $this->createStream(Json\encode($content))
            );

        $formatted = $this->formatter->formatResponseForRequest($response, $request);

        self::assertSame($expected, Json\decode($formatted, true));
    }

    /**
     * @test
     *
     * @dataProvider provideJsonExpectations
     */
    public function it_can_remove_sensitive_json_keys_from_response_with_request_context_if_base_method_does_not_exist(
        array $content,
        array $expected
    ): void {
        $request = $this->createRequest('GET', 'something')
            ->withBody(
                $this->createStream(Json\encode($content))
            );

        $response = $this->createResponse(200)
            ->withBody(
                $this->createStream(Json\encode($content))
            );

        $baseFormatter = $this->getMockBuilder(Formatter::class)
            ->onlyMethods(['formatResponse', 'formatRequest'])
            ->getMock();

        $baseFormatter
            ->method('formatResponse')
            ->with($response)
            ->willReturn(Json\encode($expected));

        $formatter = new RemoveSensitiveJsonKeysFormatter(
            $baseFormatter,
            ['password', 'refreshToken']
        );

        $formatted = $formatter->formatResponseForRequest($response, $request);

        self::assertSame($expected, Json\decode($formatted, true));
    }

    public function provideJsonExpectations()
    {
        yield 'sample1' => [
            [
                'hello' => 'world',
                'password' => 'secret',
                'nested' => [
                    'refreshToken' => 'also-secret',
                    'newPassword' => 'notwhitelisted',
                ],
            ],
            [
                'hello' => 'world',
                'password' => 'xxxx',
                'nested' => [
                    'refreshToken' => 'xxxx',
                    'newPassword' => 'notwhitelisted',
                ],
            ],
        ];
    }
}
