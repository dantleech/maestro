<?php

namespace Maestro\Tests\Unit\Task\NodeDecider;

use Maestro\Task\Artifacts;
use Maestro\Task\ArtifactsResolver;
use Maestro\Task\Graph;
use Maestro\Task\Node;
use Maestro\Task\NodeStateMachine;
use Maestro\Task\NodeDeciderDecision;
use Maestro\Task\NodeDecider\TaskRunningDecider;
use Maestro\Task\State;
use Maestro\Task\TaskRunner;
use Maestro\Task\Task\NullTask;
use Maestro\Tests\Unit\Task\NodeHelper;
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
    private $artifactsResolver;

    /**
     * @var NodeStateMachine
     */
    private $stateMachine;

    protected function setUp(): void
    {
        $this->taskRunner = $this->prophesize(TaskRunner::class);
        $this->artifactsResolver = $this->prophesize(ArtifactsResolver::class);
        $this->stateMachine = new NodeStateMachine();
    }

    public function testDoesNotWalkChildrenIfNodeIsBusy()
    {
        $node = Node::create('n1');
        $this->assertTrue(
            $this->visit(
                Graph::create([$node], []),
                NodeHelper::setState(
                    $node,
                    State::BUSY()
                )
            )->is(NodeDeciderDecision::DO_NOT_WALK_CHILDREN())
        );
    }

    public function testRunsTask()
    {
        $task = new NullTask();
        $artifacts = Artifacts::empty();

        $this->taskRunner->run($task, $artifacts)->shouldBeCalled();
        $node = Node::create('n1', ['task'=> $task]);
        $graph = Graph::create([
            $node
        ], []);

        $this->artifactsResolver->resolveFor($graph, $node)->willReturn($artifacts);

        $this->assertTrue(
            $this->visit(
                $graph,
                NodeHelper::setState(
                    $node,
                    State::WAITING()
                )
            )->is(NodeDeciderDecision::DO_NOT_WALK_CHILDREN()),
            'does not walk children when running node'
        );

        $this->assertTrue(
            $node->state()->is(State::BUSY()),
            'node is busy'
        );
    }

    private function visit(Graph $graph, Node $node)
    {
        return (new TaskRunningDecider(
            $this->taskRunner->reveal(),
            $this->artifactsResolver->reveal()
        ))->decide($this->stateMachine, $graph, $node);
    }
}
