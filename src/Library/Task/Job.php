<?php

namespace Maestro\Library\Task;

use Amp\Deferred;
use Amp\Promise;
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

    private function __construct(Task $task)
    {
        $this->task = $task;
        $this->deferred = new Deferred();
        $this->state = JobState::WAITING();
    }

    public static function create(Task $task): self
    {
        return new self($task);
    }

    public static function createNull(): self
    {
        return new self(new NullTask());
    }

    public function run(TaskRunner $runner): void
    {
        if ($this->state->isNot(JobState::WAITING())) {
            return;
        }

        $this->state = JobState::PROCESSING();

        \Amp\asyncCall(function () use ($runner) {
            $result = yield $runner->run($this->task);
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
}
