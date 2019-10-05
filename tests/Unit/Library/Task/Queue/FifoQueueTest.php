<?php

namespace Maestro\Tests\Unit\Library\Task\Queue;

use Maestro\Library\Task\Job;
use Maestro\Library\Task\Queue\FifoQueue;
use Maestro\Library\Task\Task\NullTask;
use PHPUnit\Framework\TestCase;

class FifoQueueTest extends TestCase
{
    public function testFifo()
    {
        $queue = new FifoQueue();
        $job1 = Job::create(new NullTask());
        $job2 = Job::create(new NullTask());
        $job3 = Job::create(new NullTask());

        $queue->enqueue($job1);
        $queue->enqueue($job2);
        $queue->enqueue($job3);

        $this->assertSame($job1, $queue->dequeue());
        $this->assertSame($job2, $queue->dequeue());
        $this->assertSame($job3, $queue->dequeue());
        $this->assertNull($queue->dequeue());
    }
}
