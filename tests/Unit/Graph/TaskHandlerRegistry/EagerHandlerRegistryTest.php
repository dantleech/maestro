<?php

namespace Maestro\Tests\Unit\Graph\TaskHandlerRegistry;

use Maestro\Graph\Exception\HandlerNotFound;
use Maestro\Graph\TaskHandlerRegistry\EagerHandlerRegistry;
use Maestro\Graph\Task;
use Maestro\Graph\Task\NullHandler;
use Maestro\Graph\Task\NullTask;
use PHPUnit\Framework\TestCase;

class EagerHandlerRegistryTest extends TestCase
{
    public function testThrowsExceptionWhenHandlerNotFoundForTask()
    {
        $this->expectException(HandlerNotFound::class);
        $registry = new EagerHandlerRegistry([
            NullTask::class => new NullHandler()
        ]);
        $task = $this->prophesize(Task::class);
        $registry->getFor($task->reveal());
    }

    public function testReturnsHandlerForTask()
    {
        $registry = new EagerHandlerRegistry([
            NullTask::class => new NullHandler()
        ]);
        $handler = $registry->getFor(new NullTask());
        $this->assertInstanceOf(NullHandler::class, $handler);
    }
}
