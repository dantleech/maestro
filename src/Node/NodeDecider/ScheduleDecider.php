<?php

namespace Maestro\Node\NodeDecider;

use Maestro\Node\Graph;
use Maestro\Node\Node;
use Maestro\Node\NodeDeciderDecision;
use Maestro\Node\NodeStateMachine;
use Maestro\Node\NodeVisitor;

class ScheduleDecider implements NodeVisitor
{
    public function decide(
        NodeStateMachine $stateMachine,
        Graph $graph,
        Node $node
    ): NodeDeciderDecision {
        if ($node->checkSchedule($stateMachine)) {
            return NodeDeciderDecision::RESCHEDULE_DESCENDANTS();
        }

        return NodeDeciderDecision::CONTINUE();
    }
}
