<?php

namespace Maestro\Task\Scheduler;

use Maestro\Task\Node;
use Maestro\Task\Queue;
use Maestro\Task\Scheduler;

class DepthFirstScheduler implements Scheduler
{
    public function schedule(Node $node, Queue $queue): Queue
    {
        foreach ($node->children() as $child) {
            assert($child instanceof Node);

            if ($child->state()->isBusy()) {
                continue;
            }

            if ($child->state()->isIdle()) {
                $this->schedule($node, $queue);
            }

            $queue->enqueue($child);
        }

        return $queue;
    }
}
