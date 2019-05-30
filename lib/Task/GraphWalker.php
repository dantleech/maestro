<?php

namespace Maestro\Task;

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

    private function walkNode(Graph $graph, Node $node, bool $cancel = false): void
    {
        foreach ($this->visitors as $visitor) {
            $descision = $visitor->visit($graph, $node);

            if (true === $descision->is(NodeVisitorDecision::CANCEL_DESCENDANTS())) {
                $cancel = true;
            }

            if (true === $descision->is(NodeVisitorDecision::DO_NOT_WALK_CHILDREN())) {
                return;
            }
        }

        foreach ($graph->dependentsOf($node->name()) as $dependentNode) {
            if ($cancel) {
                $dependentNode->cancel();
            }
            $this->walkNode($graph, $dependentNode, $cancel);
        }
    }

    private function addVisitor(NodeVisitor $visitor): void
    {
        $this->visitors[] = $visitor;
    }

    private function cancelDescendants(Graph $graph, Node $node): void
    {
        foreach ($graph->dependentsOf($node->name()) as $dependentNode) {
            $dependentNode->cancel();
            $this->walkNode($graph, $dependentNode);
        }
    }
}
