<?php

namespace Maestro\Util;

use RuntimeException;

class Cast
{
    public static function toString($string)
    {
        if (!is_string($string)) {
            throw new RuntimeException(sprintf(
                'Expected string, got "%s"',
                gettype($string)
            ));
        }
        return $string;
    }

    public static function toStringOrNull($string = null)
    {
        if (null === $string) {
            return $string;
        }

        return self::toString($string);
    }

    public static function toInt($value): int
    {
        return (int) $value;
    }
}
