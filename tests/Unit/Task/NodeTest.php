<?php

namespace Maestro\Tests\Unit\Task;

use Amp\Loop;
use Maestro\Task\Artifacts;
use Maestro\Task\Exception\TaskFailed;
use Maestro\Task\Node;
use Maestro\Task\NodeStateMachine;
use Maestro\Task\State;
use Maestro\Task\TaskRunner;
use Maestro\Task\TaskRunner\NullTaskRunner;
use Maestro\Task\Task\NullTask;
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
        $rootNode->run($this->stateMachine->reveal(), $taskRunner, Artifacts::empty());
        Loop::run();
        $this->assertEquals(State::DONE(), $rootNode->state());
    }

    public function testSetsStateToFailWhenTaskFails()
    {
        $taskRunner = $this->prophesize(TaskRunner::class);
        $taskRunner->run(Argument::type(NullTask::class), Artifacts::empty())->willThrow(new TaskFailed('No'));

        $rootNode = Node::create('root');
        $this->assertEquals(State::WAITING(), $rootNode->state());
        $rootNode->run($this->stateMachine->reveal(), $taskRunner->reveal(), Artifacts::empty());
        Loop::run();
        $this->assertEquals(State::FAILED(), $rootNode->state());
    }
}
