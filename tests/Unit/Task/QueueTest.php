<?php

namespace Maestro\Tests\Unit\Task;

use Maestro\Task\Node;
use Maestro\Task\Queue;
use PHPUnit\Framework\TestCase;

class QueueTest extends TestCase
{
    public function testEnqueuesAndDequeues()
    {
        $queue = new Queue();
        $this->assertCount(0, $queue);

        $node1 = new Node('one');
        $queue->enqueue($node1);
        $this->assertCount(1, $queue);

        $node2 = new Node('one');
        $queue->enqueue($node2);
        $this->assertCount(2, $queue);

        $this->assertSame($node1, $queue->dequeue());
        $this->assertCount(1, $queue);
        $this->assertSame($node2, $queue->dequeue());
        $this->assertCount(0, $queue);
    }
}
