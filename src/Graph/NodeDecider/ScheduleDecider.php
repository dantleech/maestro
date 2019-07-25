<?php

namespace Maestro\Graph\NodeDecider;

use Maestro\Graph\Graph;
use Maestro\Graph\Node;
use Maestro\Graph\NodeDeciderDecision;
use Maestro\Graph\NodeStateMachine;
use Maestro\Graph\NodeDecider;
use Maestro\Graph\SchedulerRegistry;

class ScheduleDecider implements NodeDecider
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
