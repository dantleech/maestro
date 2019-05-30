<?php

namespace Maestro\Task\NodeVisitor;

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
        if ($node->state()->isCancelled()) {
            return NodeVisitorDecision::CONTINUE();
        }

        if ($node->state()->isIdle()) {
            return NodeVisitorDecision::CONTINUE();
        }

        if ($node->state()->isWaiting() && $this->areDependenciesSatisfied($graph, $node)) {
            $node->run(
                $this->runner,
                $this->resolver->resolveFor($graph, $node)
            );
        }

        if ($node->state()->isFailed()) {
            return NodeVisitorDecision::CANCEL_DESCENDANTS();
        }

        return NodeVisitorDecision::DO_NOT_WALK_CHILDREN();
    }

    private function areDependenciesSatisfied(Graph $graph, Node $node)
    {
        $dependencies = $graph->dependenciesFor($node->name());

        foreach ($dependencies as $node) {
            if (!$node->state()->isIdle()) {
                return false;
            }
        }

        return true;
    }
}
