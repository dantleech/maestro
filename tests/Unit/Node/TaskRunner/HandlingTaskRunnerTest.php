<?php

namespace Maestro\Tests\Unit\Node\TaskRunner;

use Amp\Success;
use Maestro\Node\Artifacts;
use Maestro\Node\Exception\InvalidHandler;
use Maestro\Node\Exception\InvalidHandlerResponse;
use Maestro\Node\Node;
use Maestro\Node\TaskContext;
use Maestro\Node\TaskHandler;
use Maestro\Node\TaskHandlerRegistry;
use Maestro\Node\TaskRunner;
use Maestro\Node\TaskRunner\HandlingTaskRunner;
use Maestro\Node\Task\NullTask;
use PHPUnit\Framework\TestCase;

class HandlingTaskRunnerTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $registry;

    /**
     * @var TaskRunner
     */
    private $runner;


    protected function setUp(): void
    {
        $this->registry = $this->prophesize(TaskHandlerRegistry::class);
        $this->runner = new HandlingTaskRunner($this->registry->reveal());
    }

    public function testThrowsExceptionIfHandlerNotInvokable()
    {
        $this->expectException(InvalidHandler::class);
        $this->expectExceptionMessage('is not __invoke');
        $task = new NullTask();
        $this->registry->getFor($task)->willReturn(new class implements TaskHandler {
        });
        $this->runner->run($task, new TaskContext(Node::create('foo'), Artifacts::empty()));
    }

    public function testThrowsExceptionIfHandlerDoesNotReturnPromise()
    {
        $this->expectException(InvalidHandlerResponse::class);
        $task = new NullTask();
        $this->registry->getFor($task)->willReturn(new class implements TaskHandler {
            public function __invoke()
            {
                return '';
            }
        });
        $this->runner->run($task, $this->createTaskContext());
    }

    public function testRunsTask()
    {
        $task = new NullTask();
        $this->registry->getFor($task)->willReturn(new class implements TaskHandler {
            public function __invoke(NullTask $task)
            {
                return new Success();
            }
        });
        $promise = $this->runner->run($task, $this->createTaskContext());
        $this->assertInstanceOf(Success::class, $promise);
    }

    public function testRunsTaskWithArtifacts()
    {
        $task = new NullTask();
        $this->registry->getFor($task)->willReturn(new class implements TaskHandler {
            public function __invoke(NullTask $task, TaskContext $context)
            {
                return new Success();
            }
        });
        $promise = $this->runner->run($task, $this->createTaskContext());
        $this->assertInstanceOf(Success::class, $promise);
    }

    private function createTaskContext(): TaskContext
    {
        return new TaskContext(Node::create('root'), Artifacts::empty());
    }
}
