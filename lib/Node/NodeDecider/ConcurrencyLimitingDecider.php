<?php

namespace Maestro\Node\NodeDecider;

use Maestro\Node\Graph;
use Maestro\Node\Node;
use Maestro\Node\NodeStateMachine;
use Maestro\Node\NodeVisitor;
use Maestro\Node\NodeDeciderDecision;
use Maestro\Node\State;
use RuntimeException;

class ConcurrencyLimitingDecider implements NodeVisitor
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
