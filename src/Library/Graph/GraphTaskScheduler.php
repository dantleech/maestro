<?php

namespace Maestro\Library\Graph;

use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\Node;
use Maestro\Library\Graph\Nodes;
use Maestro\Library\Task\Queue;
use Maestro\Library\Task\Artifacts;

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

            if ($node->state()->isFailed()) {
                $this->cancelDescendants($graph, $node);
                continue;
            }

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

    private function cancelDescendants(Graph $graph, Node $node): void
    {
        foreach ($graph->descendantsFor($node->id()) as $node) {
            $node->cancel();
        }
    }
}
