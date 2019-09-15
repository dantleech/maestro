<?php

namespace Maestro\Tests\Unit\Library\Task;

use Amp\Delayed;
use Amp\Loop;
use Amp\Success;
use Maestro\Library\Task\Job;
use Maestro\Library\Task\JobState;
use Maestro\Library\Task\Queue\FifoQueue;
use Maestro\Library\Task\TaskRunner;
use Maestro\Library\Task\Worker;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class WorkerTest extends TestCase
{
    /**
     * @var ObjectProphecy^TaskRunner
     */
    private $taskRunner;

    /**
     * @var FifoQueue
     */
    private $queue;


    protected function setUp(): void
    {
        $this->taskRunner = $this->prophesize(TaskRunner::class);
        $this->queue = new FifoQueue();
    }

    public function testRunsJobs()
    {
        $worker = $this->createWorker();
        $job1 = Job::createNull();
        $job2 = Job::createNull();

        $this->queue->enqueue($job1);
        $this->queue->enqueue($job2);

        $this->taskRunner->run($job1->task(), [])->willReturn(new Success());
        $this->taskRunner->run($job2->task(), [])->willReturn(new Success());

        Loop::run(function () use ($worker) {
            $worker->start();
        });

        $this->assertEquals(JobState::DONE(), $job1->state());
        $this->assertEquals(JobState::DONE(), $job2->state());
    }

    public function testRespectsConcurrencyOfOne()
    {
        $worker = $this->createWorker(10, 1);
        $job1 = Job::createNull();
        $job2 = Job::createNull();

        $this->queue->enqueue($job1);
        $this->queue->enqueue($job2);

        $this->taskRunner->run(
            $job1->task(),
            []
        )->willReturn(new Delayed(10));
        $this->taskRunner->run(
            $job2->task(),
            []
        )->willReturn(new Delayed(10));

        Loop::delay(5, function () use ($job1, $job2) {
            $this->assertFalse(
                $job1->state()->is(JobState::PROCESSING()) && $job2->state()->is(JobState::PROCESSING()),
                'The two jobs should not overlap'
            );
        });
        Loop::run(function () use ($worker) {
            $worker->start();
        });

        $this->assertEquals(JobState::DONE(), $job1->state());
        $this->assertEquals(JobState::DONE(), $job2->state());
    }

    private function createWorker($sleep = 10, $concurrency = 10): Worker
    {
        return new Worker($this->taskRunner->reveal(), $this->queue, $sleep, $concurrency);
    }
}
