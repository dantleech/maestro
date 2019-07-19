<?php

namespace Maestro\Tests\Unit\Node\TaskRunner;

use Amp\Promise;
use Amp\Success;
use Maestro\Node\Environment;
use Maestro\Node\Task;
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

    public function testRunsTask()
    {
        $task = new NullTask();
        $this->registry->getFor($task)->willReturn(new class implements TaskHandler {
            public function execute(Task $task, Environment $environment): Promise
            {
                return new Success();
            }
        });
        $promise = $this->runner->run($task, Environment::empty());
        $this->assertInstanceOf(Success::class, $promise);
    }

    public function testRunsTaskWithEnvironment()
    {
        $task = new NullTask();
        $this->registry->getFor($task)->willReturn(new class implements TaskHandler {
            public function execute(Task $task, Environment $environment): Promise
            {
                return new Success();
            }
        });
        $promise = $this->runner->run($task, Environment::empty());
        $this->assertInstanceOf(Success::class, $promise);
    }
}
