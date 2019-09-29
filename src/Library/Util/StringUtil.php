<?php

namespace Maestro\Library\Util;

use RuntimeException;

class StringUtil
{
    public static function lastLine(string $input): string
    {
        if (empty($input)) {
            return $input;
        }

        $lines = array_filter(self::splitString($input));
        return (string) $lines[array_key_last($lines)];
    }

    public static function firstLine(string $input): string
    {
        if (empty($input)) {
            return $input;
        }
        $splitted = array_filter(self::splitString($input));
        return (string) $splitted[array_key_first($splitted)];
    }

    private static function splitString(string $input): array
    {
        $splitted = preg_split('{\R}', $input);
        if (false === $splitted) {
            throw new RuntimeException(sprintf(
                'Could not split string "%s"',
                $input
            ));
        }
        return $splitted;
    }
}
