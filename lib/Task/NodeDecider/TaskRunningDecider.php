<?php

namespace Maestro\Task\NodeDecider;

use Maestro\Task\ArtifactsResolver;
use Maestro\Task\Graph;
use Maestro\Task\Node;
use Maestro\Task\NodeStateMachine;
use Maestro\Task\NodeVisitor;
use Maestro\Task\NodeDeciderDecision;
use Maestro\Task\TaskRunner;

class TaskRunningDecider implements NodeVisitor
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

    public function decide(NodeStateMachine $stateMachine, Graph $graph, Node $node): NodeDeciderDecision
    {
        if ($node->state()->isCancelled()) {
            return NodeDeciderDecision::CONTINUE();
        }

        if ($node->state()->isIdle()) {
            return NodeDeciderDecision::CONTINUE();
        }

        if ($node->state()->isWaiting() && $this->areDependenciesSatisfied($graph, $node)) {
            $node->run(
                $stateMachine,
                $this->runner,
                $this->resolver->resolveFor($graph, $node)
            );
        }

        if ($node->state()->isFailed()) {
            return NodeDeciderDecision::CANCEL_DESCENDANTS();
        }

        return NodeDeciderDecision::DO_NOT_WALK_CHILDREN();
    }

    private function areDependenciesSatisfied(Graph $graph, Node $node)
    {
        $dependencies = $graph->dependenciesFor($node->id());

        foreach ($dependencies as $node) {
            if (!$node->state()->isIdle()) {
                return false;
            }
        }

        return true;
    }
}
