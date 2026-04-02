<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Tests\Unit\Encoding\ContentType;

use Phpro\HttpTools\Encoding\ContentType\ContentTypeAwareEncoder;
use Phpro\HttpTools\Encoding\ContentType\ContentTypeAwarePayload;
use Phpro\HttpTools\Encoding\Raw\RawEncoder;
use Phpro\HttpTools\Test\UseHttpFactories;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ContentTypeAwareEncoderTest extends TestCase
{
    use UseHttpFactories;

    #[Test]
    public function it_can_encode_with_content_type(): void
    {
        $encoder = new ContentTypeAwareEncoder(
            RawEncoder::createWithAutodiscoveredPsrFactories()
        );
        $request = $this->createRequest('POST', '/hello');
        $payload = new ContentTypeAwarePayload('application/pdf', 'raw-content');

        $actual = $encoder($request, $payload);

        self::assertSame('raw-content', (string) $actual->getBody());
        self::assertSame('application/pdf', $actual->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function it_overrides_content_type_set_by_inner_encoder(): void
    {
        $encoder = new ContentTypeAwareEncoder(
            RawEncoder::createWithAutodiscoveredPsrFactories()
        );
        $request = $this->createRequest('POST', '/hello')
            ->withHeader('Content-Type', 'text/plain');
        $payload = new ContentTypeAwarePayload('application/xml', 'raw-content');

        $actual = $encoder($request, $payload);

        self::assertSame('application/xml', $actual->getHeaderLine('Content-Type'));
    }
}
