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

    /**
     * @var array
     */
    private $artifacts;

    private function __construct(Task $task, array $artifacts = [])
    {
        $this->task = $task;
        $this->deferred = new Deferred();
        $this->state = JobState::WAITING();
        $this->artifacts = $artifacts;
    }

    public static function create(Task $task, array $artifacts = []): Job
    {
        return new self($task, $artifacts);
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
            $result = yield $runner->run($this->task, $this->artifacts);
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

    public function artifacts(): array
    {
        return $this->artifacts;
    }
}
