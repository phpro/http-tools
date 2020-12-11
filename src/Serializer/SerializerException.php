<?php

declare(strict_types=1);

namespace Phpro\HttpTools\Serializer;

use Phpro\HttpTools\Exception\RuntimeException;

final class SerializerException extends RuntimeException
{
    public static function noDeserializeTypeSpecified(): self
    {
        return new self(
            'No deserialization output type was specified.'
        );
    }
}
