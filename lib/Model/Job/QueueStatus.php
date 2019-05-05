<?php

namespace Maestro\Model\Job;

use DateTimeImmutable;
use Maestro\Model\Job\Exception\JobFailure;
use RuntimeException;

class QueueStatus
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $code = 0;

    /**
     * @var string|null
     */
    private $message;

    /**
     * @var DateTimeImmutable
     */
    private $start;

    /**
     * @var DateTimeImmutable|null
     */
    private $end;

    /**
     * @var int
     */
    private $size;

    /**
     * @var Job|null
     */
    private $currentJob;

    /**
     * @var QueueState
     */
    private $state;

    /**
     * @var int
     */
    private $maxSize;

    public function __construct(string $id, int $size)
    {
        $this->id = $id;
        $this->size = $size;
        $this->maxSize = $size;
        $this->state = QueueState::PENDING();
    }

    public function isRunning(): bool
    {
        return $this->end !== null;
    }

    public static function fromQueue(Queue $queue): self
    {
        $new = new self($queue->id(), count($queue));
        return $new;
    }

    public function queueStarted(Queue $queue): self
    {
        $clone = clone $this;
        $clone->start = new DateTimeImmutable();
        $clone->state = QueueState::RUNNING();
        return $clone;
    }


    public function queueFinished(Queue $queue): self
    {
        $clone = clone $this;
        $clone->end = new DateTimeImmutable();

        if (!$this->state->isFailed()) {
            $clone->state = QueueState::DONE();
        }

        return $clone;
    }

    public function jobStarted(Queue $queue, Job $job): self
    {
        $clone = clone $this;
        $clone->size = count($queue);
        $clone->currentJob = $job;
        $clone->state = QueueState::RUNNING();
        return $clone;
    }

    public function jobFinished(Queue $queue, Job $job, ?string $message): self
    {
        $clone = clone $this;
        $clone->size = count($queue);

        if (count($queue) > $this->maxSize) {
            $this->maxSize = count($queue);
        }

        $clone->currentJob = null;
        $clone->message = $message;
        $clone->state = QueueState::PENDING();
        return $clone;
    }

    public function jobFailure(JobFailure $fail): self
    {
        $clone = clone $this;
        $clone->code = $fail->getCode();
        $clone->message = $fail->getMessage();
        $clone->end = new DateTimeImmutable();
        $clone->state = QueueState::FAILED();

        return $clone;
    }

    public function id(): ?string
    {
        return $this->id;
    }

    public function code(): ?int
    {
        return $this->code;
    }

    public function message(): ?string
    {
        return $this->message;
    }

    public function start(): DateTimeImmutable
    {
        return $this->start;
    }

    public function end(): DateTimeImmutable
    {
        if (!$this->end) {
            throw new RuntimeException(sprintf(
                'End time for queue "%s" has not been set',
                $this->id
            ));
        }

        return $this->end;
    }

    public function size(): int
    {
        return $this->size;
    }

    public function currentJob(): ?Job
    {
        return $this->currentJob;
    }

    public function state(): QueueState
    {
        return $this->state;
    }

    public function maxSize(): int
    {
        return $this->maxSize;
    }
}
