<?php

namespace Maestro\Library\Graph;

use Exception;
use Maestro\Library\GraphTask\Artifacts;
use Maestro\Library\Instantiator\Instantiator;
use Maestro\Library\Task\Job;
use Maestro\Library\Task\JobState;
use Maestro\Library\Task\Queue;
use Maestro\Library\Task\Task;
use Maestro\Library\Task\Task\NullTask;
use RuntimeException;

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

    /**
     * @var Exception|null
     */
    private $exception;

    public function __construct(
        string $id,
        string $label = null,
        ?Task $task = null,
        array $tags = [],
        array $artifacts = []
    ) {
        $this->id = $id;
        $this->label = $label ?: $id;
        $this->state = State::IDLE();
        $this->task = $task ?: new NullTask();
        $this->tags = $tags;
        $this->artifacts = $artifacts;
    }

    /**
     * Create a new node, the options relate directly to the constructor
     * parameters.
     */
    public static function create(string $id, array $options = []): self
    {
        return Instantiator::instantiate(self::class, array_merge($options, [
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

    public function run(Queue $queue, Artifacts $artifacts): void
    {
        \Amp\asyncCall(function () use ($queue, $artifacts) {
            $this->state = State::DISPATCHED();
            $job = Job::create($this->task, $artifacts);
            $queue->enqueue($job);

            $artifacts = yield $job->result();

            if (!is_array($artifacts)) {
                throw new RuntimeException(sprintf(
                    'Node Task handler for "%s" was expected to return an array of artifacts, got "%s"',
                    get_class($this->task),
                    gettype($artifacts)
                ));
            }

            if ($job->state()->is(JobState::FAILED())) {
                $this->state = State::FAILED();
                $this->exception = $job->failure();
                return;
            }

            $this->artifacts = $artifacts;
            $this->state = State::DONE();
        });
    }

    public function artifacts(): array
    {
        return $this->artifacts;
    }

    public function cancel(): void
    {
        $this->state = State::CANCELLED();
    }

    public function exception(): ?Exception
    {
        return $this->exception;
    }
}
