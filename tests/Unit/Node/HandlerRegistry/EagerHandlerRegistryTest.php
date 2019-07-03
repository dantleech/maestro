<?php

namespace Maestro\Tests\Unit\Node\HandlerRegistry;

use Maestro\Node\Exception\HandlerNotFound;
use Maestro\Node\HandlerRegistry\EagerHandlerRegistry;
use Maestro\Node\Task;
use Maestro\Node\Task\NullHandler;
use Maestro\Node\Task\NullTask;
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
