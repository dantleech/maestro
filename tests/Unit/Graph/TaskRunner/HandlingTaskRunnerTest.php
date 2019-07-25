<?php

namespace Maestro\Tests\Unit\Graph\TaskRunner;

use Amp\Promise;
use Amp\Success;
use Maestro\Graph\Environment;
use Maestro\Graph\Task;
use Maestro\Graph\TaskHandler;
use Maestro\Graph\TaskHandlerRegistry;
use Maestro\Graph\TaskRunner;
use Maestro\Graph\TaskRunner\HandlingTaskRunner;
use Maestro\Graph\Task\NullTask;
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
