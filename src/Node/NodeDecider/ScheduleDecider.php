<?php

namespace Maestro\Node\NodeDecider;

use Maestro\Node\Graph;
use Maestro\Node\Node;
use Maestro\Node\NodeDeciderDecision;
use Maestro\Node\NodeStateMachine;
use Maestro\Node\NodeVisitor;
use Maestro\Node\SchedulerRegistry;

class ScheduleDecider implements NodeVisitor
{
    /**
     * @var SchedulerRegistry
     */
    private $schedulerRegistry;

    public function __construct(SchedulerRegistry $schedulerRegistry)
    {
        $this->schedulerRegistry = $schedulerRegistry;
    }

    public function decide(
        NodeStateMachine $stateMachine,
        Graph $graph,
        Node $node
    ): NodeDeciderDecision {
        if ($node->performScheduling($stateMachine, $this->schedulerRegistry)) {
            return NodeDeciderDecision::RESCHEDULE_DESCENDANTS();
        }

        return NodeDeciderDecision::CONTINUE();
    }
}
