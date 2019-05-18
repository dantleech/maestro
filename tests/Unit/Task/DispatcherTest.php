<?php

namespace Maestro\Tests\Unit\Task;

use Amp\Loop;
use Maestro\Task\Dispatcher;
use Maestro\Task\Task\NullHandler;
use Maestro\Task\Node;
use Maestro\Task\Queue;
use Maestro\Task\TaskHandlerRegistry;
use Maestro\Task\TaskRunner;
use Maestro\Task\Task\NullTask;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class DispatcherTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $handlerRegistry;

    /**
     * @var TaskRunner
     */
    private $runner;

    /**
     * @var DummyHandler
     */
    private $handler1;

    /**
     * @var DummyHandler
     */
    private $handler2;

    protected function setUp(): void
    {
        $this->handlerRegistry = $this->prophesize(TaskHandlerRegistry::class);
        $this->runner = new TaskRunner($this->handlerRegistry->reveal());
        $this->handler1 = new NullHandler();
        $this->handler2 = new NullHandler();
    }

    public function testDispatchesQueue()
    {
        $queue = new Queue();

        $task1 = new NullTask();
        $this->handlerRegistry->getFor($task1)->willReturn($this->handler1);
        $queue->enqueue($this->createNode('one', $task1));

        $task2 = new NullTask();
        $this->handlerRegistry->getFor(Argument::is($task2))->willReturn($this->handler2);
        $queue->enqueue($this->createNode('two', $task2));

        $dispatcher = new Dispatcher($this->runner);
        $dispatcher->dispatch($queue);

        Loop::run();

        $this->assertTrue($this->handler1->wasInvoked());
        $this->assertTrue($this->handler2->wasInvoked());
    }

    private function createNode(string $name, NullTask $task): Node
    {
        return new Node('one', $task);
    }
}
