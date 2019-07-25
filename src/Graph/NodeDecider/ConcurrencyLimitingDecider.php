<?php

namespace Maestro\Graph\NodeDecider;

use Maestro\Graph\Graph;
use Maestro\Graph\Node;
use Maestro\Graph\NodeStateMachine;
use Maestro\Graph\NodeDecider;
use Maestro\Graph\NodeDeciderDecision;
use Maestro\Graph\State;
use RuntimeException;

class ConcurrencyLimitingDecider implements NodeDecider
{
    /**
     * @var int
     */
    private $maxConcurrency;

    public function __construct(int $maxConcurrency)
    {
        if ($maxConcurrency < 1) {
            throw new RuntimeException(sprintf(
                'Max concurrency must be 1 or more, got %s',
                $maxConcurrency
            ));
        }

        $this->maxConcurrency = $maxConcurrency;
    }

    public function decide(NodeStateMachine $stateMachine, Graph $graph, Node $node): NodeDeciderDecision
    {
        if ($graph->nodes()->byState(State::BUSY())->count() >= $this->maxConcurrency) {
            return NodeDeciderDecision::DO_NOT_WALK_CHILDREN();
        }

        return NodeDeciderDecision::CONTINUE();
    }
}
