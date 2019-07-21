<?php

namespace Maestro\Node;

use Amp\Success;
use Maestro\Loader\Instantiator;
use Maestro\Node\Exception\TaskFailed;
use Maestro\Node\Exception\TaskHandlerDidNotReturnEnvironment;
use Maestro\Node\Scheduler\AsapSchedule;
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

    /**
     * Create a new node, the options relate directly to the constructor
     * parameters.
     */
    public static function create(string $id, array $options = []): self
    {
        return Instantiator::create()->instantiate(self::class, array_merge($options, [
            'id' => $id,
        ]));
    }

    /**
     * Check to see if the node is scheduled, and if so check to see if it is
     * ready to be executed, if so change the state to WAITING. The node will
     * be executed on the next tick.
     *
     * Returns true if the node was changed to WAITING, false otherwise.
     */
    public function performScheduling(NodeStateMachine $stateMachine, SchedulerRegistry $registry): bool
    {
        if (
            $this->state->isScheduled() &&
            $registry->getFor($this->schedule)->shouldRun($this->schedule, $this)
        ) {
            $this->changeState($stateMachine, State::WAITING());
            return true;
        }

        return false;
    }

    /**
     * Change the state back to scheduled.
     *
     * WARNING: this may throw an exception if the current state does not allow
     *          this state transition.
     */
    public function reschedule(NodeStateMachine $stateMachine):void
    {
        $this->changeState($stateMachine, State::SCHEDULED());
    }

    /**
     * Return the state of the node
     */
    public function state(): State
    {
        return $this->state;
    }

    /**
     * Cancel the node. If a task is running it will be unaffected by this
     * operation (but the node state will change to CANCELLED immediately).
     */
    public function cancel(NodeStateMachine $stateMachine): void
    {
        $this->changeState($stateMachine, State::CANCELLED());
    }

    /**
     * Run the task.
     *
     * This atomic operation will set the node to BUSY, run the task then set
     * the state to DONE when the task finishes (in either a success or failure
     * state).
     */
    public function run(
        NodeStateMachine $stateMachine,
        SchedulerRegistry $schedulerRegistry,
        TaskRunner $taskRunner,
        Environment $environment
    ): void {
        \Amp\asyncCall(function () use ($stateMachine, $taskRunner, $environment, $schedulerRegistry) {
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
                $this->changeState($stateMachine, State::DONE());

                if ($schedulerRegistry->getFor($this->schedule)->shouldReschedule($this->schedule, $this)) {
                    $this->changeState($stateMachine, State::SCHEDULED());
                }
            } catch (TaskFailed $failed) {
                $this->taskResult = TaskResult::FAILURE();
                $this->changeState($stateMachine, State::DONE());
            }

            return new Success($environment);
        });
    }

    /**
     * Return the node ID
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Return the human readable label for the node
     */
    public function label(): string
    {
        return $this->label;
    }

    /**
     * Return the node's task
     */
    public function task(): Task
    {
        return $this->task;
    }

    /**
     * Return the environment that has been collected by an EXECUTED TASK,
     * otherwise the environment will be empty.
     */
    public function environment(): Environment
    {
        return $this->environment;
    }

    /**
     * Return the result state of the task.
     */
    public function taskResult(): TaskResult
    {
        return $this->taskResult;
    }

    private function changeState(NodeStateMachine $stateMachine, State $state): void
    {
        $this->state = $stateMachine->transition($this, $state);
    }
}
