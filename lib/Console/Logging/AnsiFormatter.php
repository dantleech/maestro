<?php

namespace Maestro\Console\Logging;

use Monolog\Formatter\FormatterInterface;

class AnsiFormatter implements FormatterInterface
{
    /**
     * {@inheritDoc}
     */
    public function format(array $record)
    {
        return sprintf(
            '[%s] %s' . PHP_EOL,
            $this->formatLevel($record['level_name']),
            $this->formatMessage($record['message'])
        );
    }

    /**
     * {@inheritDoc}
     */
    public function formatBatch(array $records)
    {
    }

    private function formatLevel(string $level)
    {
        $color = 32;

        switch ($level) {
            case 'INFO':
                $color = 32;
                break;
            case 'DEBUG':
                $color = 35;
                break;
            case 'ERROR':
                $color = 31;
                break;
        }

        return sprintf("\033[%sm%s\033[0m", $color, $level);
    }

    private function formatMessage(string $message)
    {
        return $message;
    }
}
