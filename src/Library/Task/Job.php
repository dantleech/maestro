<?php

namespace Maestro\Library\Task;

use Amp\Deferred;
use Amp\Promise;
use Maestro\Library\Task\Exception\TaskFailure;
use Maestro\Library\Task\Task\NullTask;

class Job
{
    /**
     * @var Task
     */
    private $task;

    /**
     * @var Deferred
     */
    private $deferred;

    /**
     * @var JobState
     */
    private $state;

    /**
     * @var Artifacts
     */
    private $artifacts;

    /**
     * @var TaskFailure|null
     */
    private $failure;

    private function __construct(Task $task, Artifacts $artifacts)
    {
        $this->task = $task;
        $this->deferred = new Deferred();
        $this->state = JobState::WAITING();
        $this->artifacts = $artifacts;
    }

    public static function create(Task $task, ?Artifacts $artifacts = null): Job
    {
        return new self($task, $artifacts ?: new Artifacts());
    }

    public static function createNull(): self
    {
        return self::create(new NullTask());
    }

    public function run(TaskRunner $runner): void
    {
        if ($this->state->isNot(JobState::WAITING())) {
            return;
        }

        $this->state = JobState::PROCESSING();

        \Amp\asyncCall(function () use ($runner) {
            try {
                $result = yield $runner->run($this->task, $this->artifacts);
            } catch (TaskFailure $e) {
                $this->state = JobState::FAILED();
                $this->failure = $e;
                $this->deferred->resolve([]);
                return;
            }
            $this->state = JobState::DONE();
            $this->deferred->resolve($result);
        });
    }

    public function result(): Promise
    {
        return $this->deferred->promise();
    }

    public function task(): Task
    {
        return $this->task;
    }

    public function state(): JobState
    {
        return $this->state;
    }

    public function artifacts(): Artifacts
    {
        return $this->artifacts;
    }

    public function failure(): ?TaskFailure
    {
        return $this->failure;
    }
}
