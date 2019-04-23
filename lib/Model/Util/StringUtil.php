<?php

namespace Maestro\Model\Util;

class StringUtil
{
    public static function extractAfterNewline(string $input): string
    {
        $lines = array_filter(array_reverse(preg_split('{\R}', $input)));
        return reset($lines);
    }

    public static function removeNonPrintableChars(string $input): string
    {
        return preg_replace('{[[:^print:]]}', ' ', $input);
    }
}
