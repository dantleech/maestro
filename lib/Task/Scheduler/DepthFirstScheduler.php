<?php

namespace Maestro\Task\Scheduler;

use Maestro\Task\Graph;
use Maestro\Task\Node;
use Maestro\Task\Queue;
use Maestro\Task\Scheduler;

class DepthFirstScheduler implements Scheduler
{
    public function schedule(Graph $graph, Queue $queue): Queue
    {
        foreach ($graph->roots() as $rootNode) {
            $this->walkNode($graph, $rootNode, $queue);
        }

        return $queue;
    }

    private function walkNode(Graph $graph, Node $node, Queue $queue): void
    {
        if ($node->state()->isFailed() || $node->state()->isBusy()) {
            return;
        }

        if ($node->state()->isWaiting()) {
            $queue->enqueue($node);
            return;
        }

        foreach ($graph->dependenciesOf($node->name()) as $dependentNode) {
            $this->walkNode($graph, $dependentNode, $queue);
        }
    }
}
