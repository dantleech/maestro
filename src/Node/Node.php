<?php

namespace Maestro\Node;

use Amp\Success;
use Maestro\Loader\Instantiator;
use Maestro\Node\Exception\TaskFailed;
use Maestro\Node\Exception\TaskHandlerDidNotReturnEnvironment;
use Maestro\Node\Schedule\AsapSchedule;
use Maestro\Node\Task\NullTask;

/**
 * The node represents a task in the task graph.
 *
 * It is responsible for:
 *
 * - Running the task according to a given environment
 * - Storing the environment returned by a task
 */
final class Node
{
    const NAMEPSPACE_SEPARATOR = '/';

    private $task;
    private $id;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var string
     */
    private $label;

    /**
     * @var State
     */
    private $state;

    /**
     * @var TaskResult
     */
    private $taskResult;

    /**
     * @var Schedule
     */
    private $schedule;

    public function __construct(string $id, string $label = null, ?Task $task = null, ?Schedule $schedule = null)
    {
        $this->environment = Environment::empty();
        $this->id = $id;
        $this->label = $label ?: $id;
        $this->state = State::SCHEDULED();
        $this->task = $task ?: new NullTask();
        $this->taskResult = TaskResult::PENDING();
        $this->schedule = $schedule ?: new AsapSchedule();
    }

    public static function create(string $id, array $options = []): self
    {
        return Instantiator::create()->instantiate(self::class, array_merge($options, [
            'id' => $id,
        ]));
    }

    public function checkSchedule(NodeStateMachine $stateMachine): bool
    {
        if ($this->state->isScheduled() && $this->schedule->shouldRun($this)) {
            $this->changeState($stateMachine, State::WAITING());
            return true;
        }

        return false;
    }

    public function reschedule(NodeStateMachine $stateMachine):void
    {
        $this->changeState($stateMachine, State::SCHEDULED());
    }

    public function state(): State
    {
        return $this->state;
    }

    public function cancel(NodeStateMachine $stateMachine): void
    {
        $this->changeState($stateMachine, State::CANCELLED());
    }

    public function run(NodeStateMachine $stateMachine, TaskRunner $taskRunner, Environment $environment): void
    {
        \Amp\asyncCall(function () use ($stateMachine, $taskRunner, $environment) {
            $this->changeState($stateMachine, State::BUSY());

            try {
                $environment = yield $taskRunner->run(
                    $this->task,
                    $environment
                );
                if (!$environment instanceof Environment) {
                    throw new TaskHandlerDidNotReturnEnvironment(sprintf(
                        'Promise from task handler for tas "%s" did not return an environment, ' .
                        'all task handlers must return a modified/unmodified environment',
                        get_class($this->task)
                    ));
                }
                $this->environment = $environment;
                $this->taskResult = TaskResult::SUCCESS();

                if ($this->schedule->shouldReschedule($this)) {
                    $this->changeState($stateMachine, State::SCHEDULED());
                }
            } catch (TaskFailed $failed) {
                $this->taskResult = TaskResult::FAILURE();
            }

            $this->changeState($stateMachine, State::DONE());

            return new Success($environment);
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

    public function environment(): Environment
    {
        return $this->environment;
    }

    private function changeState(NodeStateMachine $stateMachine, State $state): void
    {
        $this->state = $stateMachine->transition($this, $state);
    }

    public function taskResult(): TaskResult
    {
        return $this->taskResult;
    }
}
