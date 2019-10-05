<?php

namespace Maestro\Extension\Runner\Logger;

use Bramus\Monolog\Formatter\ColoredLineFormatter;

class MaestroColoredLineFormatter extends ColoredLineFormatter
{
    private $start;

    public function format(array $record): string
    {
        if (null === $this->start) {
            $this->start = microtime(true);
        }

        $record['elapsed'] = number_format(microtime(true) - $this->start, 4);

        return parent::format($record);
    }
}
