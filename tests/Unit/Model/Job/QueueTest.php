<?php

namespace Maestro\Tests\Unit\Model\Job;

use Maestro\Model\Job\Job;
use Maestro\Model\Job\Queue;
use PHPUnit\Framework\TestCase;

class QueueTest extends TestCase
{
    public function testReturnsHead()
    {
        $job1 = new class implements Job {
        };
        $job2 = new class implements Job {
        };
        $queue = new Queue('foobar');
        $queue->enqueue($job1);
        $queue->enqueue($job2);
        $this->assertSame($job1, $queue->head());
    }

    public function testReturnsNullForHeadIfQueueEmpty()
    {
        $queue = new Queue('foobar');
        $this->assertNull($queue->head());
    }
}
