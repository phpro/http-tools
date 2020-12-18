<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Transport\IO\Input;

use Phpro\HttpTools\Encoding\Raw\RawEncoder;
use Phpro\HttpTools\Test\UseHttpToolsFactories;
use Phpro\HttpTools\Transport\IO\Input\EncodingRequestConverter;
use Phpro\HttpTools\Uri\RawUriBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class EncodingRequestConverterTest extends TestCase
{
    use UseHttpToolsFactories;

    /** @test */
    public function it_can_convert_a_request_to_a_psr_request(): void
    {
        $converter = EncodingRequestConverter::createWithAutodiscoveredPsrFactories(
            RawUriBuilder::createWithAutodiscoveredPsrFactories(),
            RawEncoder::createWithAutodiscoveredPsrFactories()
        );

        $psrRequest = $converter($this->createToolsRequest('GET', '/users', [], 'hello'));

        self::assertInstanceOf(RequestInterface::class, $psrRequest);
        self::assertSame('GET', $psrRequest->getMethod());
        self::assertSame('/users', (string) $psrRequest->getUri());
        self::assertSame('hello', (string) $psrRequest->getBody());
    }
}
