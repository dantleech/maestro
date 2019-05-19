<?php

namespace Maestro\Tests\Unit\Task\HandlerRegistry;

use Maestro\Task\Exception\HandlerNotFound;
use Maestro\Task\HandlerRegistry\EagerHandlerRegistry;
use Maestro\Task\Task;
use Maestro\Task\Task\NullHandler;
use Maestro\Task\Task\NullTask;
use PHPUnit\Framework\TestCase;

class EagerHandlerRegistryTest extends TestCase
{
    public function testThrowsExceptionWhenHandlerNotFoundForTask()
    {
        $this->expectException(HandlerNotFound::class);
        $registry = new EagerHandlerRegistry([
            new NullHandler()
        ]);
        $task = $this->prophesize(Task::class);
        $task->handler()->willReturn('Foobar');
        $registry->getFor($task->reveal());
    }

    public function testReturnsHandlerForTask()
    {
        $registry = new EagerHandlerRegistry([
            new NullHandler()
        ]);
        $handler = $registry->getFor(new NullTask());
        $this->assertInstanceOf(NullHandler::class, $handler);
    }
}
