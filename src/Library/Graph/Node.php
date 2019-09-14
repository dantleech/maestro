<?php

namespace Maestro\Library\Graph;

use Maestro\Library\Instantiator\Instantiator;
use Maestro\Library\Task\Job;
use Maestro\Library\Task\Queue;
use Maestro\Library\Task\Task;
use Maestro\Library\Task\Task\NullTask;

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

    /**
     * @var object[]
     */
    private $artifacts = [];

    private $id;

    /**
     * @var string
     */
    private $label;

    /**
     * @var State
     */
    private $state;

    /**
     * @var array
     */
    private $tags;

    public function __construct(
        string $id,
        string $label = null,
        ?Task $task = null,
        array $tags = []
    ) {
        $this->id = $id;
        $this->label = $label ?: $id;
        $this->state = State::IDLE();
        $this->task = $task ?: new NullTask();
        $this->tags = $tags;
    }

    /**
     * Create a new node, the options relate directly to the constructor
     * parameters.
     */
    public static function create(string $id, array $options = []): self
    {
        return Instantiator::create(self::class, array_merge($options, [
            'id' => $id,
        ]));
    }

    /**
     * Return the state of the node
     */
    public function state(): State
    {
        return $this->state;
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
     * @return string[]
     */
    public function tags(): array
    {
        return $this->tags;
    }

    public function run(Queue $queue, array $artifacts): void
    {
        \Amp\asyncCall(function () use ($queue, $artifacts) {
            $this->state = State::DISPATCHED();
            $job = Job::create($this->task, $artifacts);
            $queue->enqueue($job);
            $this->artifacts = yield $job->result();
            $this->state = State::DONE();
        });
    }

    public function artifacts(): array
    {
        return $this->artifacts;
    }
}
