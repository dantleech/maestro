<?php

namespace Maestro\Library\GraphTask;

use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\Node;
use Maestro\Library\Graph\Nodes;
use Maestro\Library\Task\Queue;

class GraphTaskScheduler
{
    /**
     * @var Queue
     */
    private $queue;

    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
    }

    public function run(Graph $graph): void
    {
        $artifacts = new Artifacts();
        $this->runNodes($artifacts, $graph, $graph->roots());
    }

    private function runNodes(Artifacts $artifacts, Graph $graph, Nodes $nodes): void
    {
        foreach ($nodes as $node) {
            assert($node instanceof Node);

            if ($node->state()->isIdle()) {
                $node->run($this->queue, $artifacts);
                continue;
            }

            if ($node->state()->isDone()) {
                $artifacts = $artifacts->spawnMutated($node->artifacts());
                $this->runNodes($artifacts, $graph, $graph->dependentsFor($node->id()));
                continue;
            }
        }
    }
}
