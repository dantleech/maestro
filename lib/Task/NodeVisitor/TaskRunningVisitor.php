<?php

namespace Maestro\Task\NodeVisitor;

use Maestro\Task\Artifacts;
use Maestro\Task\ArtifactsResolver;
use Maestro\Task\Graph;
use Maestro\Task\Node;
use Maestro\Task\NodeVisitor;
use Maestro\Task\NodeVisitorDecision;
use Maestro\Task\TaskRunner;

class TaskRunningVisitor implements NodeVisitor
{
    /**
     * @var TaskRunner
     */
    private $runner;

    /**
     * @var ArtifactsResolver
     */
    private $resolver;

    public function __construct(TaskRunner $runner, ArtifactsResolver $resolver)
    {
        $this->runner = $runner;
        $this->resolver = $resolver;
    }

    public function visit(Graph $graph, Node $node): NodeVisitorDecision
    {
        if ($node->state()->isFailed() || $node->state()->isBusy()) {
            return NodeVisitorDecision::DO_NOT_WALK_CHILDREN();
        }

        if ($node->state()->isWaiting()) {
            $node->run(
                $this->runner,
                $this->resolver->resolveFor($graph, $node)
            );
            return NodeVisitorDecision::DO_NOT_WALK_CHILDREN();
        }

        return NodeVisitorDecision::CONTINUE();
    }
}
