<?php

namespace Maestro\Util;

use RuntimeException;

class StringUtil
{
    public static function lastLine(string $input): string
    {
        $lines = array_filter(array_reverse(self::splitString($input)));
        return (string) $lines[array_key_first($lines)];
    }

    public static function firstLine(string $input): string
    {
        $splitted = self::splitString($input);
        return (string) $splitted[array_key_last($splitted)];
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
