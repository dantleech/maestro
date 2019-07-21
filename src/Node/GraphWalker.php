<?php

namespace Maestro\Node;

class GraphWalker
{
    private const ACTION_CANCEL = 'cancel';
    private const ACTION_RESCHEDULE = 'reschedule';
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

    private function walkNode(Graph $graph, Node $node, string $action = null): void
    {
        foreach ($this->deciders as $visitor) {
            $descision = $visitor->decide($this->stateMachine, $graph, $node);

            if (true === $descision->is(NodeDeciderDecision::CANCEL_DESCENDANTS())) {
                $action = self::ACTION_CANCEL;
            }

            if (true === $descision->is(NodeDeciderDecision::RESCHEDULE_DESCENDANTS())) {
                $action = self::ACTION_RESCHEDULE;
            }

            if (true === $descision->is(NodeDeciderDecision::DO_NOT_WALK_CHILDREN())) {
                return;
            }
        }

        foreach ($graph->dependentsFor($node->id()) as $dependentNode) {
            if ($action === self::ACTION_CANCEL) {
                $dependentNode->cancel($this->stateMachine);
            }

            if ($action === self::ACTION_RESCHEDULE) {
                $dependentNode->reschedule($this->stateMachine);
            }

            $this->walkNode($graph, $dependentNode, $action);
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
