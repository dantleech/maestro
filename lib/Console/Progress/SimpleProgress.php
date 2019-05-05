<?php

namespace Maestro\Console\Progress;

use Maestro\Model\Job\Queue;
use Maestro\Model\Job\QueueMonitor;
use Maestro\Model\Job\QueueStatus;
use Maestro\Model\Job\Queues;

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

    /**
     * @param Queues<Queue> $queues
     */
    public function render(Queues $queues): ?string
    {
        $output = [
            'Queue progress:',
''
        ];

        foreach ($this->monitor->report() as $queue) {
            $size = $this->resolveSize($queue);
            $output[] = sprintf(
                '  <info>%s</> %s',
                $queue->id,
                str_repeat('X', $size - $queue->size).
                str_repeat('.', $queue->size)
            );
        }

        return implode("\n", $output);
    }

    private function resolveSize(QueueStatus $queue)
    {
        if (!isset($this->sizes[$queue->id])) {
            $this->sizes[$queue->id] = $queue->size;
        }

        return $this->sizes[$queue->id];
    }
}
