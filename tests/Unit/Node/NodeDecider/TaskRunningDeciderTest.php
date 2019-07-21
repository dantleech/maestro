<?php

namespace Maestro\Tests\Unit\Node\NodeDecider;

use Maestro\Node\Environment;
use Maestro\Node\EnvironmentResolver;
use Maestro\Node\Exception\TaskFailed;
use Maestro\Node\Graph;
use Maestro\Node\Node;
use Maestro\Node\NodeStateMachine;
use Maestro\Node\NodeDeciderDecision;
use Maestro\Node\NodeDecider\TaskRunningDecider;
use Maestro\Node\SchedulerRegistry;
use Maestro\Node\State;
use Maestro\Node\TaskRunner;
use Maestro\Node\Task\NullTask;
use Maestro\Tests\Unit\Node\NodeHelper;
use PHPUnit\Framework\TestCase;

class TaskRunningDeciderTest extends TestCase
{
    /**
     * @var ObjectProphecy|TaskRunner
     */
    private $taskRunner;

    /**
     * @var ObjectProphecy
     */
    private $environmentResolver;

    /**
     * @var NodeStateMachine
     */
    private $stateMachine;

    /**
     * @var ObjectProphecy
     */
    private $schedulerRegistry;

    protected function setUp(): void
    {
        $this->taskRunner = $this->prophesize(TaskRunner::class);
        $this->environmentResolver = $this->prophesize(EnvironmentResolver::class);
        $this->stateMachine = new NodeStateMachine();
        $this->schedulerRegistry = $this->prophesize(SchedulerRegistry::class);
    }

    public function testRunsTask()
    {
        $task = new NullTask();
        $environment = Environment::empty();

        $this->taskRunner->run($task, $environment)->shouldBeCalled();
        $node = Node::create('n1', ['task'=> $task]);
        $graph = Graph::create([
            $node
        ], []);

        $this->environmentResolver->resolveFor($graph, $node)->willReturn($environment);

        $this->assertEquals(
            NodeDeciderDecision::CONTINUE(),
            $this->visit(
                $graph,
                NodeHelper::setState(
                    $node,
                    State::WAITING()
                )
            )
        );

        $this->assertTrue(
            $node->state()->is(State::BUSY()),
            'node is busy'
        );
    }

    public function testCancelsDescendantsIfTaskFailed()
    {
        $task = new NullTask();
        $environment = Environment::empty();

        $this->taskRunner->run($task, $environment)->willThrow(new TaskFailed('Sorry!!'));
        $node = Node::create('n1', ['task'=> $task]);
        $graph = Graph::create([
            $node
        ], []);
        $this->environmentResolver->resolveFor($graph, $node)->willReturn($environment);

        $this->assertTrue(
            $this->visit(
                $graph,
                NodeHelper::setState(
                    $node,
                    State::WAITING()
                )
            )->is(NodeDeciderDecision::CANCEL_DESCENDANTS())
        );
    }

    private function visit(Graph $graph, Node $node)
    {
        return (new TaskRunningDecider(
            $this->taskRunner->reveal(),
            $this->schedulerRegistry->reveal(),
            $this->environmentResolver->reveal()
        ))->decide($this->stateMachine, $graph, $node);
    }
}
