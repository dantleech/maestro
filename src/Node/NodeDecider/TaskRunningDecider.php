<?php

namespace Maestro\Node\NodeDecider;

use Maestro\Node\EnvironmentResolver;
use Maestro\Node\Graph;
use Maestro\Node\Node;
use Maestro\Node\NodeStateMachine;
use Maestro\Node\NodeVisitor;
use Maestro\Node\NodeDeciderDecision;
use Maestro\Node\SchedulerRegistry;
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

    /**
     * @var SchedulerRegistry
     */
    private $registry;

    /**
     * @var EnvironmentResolver
     */
    private $resolver;

    public function __construct(
        TaskRunner $runner,
        SchedulerRegistry $registry,
        EnvironmentResolver $resolver
    ) {
        $this->runner = $runner;
        $this->artifactResolver = $resolver;
        $this->registry = $registry;
        $this->resolver = $resolver;
    }

    public function decide(NodeStateMachine $stateMachine, Graph $graph, Node $node): NodeDeciderDecision
    {
        if ($node->state()->isWaiting() && $this->areDependenciesSatisfied($graph, $node)) {
            $node->run(
                $stateMachine,
                $this->registry,
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
