<?php

namespace Maestro\Library\GraphTaskRunner;

use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\Node;
use Maestro\Library\Graph\Nodes;
use Maestro\Library\Task\Queue;

class GraphTaskRunner
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
        $context = new ArtifactContainer();
        $this->runNodes($context, $graph, $graph->roots());
    }

    private function runNodes(ArtifactContainer $container, Graph $graph, Nodes $nodes): void
    {
        foreach ($nodes as $node) {
            assert($node instanceof Node);

            if ($node->state()->isIdle()) {
                $node->run($this->queue, $container->toArray());
                continue;
            }

            if ($node->state()->isDone()) {
                $container = $container->spawnMutated($node->artifacts());
                $this->runNodes($container, $graph, $graph->dependentsFor($node->id()));
                continue;
            }
        }
    }
}
