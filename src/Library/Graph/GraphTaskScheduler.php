<?php

namespace Maestro\Library\Graph;

use Maestro\Library\Task\Queue;
use Maestro\Library\Artifact\Artifacts;

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

            if ($node->state()->isIdle() && $this->isSatisfied($graph, $node)) {
                $node->run($this->queue, $artifacts);
                continue;
            }

            if ($node->state()->isDone()) {
                $this->runNodes($artifacts->spawnMutated($node->artifacts()), $graph, $graph->dependentsFor($node->id()));
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

    private function isSatisfied(Graph $graph, Node $node): bool
    {
        foreach ($graph->dependenciesFor($node->id()) as $dependency) {
            if (!$dependency->state()->isDone()) {
                return false;
            }
        }

        return true;
    }
}
