<?php

namespace Maestro\Task\NodeVisitor;

use Maestro\Task\Graph;
use Maestro\Task\Node;
use Maestro\Task\NodeVisitor;
use Maestro\Task\NodeVisitorDecision;

class SchedulingVisitor implements NodeVisitor
{
    public function visit(Graph $graph, Node $node): NodeVisitorDecision
    {
        if (false === $node->isScheduled()) {
            return NodeVisitorDecision::CONTINUE();
        }
    }
}
