<?php

namespace Maestro\Console\Progress;

use Maestro\Model\Job\QueueMonitor;
use Maestro\Model\Job\QueueState;
use Maestro\Model\Job\QueueStatus;

class SimpleProgress implements Progress
{
    private $maxSizes = [];

    /**
     * @var QueueMonitor
     */
    private $monitor;

    public function __construct(QueueMonitor $monitor)
    {
        $this->monitor = $monitor;
    }

    public function render(): ?string
    {
        $output = [
            'Queue progress:',
''
        ];

        foreach ($this->monitor->report() as $queue) {
            $currentJob = $queue->currentJob();

            if (false === $queue->isRunning()) {
                $output[] = sprintf(
                    '  [%s] <info>%-45s</> %s (<fg=magenta>%s</>)',
                    $this->resolveStateChars($queue),
                    $queue->id(),
                    sprintf('%s/%s', $queue->maxSize() - $queue->size(), $queue->maxSize()),
                    $currentJob ? $currentJob->description() : '',
                    );
                continue;
            }

            $format = $queue->state()->isFailed() ? 'fg=white;bg=red' : 'fg=white;bg=green';
            $output[] = sprintf(
                '  [<%s>%s</>] <info>%-45s</> %s <%s>%s</>',
                $format,
                $this->resolveStateChars($queue),
                $queue->id(),
                sprintf('%s/%s', $queue->maxSize() - $queue->size(), $queue->maxSize()),
                $format,
                $currentJob ? 'Error on: ' . $currentJob->description() : '',
                );
        }

        return implode("\n", $output);
    }

    private function resolveStateChars(QueueStatus $queue): string
    {
        if ($queue->state()->isFailed()) {
            return 'NO';
        }

        if ($queue->state()->isDone()) {
            return 'OK';
        }

        if ($queue->state()->isPending()) {
            return '  ';
        }

        if ($queue->state()->isStarted()) {
            return '>>';
        }
    }
}
