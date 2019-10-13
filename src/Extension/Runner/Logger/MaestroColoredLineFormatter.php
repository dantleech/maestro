<?php

namespace Maestro\Extension\Runner\Logger;

use Bramus\Monolog\Formatter\ColoredLineFormatter;

class MaestroColoredLineFormatter extends ColoredLineFormatter
{
    private $start;

    public function __construct($colorScheme = null, $format = null, $dateFormat = null, $allowInlineLineBreaks = false, $ignoreEmptyContextAndExtra = false)
    {
        parent::__construct($colorScheme, $format, $dateFormat, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra);
        $this->start = microtime(true);
    }

    public function format(array $record): string
    {
        $record['elapsed'] = number_format(microtime(true) - $this->start, 4);

        return parent::format($record);
    }

    public function stringify($value)
    {
        return trim($this->convertToString($value));
    }
}
