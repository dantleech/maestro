<?php

namespace Maestro\Task;

use Maestro\Task\Graph;
use Maestro\Task\Node;
use Maestro\Task\Queue;
use Maestro\Task\Scheduler;

class GraphWalker
{
    /**
     * @var NodeVisitor[]
     */
    private $visitors;

    public function __construct(array $visitors)
    {
        foreach ($visitors as $visitor) {
            $this->addVisitor($visitor);
        }
    }

    public function walk(Graph $graph): void
    {
        foreach ($graph->roots() as $rootNode) {
            $this->walkNode($graph, $rootNode);
        }
    }

    private function walkNode(Graph $graph, Node $node): void
    {
        foreach ($this->visitors as $visitor) {
            $descision = $visitor->visit($graph, $node);

            if ($descision->is(NodeVisitorDecision::DO_NOT_WALK_CHILDREN())) {
                return;
            }
        }

        foreach ($graph->dependentsOf($node->name()) as $dependentNode) {
            $this->walkNode($graph, $dependentNode);
        }
    }

    private function addVisitor(NodeVisitor $visitor)
    {
        $this->visitors[] = $visitor;
    }
}
