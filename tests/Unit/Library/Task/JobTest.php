<?php

namespace Maestro\Tests\Unit\Library\Task;

use Amp\Delayed;
use Amp\Loop;
use Amp\Success;
use Maestro\Library\Task\Job;
use Maestro\Library\Task\JobState;
use Maestro\Library\Task\TaskRunner;
use Maestro\Library\Task\Task\NullTask;
use PHPUnit\Framework\TestCase;

class JobTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $taskRunner;

    protected function setUp(): void
    {
        $this->taskRunner = $this->prophesize(TaskRunner::class);
    }
    public function testDefaultStateIsWaiting()
    {
        $task = new NullTask();
        $job = Job::create($task);
        $this->assertEquals(JobState::WAITING(), $job->state());
    }

    public function testJobStateIsProcessingAfterCallingRun()
    {
        $task = new NullTask();
        $this->taskRunner->run($task)->willReturn(new Delayed(5));
        $job = Job::create($task);
        $job->run($this->taskRunner->reveal());

        $this->assertEquals(JobState::PROCESSING(), $job->state());
    }

    public function testJobStateIsDoneAfterRunning()
    {
        $task = new NullTask();
        $this->taskRunner->run($task)->willReturn(new Success());
        $job = Job::create($task);
        Loop::run(function () use ($job) {
            $job->run($this->taskRunner->reveal());
        });

        $this->assertEquals(JobState::DONE(), $job->state());
    }

    public function testJobCannotBeRunTwiceOrMore()
    {
        $task = new NullTask();
        $this->taskRunner->run($task)->willReturn(
            new Success()
        )->shouldBeCalledTimes(1);

        $job = Job::create($task);
        Loop::run(function () use ($job) {
            $job->run($this->taskRunner->reveal());
            $job->run($this->taskRunner->reveal());
            $job->run($this->taskRunner->reveal());
            $job->run($this->taskRunner->reveal());
        });

        $this->assertEquals(JobState::DONE(), $job->state());
    }
}
