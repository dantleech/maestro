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
        $this->runNodes($graph, $graph->roots());
    }

    private function runNodes(Graph $graph, Nodes $nodes): void
    {
        foreach ($nodes as $node) {
            assert($node instanceof Node);

            if ($node->state()->isFailed()) {
                $this->cancelDescendants($graph, $node);
                continue;
            }

            if ($node->state()->isIdle() && $this->isSatisfied($graph, $node)) {
                $node->run($this->queue, $this->resolveArtifacts($graph, $node));
                continue;
            }

            if ($node->state()->isDone()) {
                $this->runNodes(
                    $graph,
                    $graph->dependentsFor($node->id())
                );
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

    private function resolveArtifacts(Graph $graph, Node $node): Artifacts
    {
        $artifacts = new Artifacts();

        foreach ($graph->dependenciesFor($node->id()) as $artNode) {
            $artifacts = $artifacts->spawnMutated($artNode->artifacts());
            foreach ($graph->ancestryFor($artNode->id()) as $descNode) {
                $artifacts = $artifacts->spawnMutated($descNode->artifacts());
            }
        }

        return $artifacts;
    }
}
