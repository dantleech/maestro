<?php

namespace Maestro\Tests\Unit\Library\Task;

use Amp\Failure;
use Amp\Loop;
use Amp\Success;
use Maestro\Library\Task\Artifacts;
use Maestro\Library\Task\Exception\TaskFailure;
use Maestro\Library\Task\Job;
use Maestro\Library\Task\JobState;
use Maestro\Library\Task\Queue\FifoQueue;
use Maestro\Library\Task\TaskRunner\InvokingTaskRunner;
use Maestro\Library\Task\Worker;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
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
        $this->taskRunner = $this->prophesize(InvokingTaskRunner::class);
        $this->queue = new FifoQueue();
    }

    public function testRunsJobs()
    {
        $worker = $this->createWorker();
        $job1 = Job::createNull();
        $job2 = Job::createNull();

        $this->queue->enqueue($job1);
        $this->queue->enqueue($job2);

        $this->taskRunner->run($job1->task(), Argument::type(Artifacts::class))->willReturn(new Success());
        $this->taskRunner->run($job2->task(), Argument::type(Artifacts::class))->willReturn(new Success());

        Loop::run(function () use ($worker) {
            $worker->start();
        });

        $this->assertEquals(JobState::SUCCEEDED(), $job1->state());
        $this->assertEquals(JobState::SUCCEEDED(), $job2->state());
    }

    public function testFailedJobsAreRemoved()
    {
        $worker = $this->createWorker(10, 1);
        $job1 = Job::createNull();
        $job2 = Job::createNull();

        $this->queue->enqueue($job1);
        $this->queue->enqueue($job2);

        $this->taskRunner->run(
            $job1->task(),
            Argument::type(Artifacts::class)
        )->willReturn(new Failure(new TaskFailure('no')));

        $this->taskRunner->run(
            $job2->task(),
            Argument::type(Artifacts::class)
        )->willReturn(new Failure(new TaskFailure('no')));

        Loop::run(function () use ($worker) {
            $worker->start();
        });

        $this->assertEquals(JobState::FAILED(), $job1->state());
        $this->assertEquals(JobState::FAILED(), $job2->state());
    }

    private function createWorker($sleep = 10, $concurrency = 10): Worker
    {
        return new Worker($this->taskRunner->reveal(), $this->queue, $sleep, $concurrency);
    }
}
