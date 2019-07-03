<?php

namespace Maestro\Node;

use Maestro\Node\Graph;
use Maestro\Node\Node;
use Maestro\Node\NodeDeciderDecision;
use Maestro\Node\NodeStateMachine;

/**
 * TODO: The state machine dependency here should be factored out.
 * TODO: Should be renamed to "decision maker" or "descent condition"
 */
interface NodeVisitor
{
    public function decide(NodeStateMachine $stateMachine, Graph $graph, Node $node): NodeDeciderDecision;
}
