<?php

namespace Maestro\Tests\Unit\Task;

use Amp\Success;
use Maestro\Task\Artifacts;
use Maestro\Task\Exception\TaskFailed;
use Maestro\Task\Node;
use Maestro\Task\State;
use Maestro\Task\TaskRunner;
use Maestro\Task\TaskRunner\NullTaskRunner;
use Maestro\Task\Task\NullTask;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use RuntimeException;

class NodeTest extends TestCase
{
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
        \Amp\Promise\wait($rootNode->run($taskRunner));
        $this->assertEquals(State::IDLE(), $rootNode->state());
    }

    public function testSetsStateToFailWhenTaskFails()
    {
        $taskRunner = $this->prophesize(TaskRunner::class);
        $taskRunner->run(Argument::type(NullTask::class), Artifacts::empty())->willThrow(new TaskFailed('No'));

        $rootNode = Node::create('root');
        $this->assertEquals(State::WAITING(), $rootNode->state());
        \Amp\Promise\wait($rootNode->run($taskRunner->reveal()));
        $this->assertEquals(State::FAILED(), $rootNode->state());
    }
}
