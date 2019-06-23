<?php

namespace Maestro\Task\NodeVisitor;

use Maestro\Task\Graph;
use Maestro\Task\Node;
use Maestro\Task\NodeStateMachine;
use Maestro\Task\NodeVisitor;
use Maestro\Task\NodeVisitorDecision;
use Maestro\Task\State;
use RuntimeException;

class ConcurrencyLimitingVisitor implements NodeVisitor
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

    public function visit(NodeStateMachine $stateMachine, Graph $graph, Node $node): NodeVisitorDecision
    {
        if ($graph->nodes()->byState(State::BUSY())->count() >= $this->maxConcurrency) {
            return NodeVisitorDecision::DO_NOT_WALK_CHILDREN();
        }

        return NodeVisitorDecision::CONTINUE();
    }
}
