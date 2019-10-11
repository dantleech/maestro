<?php

namespace Maestro\Tests\Unit\Library\Task;

use Amp\Delayed;
use Amp\Loop;
use Amp\Success;
use Maestro\Library\Artifact\Artifacts;
use Maestro\Library\Task\Exception\TaskFailure;
use Maestro\Library\Task\Job;
use Maestro\Library\Task\JobState;
use Maestro\Library\Task\TaskRunner\InvokingTaskRunner;
use Maestro\Library\Task\Task\NullTask;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class JobTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $taskRunner;

    protected function setUp(): void
    {
        $this->taskRunner = $this->prophesize(InvokingTaskRunner::class);
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
        $this->taskRunner->run($task, Argument::type(Artifacts::class))->willReturn(new Delayed(5));
        $job = Job::create($task);
        $job->run($this->taskRunner->reveal());

        $this->assertEquals(JobState::BUSY(), $job->state());
    }

    public function testJobStateIsDoneAfterRunning()
    {
        $task = new NullTask();
        $this->taskRunner->run($task, Argument::type(Artifacts::class))->willReturn(new Success());
        $job = Job::create($task);
        Loop::run(function () use ($job) {
            $job->run($this->taskRunner->reveal());
        });

        $this->assertEquals(JobState::SUCCEEDED(), $job->state());
    }

    public function testJobThrowsExceptionIsMarkedAsFailed()
    {
        $task = new NullTask();
        $exception = new TaskFailure('Sorry');
        $this->taskRunner->run($task, Argument::type(Artifacts::class))->willThrow($exception);
        $job = Job::create($task);
        Loop::run(function () use ($job) {
            $job->run($this->taskRunner->reveal());
        });

        $this->assertEquals(JobState::FAILED(), $job->state());
        $this->assertSame($exception, $job->failure());
    }

    public function testJobCannotBeRunTwiceOrMore()
    {
        $task = new NullTask();
        $this->taskRunner->run($task, Argument::type(Artifacts::class))->willReturn(
            new Success()
        )->shouldBeCalledTimes(1);

        $job = Job::create($task);
        Loop::run(function () use ($job) {
            $job->run($this->taskRunner->reveal());
            $job->run($this->taskRunner->reveal());
            $job->run($this->taskRunner->reveal());
            $job->run($this->taskRunner->reveal());
        });

        $this->assertEquals(JobState::SUCCEEDED(), $job->state());
    }
}
