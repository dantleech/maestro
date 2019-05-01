<?php

namespace Maestro\Console\Util;

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
}
