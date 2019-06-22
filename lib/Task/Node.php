<?php

namespace Maestro\Task;

use Amp\Success;
use Maestro\Loader\Instantiator;
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
    const NAMEPSPACE_SEPARATOR = '/';

    private $task;
    private $id;

    /**
     * @var Artifacts
     */
    private $artifacts;

    /**
     * @var string
     */
    private $label;

    /**
     * @var NodeStateMachine
     */
    private $stateMachine;

    public function __construct(string $id, string $label = null, ?Task $task = null)
    {
        $this->stateMachine = new NodeStateMachine();
        $this->task = $task ?: new NullTask();
        $this->id = $id;
        $this->artifacts = Artifacts::empty();
        $this->label = $label ?: $id;
    }

    public static function create(string $id, array $options = []): self
    {
        return Instantiator::create()->instantiate(self::class, array_merge($options, [
            'id' => $id,
        ]));
    }

    public function state(): State
    {
        return $this->stateMachine->state();
    }

    public function cancel(): void
    {
        $this->stateMachine->changeTo(State::CANCELLED());
    }

    public function run(TaskRunner $taskRunner, Artifacts $artifacts): void
    {
        \Amp\asyncCall(function () use ($taskRunner, $artifacts) {
            $this->stateMachine->changeTo(State::BUSY());

            try {
                $artifacts = yield $taskRunner->run(
                    $this->task,
                    $artifacts
                );
                $this->stateMachine->changeTo(State::DONE());
            } catch (TaskFailed $failed) {
                $this->stateMachine->changeTo(State::FAILED());
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

    public function label(): string
    {
        return $this->label;
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
