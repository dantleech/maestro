<?php

namespace Maestro\Graph\NodeDecider;

use Maestro\Graph\EnvironmentResolver;
use Maestro\Graph\Graph;
use Maestro\Graph\Node;
use Maestro\Graph\NodeStateMachine;
use Maestro\Graph\NodeDecider;
use Maestro\Graph\NodeDeciderDecision;
use Maestro\Graph\SchedulerRegistry;
use Maestro\Graph\TaskResult;
use Maestro\Graph\TaskRunner;

class TaskRunningDecider implements NodeDecider
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
            if (!$node->taskResult()->is(TaskResult::SUCCESS())) {
                return false;
            }
        }

        return true;
    }
}
