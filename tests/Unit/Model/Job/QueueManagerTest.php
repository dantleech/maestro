<?php

namespace Maestro\Tests\Unit\Model\Job;

use Maestro\Model\Job\Queue;
use Maestro\Model\Job\QueueManager;
use PHPUnit\Framework\TestCase;

class QueueManagerTest extends TestCase
{
    /**
     * @var QueueManager
     */
    private $queueManager;

    protected function setUp(): void
    {
        $this->queueManager = new QueueManager();
    }

    public function testCreatesNewNamedQueue()
    {
        $queue = $this->queueManager->getOrCreate('foobar');
        $this->assertInstanceOf(Queue::class, $queue);
        $this->assertEquals('foobar', $queue->id());
    }

    public function testDoesNotRecreateTheSameNamedQueue()
    {
        $queue1 = $this->queueManager->getOrCreate('foobar');
        $queue2 = $this->queueManager->getOrCreate('foobar');
        $this->assertSame($queue1, $queue2);
    }
}
