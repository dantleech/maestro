<?php

namespace Maestro\Node\NodeDecider;

use Maestro\Node\EnvironmentResolver;
use Maestro\Node\Graph;
use Maestro\Node\Node;
use Maestro\Node\NodeStateMachine;
use Maestro\Node\NodeVisitor;
use Maestro\Node\NodeDeciderDecision;
use Maestro\Node\TaskResult;
use Maestro\Node\TaskRunner;

class TaskRunningDecider implements NodeVisitor
{
    /**
     * @var TaskRunner
     */
    private $runner;

    /**
     * @var EnvironmentResolver
     */
    private $artifactResolver;

    public function __construct(TaskRunner $runner, EnvironmentResolver $resolver)
    {
        $this->runner = $runner;
        $this->artifactResolver = $resolver;
    }

    public function decide(NodeStateMachine $stateMachine, Graph $graph, Node $node): NodeDeciderDecision
    {
        if ($node->state()->isCancelled()) {
            return NodeDeciderDecision::CONTINUE();
        }

        if ($node->state()->isDone()) {
            return NodeDeciderDecision::CONTINUE();
        }

        if ($node->state()->isWaiting() && $this->areDependenciesSatisfied($graph, $node)) {
            $node->run(
                $stateMachine,
                $this->runner,
                $this->artifactResolver->resolveFor($graph, $node)
            );
        }

        if ($node->taskResult()->is(TaskResult::FAILURE())) {
            return NodeDeciderDecision::CANCEL_DESCENDANTS();
        }

        return NodeDeciderDecision::CONTINUE();
    }

    private function areDependenciesSatisfied(Graph $graph, Node $node)
    {
        foreach ($graph->dependenciesFor($node->id()) as $node) {
            if (!$node->state()->isDone()) {
                return false;
            }
        }

        return true;
    }
}
