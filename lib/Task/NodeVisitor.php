<?php

namespace Maestro\Task;

/**
 * TODO: The state machine dependency here should be factored out.
 * TODO: Should be renamed to "decision maker" or "descent condition"
 */
interface NodeVisitor
{
    public function decide(NodeStateMachine $stateMachine, Graph $graph, Node $node): NodeDeciderDecision;
}
