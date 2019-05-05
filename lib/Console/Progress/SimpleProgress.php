<?php

namespace Maestro\Console\Progress;

use Maestro\Model\Job\QueueMonitor;
use Maestro\Model\Job\QueueStatus;

class SimpleProgress implements Progress
{
    private $sizes = [];

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
            $size = $this->resolveSize($queue);
            $currentJob = $queue->currentJob();

            if (false === $queue->isRunning()) {
                $output[] = sprintf(
                    '  [  ] <info>%-45s</> %s (<fg=magenta>%s</>)',
                    $queue->id(),
                    sprintf('%s/%s', $size - $queue->size(), $size),
                    $currentJob ? $currentJob->description() : '',
                    );
                continue;
            }

            $format = $queue->state()->isFailed() ? 'fg=white;bg=red' : 'fg=white;bg=green';
            $output[] = sprintf(
                '  [<%s>%s</>] <info>%-45s</> %s <%s>%s</>',
                $format,
                $queue->state()->isFailed() ? 'NO' : 'OK',
                $queue->id(),
                sprintf('%s/%s', $size - $queue->size(), $size),
                $format,
                $currentJob ? 'Error on: ' . $currentJob->description() : '',
                );
        }

        return implode("\n", $output);
    }

    private function resolveSize(QueueStatus $queue)
    {
        if (!isset($this->sizes[$queue->id()])) {
            $this->sizes[$queue->id()] = $queue->size();
        }

        return $this->sizes[$queue->id()];
    }
}
