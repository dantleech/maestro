<?php

namespace Maestro\Node;

class GraphWalker
{
    /**
     * @var NodeVisitor[]
     */
    private $deciders;

    /**
     * @var NodeStateMachine
     */
    private $stateMachine;

    public function __construct(NodeStateMachine $stateMachine, array $visitors)
    {
        foreach ($visitors as $visitor) {
            $this->addVisitor($visitor);
        }
        $this->stateMachine = $stateMachine;
    }

    public function walk(Graph $graph): void
    {
        foreach ($graph->roots() as $rootNode) {
            $this->walkNode($graph, $rootNode);
        }
    }

    private function walkNode(Graph $graph, Node $node, bool $cancel = false): void
    {
        foreach ($this->deciders as $visitor) {
            $descision = $visitor->decide($this->stateMachine, $graph, $node);

            if (true === $descision->is(NodeDeciderDecision::CANCEL_DESCENDANTS())) {
                $cancel = true;
            }

            if (true === $descision->is(NodeDeciderDecision::DO_NOT_WALK_CHILDREN())) {
                return;
            }
        }

        foreach ($graph->dependentsFor($node->id()) as $dependentNode) {
            if ($cancel) {
                $dependentNode->cancel($this->stateMachine);
            }
            $this->walkNode($graph, $dependentNode, $cancel);
        }
    }

    private function addVisitor(NodeVisitor $visitor): void
    {
        $this->deciders[] = $visitor;
    }

    private function cancelDescendants(Graph $graph, Node $node): void
    {
        foreach ($graph->dependentsFor($node->id()) as $dependentNode) {
            $dependentNode->cancel();
            $this->walkNode($graph, $dependentNode);
        }
    }
}
