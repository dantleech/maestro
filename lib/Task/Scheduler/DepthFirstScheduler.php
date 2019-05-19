<?php

namespace Maestro\Task\Scheduler;

use Maestro\Task\Node;
use Maestro\Task\Queue;
use Maestro\Task\Scheduler;

class DepthFirstScheduler implements Scheduler
{
    public function schedule(Node $node, Queue $queue): Queue
    {
        if ($node->state()->isBusy()) {
            return $queue;
        }

        if ($node->state()->isWaiting()) {
            $queue->enqueue($node);
            return $queue;
        }

        foreach ($node->children() as $child) {
            assert($child instanceof Node);
            $this->schedule($child, $queue);
        }

        return $queue;
    }
}
