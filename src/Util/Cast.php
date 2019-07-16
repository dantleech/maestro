<?php

namespace Maestro\Util;

use RuntimeException;

class Cast
{
    public static function toString($value)
    {
        if (!is_string($value)) {
            throw new RuntimeException(sprintf(
                'Expected string, got "%s"',
                gettype($value)
            ));
        }
        return $value;
    }

    public static function toStringOrNull($value = null)
    {
        if (null === $value) {
            return $value;
        }

        return self::toString($value);
    }

    public static function toInt($value): int
    {
        return (int) $value;
    }

    public static function toIntOrNull($value): ?int
    {
        if (null === $value) {
            return null;
        }

        return self::toInt($value);
    }

    public static function toBool($value): bool
    {
        return (bool) $value;
    }
}
