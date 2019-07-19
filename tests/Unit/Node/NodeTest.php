<?php

namespace Maestro\Tests\Unit\Node;

use Amp\Loop;
use Amp\Promise;
use Amp\Success;
use Maestro\Node\Environment;
use Maestro\Node\Exception\TaskFailed;
use Maestro\Node\Exception\TaskHandlerDidNotReturnEnvironment;
use Maestro\Node\Node;
use Maestro\Node\NodeStateMachine;
use Maestro\Node\State;
use Maestro\Node\TaskRunner;
use Maestro\Node\TaskRunner\NullTaskRunner;
use Maestro\Node\Task\NullTask;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use stdClass;

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

    public function testThrowsExceptionIfResolvedPromiseValueFromTaskHandlerIsNotAnEnvironment()
    {
        $this->expectException(TaskHandlerDidNotReturnEnvironment::class);

        $taskRunner = $this->prophesize(TaskRunner::class);
        $taskRunner->run(new NullTask(), Environment::empty())->willReturn(new Success(new stdClass()));

        $rootNode = Node::create('root');
        $rootNode->run(
            $this->stateMachine->reveal(),
            $taskRunner->reveal(),
            Environment::empty()
        );

        Loop::run();
    }

    public function testRunsTask()
    {
        $taskRunner = new NullTaskRunner();
        $rootNode = Node::create('root');
        $this->assertEquals(State::WAITING(), $rootNode->state());
        $rootNode->run($this->stateMachine->reveal(), $taskRunner, Environment::empty());
        Loop::run();
        $this->assertEquals(State::DONE(), $rootNode->state());
    }

    public function testSetsStateToFailWhenTaskFails()
    {
        $taskRunner = $this->prophesize(TaskRunner::class);
        $taskRunner->run(Argument::type(NullTask::class), Environment::empty())->willThrow(new TaskFailed('No'));

        $rootNode = Node::create('root');
        $this->assertEquals(State::WAITING(), $rootNode->state());
        $rootNode->run($this->stateMachine->reveal(), $taskRunner->reveal(), Environment::empty());
        Loop::run();
        $this->assertEquals(State::FAILED(), $rootNode->state());
    }
}
