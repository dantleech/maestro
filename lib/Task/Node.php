<?php

namespace Maestro\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Task\Exception\TaskFailed;
use Maestro\Task\Task\NullTask;

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
    const NAMEPSPACE_SEPARATOR = '#';

    private $state;
    private $task;
    private $id;

    /**
     * @var Artifacts
     */
    private $artifacts;

    public function __construct(string $id, ?Task $task = null)
    {
        $this->state = State::WAITING();
        $this->task = $task ?: new NullTask();
        $this->id = $id;
        $this->artifacts = Artifacts::empty();
    }

    public static function create(string $id, ?Task $task = null): self
    {
        return new self($id, $task);
    }

    public function state(): State
    {
        return $this->state;
    }

    public function cancel(): void
    {
        $this->state = State::CANCELLED();
    }

    public function run(TaskRunner $taskRunner, Artifacts $artifacts): Promise
    {
        return \Amp\call(function () use ($taskRunner, $artifacts) {
            $this->state = State::BUSY();

            try {
                $artifacts = yield $taskRunner->run(
                    $this->task,
                    $artifacts
                );
                $this->state = State::IDLE();
            } catch (TaskFailed $failed) {
                $this->state = State::FAILED();
                $artifacts = $failed->artifacts();
            }

            $this->artifacts = $artifacts ?: Artifacts::empty();

            return new Success($artifacts);
        });
    }

    public function id(): string
    {
        return $this->id;
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
