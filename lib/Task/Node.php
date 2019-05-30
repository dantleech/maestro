<?php

namespace Maestro\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Task\Exception\TaskFailed;
use Maestro\Task\Task\NullTask;
use RuntimeException;

/**
 * The node represents a task in the task graph.
 *
 * It is responsible for:
 *
 * - Running the task and managing it's state.
 * - Aggregating task artifacts.
 */
final class Node
{
    private $state;
    private $task;
    private $name;

    /**
     * @var Artifacts
     */
    private $artifacts;

    public function __construct(string $name, ?Task $task = null)
    {
        $this->state = State::WAITING();
        $this->task = $task ?: new NullTask();
        $this->name = $name;
        $this->artifacts = Artifacts::empty();
    }

    public static function create(string $name, ?Task $task = null): self
    {
        return new self($name, $task);
    }

    public function state(): State
    {
        return $this->state;
    }

    public function run(TaskRunner $taskRunner, Artifacts $artifacts): Promise
    {
        return \Amp\call(function () use ($taskRunner, $artifacts) {
            $this->state = State::BUSY();

            try {
                $this->artifacts = yield $taskRunner->run(
                    $this->task,
                    $artifacts
                );
                $this->state = State::IDLE();
            } catch (TaskFailed $failed) {
                $this->state = State::FAILED();
                $artifacts = $failed->artifacts();
            }

            return new Success($artifacts);
        });
    }

    public function name(): string
    {
        return $this->name;
    }

    public function task(): Task
    {
        return $this->task;
    }

    public function artifacts(): Artifacts
    {
        return $this->artifacts;
    }
}
