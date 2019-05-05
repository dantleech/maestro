<?php

namespace Maestro\Console\Progress;

use Maestro\Model\Job\Queue;
use Maestro\Model\Job\Queues;

class SimpleProgress implements Progress
{
    private $sizes = [];

    /**
     * @param Queues<Queue> $queues
     */
    public function render(Queues $queues): ?string
    {
        $output = [
            'Queue progress:',
''
        ];
        foreach ($queues as $queue) {
            $size = $this->resolveSize($queue);
            $output[] = sprintf(
                '  <info>%s</> %s',
                $queue->id(),
                str_repeat('X', $size - count($queue)).
                str_repeat('.', count($queue))
            );
        }

        return implode("\n", $output);
    }

    private function resolveSize(Queue $queue)
    {
        if (!isset($this->sizes[$queue->id()])) {
            $this->sizes[$queue->id()] = count($queue);
        }

        return $this->sizes[$queue->id()];
    }
}
