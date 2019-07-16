<?php

namespace Maestro\Tests\Unit\Node;

use Amp\Loop;
use Maestro\Node\Artifacts;
use Maestro\Node\Exception\TaskFailed;
use Maestro\Node\Graph;
use Maestro\Node\Node;
use Maestro\Node\NodeStateMachine;
use Maestro\Node\State;
use Maestro\Node\TaskContext;
use Maestro\Node\TaskRunner;
use Maestro\Node\TaskRunner\NullTaskRunner;
use Maestro\Node\Task\NullTask;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class NodeTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $stateMachine;

    protected function setUp(): void
    {
        $this->stateMachine = $this->prophesize(NodeStateMachine::class);
        $this->stateMachine->transition(Argument::type(Node::class), Argument::type(State::class))->will(function ($args) {
            return $args[1];
        });
    }

    public function testReturnsLabelIfGiven()
    {
        $rootNode = Node::create('root', [
            'label' => 'Foobar',
        ]);
        $this->assertEquals('Foobar', $rootNode->label());
    }

    public function testDefaultStateIsWaiting()
    {
        $rootNode = Node::create('root');
        $this->assertTrue($rootNode->state()->isWaiting());
    }

    public function testRunsTask()
    {
        $taskRunner = new NullTaskRunner();
        $rootNode = Node::create('root');
        $this->assertEquals(State::WAITING(), $rootNode->state());
        $rootNode->run(
            $this->stateMachine->reveal(),
            $taskRunner,
            Artifacts::empty(),
            Graph::create([$rootNode], [])
        );
        Loop::run();
        $this->assertEquals(State::DONE(), $rootNode->state());
    }

    public function testSetsStateToFailWhenTaskFails()
    {
        $rootNode = Node::create('root');
        $taskRunner = $this->prophesize(TaskRunner::class);
        $taskRunner->run(Argument::type(NullTask::class), new TaskContext($rootNode, Artifacts::empty()))->willThrow(new TaskFailed('No'));

        $this->assertEquals(State::WAITING(), $rootNode->state());
        $rootNode->run(
            $this->stateMachine->reveal(),
            $taskRunner->reveal(),
            Artifacts::empty(),
            Graph::create([$rootNode], [])
        );
        Loop::run();
        $this->assertEquals(State::FAILED(), $rootNode->state());
    }
}
