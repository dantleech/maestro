<?php

namespace Maestro\Tests\Unit\Task;

use Amp\Loop;
use Maestro\Task\Dispatcher;
use Maestro\Task\State;
use Maestro\Task\TaskRunner\NullTaskRunner;
use Maestro\Task\Node;
use Maestro\Task\Queue;
use Maestro\Task\TaskRunner;
use Maestro\Task\Task\NullTask;
use PHPUnit\Framework\TestCase;

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

    protected function setUp(): void
    {
        $this->runner = new NullTaskRunner();
    }

    public function testDispatchesQueue()
    {
        $queue = new Queue();

        $task1 = new NullTask();
        $node1 = $this->createNode('one', $task1);
        $queue->enqueue($node1);

        $task2 = new NullTask();
        $node2 = $this->createNode('two', $task2);
        $queue->enqueue($node2);

        $dispatcher = new Dispatcher($this->runner);
        $dispatcher->dispatch($queue);

        Loop::run();

        $this->assertEquals(State::IDLE(), $node1->state());
        $this->assertEquals(State::IDLE(), $node2->state());
    }

    private function createNode(string $name, NullTask $task): Node
    {
        return new Node($name, $task);
    }
}
