<?php

namespace Maestro\Node\NodeDecider;

use Maestro\Node\ArtifactsResolver;
use Maestro\Node\Graph;
use Maestro\Node\Node;
use Maestro\Node\NodeStateMachine;
use Maestro\Node\NodeVisitor;
use Maestro\Node\NodeDeciderDecision;
use Maestro\Node\TaskRunner;

class TaskRunningDecider implements NodeVisitor
{
    /**
     * @var TaskRunner
     */
    private $runner;

    /**
     * @var ArtifactsResolver
     */
    private $artifactResolver;

    public function __construct(TaskRunner $runner, ArtifactsResolver $resolver)
    {
        $this->runner = $runner;
        $this->artifactResolver = $resolver;
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
                $this->artifactResolver->resolveFor($graph, $node),
                $graph
            );
        }

        if ($node->state()->isFailed()) {
            return NodeDeciderDecision::CANCEL_DESCENDANTS();
        }

        return NodeDeciderDecision::DO_NOT_WALK_CHILDREN();
    }

    private function areDependenciesSatisfied(Graph $graph, Node $node)
    {
        foreach ($graph->dependenciesFor($node->id()) as $node) {
            if (!$node->state()->isIdle()) {
                return false;
            }
        }

        return true;
    }
}
