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
    private $name;
    private $artifacts;

    public function __construct(NodeName $name, ?Task $task = null)
    {
        $this->state = State::WAITING();
        $this->task = $task ?: new NullTask();
        $this->name = $name;
        $this->artifacts = Artifacts::empty();
    }

    public static function create($name, ?Task $task = null): self
    {
        return new self(NodeName::fromUnknown($name), $task);
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

    public function name(): NodeName
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
