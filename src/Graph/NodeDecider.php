<?php

namespace Maestro\Graph;

/**
 * TODO: The state machine dependency here should be factored out.
 */
interface NodeDecider
{
    public function decide(NodeStateMachine $stateMachine, Graph $graph, Node $node): NodeDeciderDecision;
}
