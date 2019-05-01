<?php

namespace Maestro\Model\Util;

use RuntimeException;

class StringUtil
{
    public static function lastLine(string $input): string
    {
        $splitted = preg_split('{\R}', $input);
        if (false === $splitted) {
            throw new RuntimeException(sprintf(
                'Could not split string "%s"',
                $input
            ));
        }
        $lines = array_filter(array_reverse($splitted));

        return (string) reset($lines);
    }

    public static function removeNonPrintableChars(string $input): string
    {
        return (string) preg_replace('{[[:^print:]]}', ' ', $input);
    }
}
