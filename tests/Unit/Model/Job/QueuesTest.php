<?php

namespace Maestro\Tests\Unit\Model\Job;

use Maestro\Model\Job\Queue;
use Maestro\Model\Job\QueueRegistry;
use Maestro\Model\Job\Queues;
use PHPUnit\Framework\TestCase;

class QueuesTest extends TestCase
{
    public function testCreatesNewNamedQueue()
    {
        $queue = Queues::create()->get('foobar');
        $this->assertInstanceOf(Queue::class, $queue);
        $this->assertEquals('foobar', $queue->id());
    }

    public function testDoesNotRecreateTheSameNamedQueue()
    {
        $queues = Queues::Create();
        $queue1 = $queues->get('foobar');
        $queue2 = $queues->get('foobar');
        $this->assertSame($queue1, $queue2);
    }
}
