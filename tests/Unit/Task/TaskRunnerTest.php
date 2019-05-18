<?php

namespace Maestro\Tests\Unit\Task;

use Amp\Success;
use Maestro\Task\Artifacts;
use Maestro\Task\Exception\InvalidHandler;
use Maestro\Task\TaskHandler;
use Maestro\Task\TaskHandlerRegistry;
use Maestro\Task\TaskRunner;
use Maestro\Task\Task\NullTask;
use PHPUnit\Framework\TestCase;

class TaskRunnerTest extends TestCase
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
        $this->runner = new TaskRunner($this->registry->reveal());
    }

    public function testThrowsExceptionIfHandlerNotInvokable()
    {
        $this->expectException(InvalidHandler::class);
        $this->expectExceptionMessage('is not __invoke');
        $task = new NullTask();
        $this->registry->getFor($task)->willReturn(new class implements TaskHandler {
        });
        $this->runner->run($task, Artifacts::empty());
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
        $promise = $this->runner->run($task, Artifacts::empty());
        $this->assertInstanceOf(Success::class, $promise);
    }
}
