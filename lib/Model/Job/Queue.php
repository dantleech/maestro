<?php

namespace Maestro\Model\Job;

use Countable;

class Queue implements Countable
{
    /**
     * @var Job[]
     */
    private $jobs = [];

    /**
     * @var string
     */
    private $id;

    /**
     * @var QueueStatus
     */
    private $queueStatus;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->queueStatus = new QueueStatus();
        $this->queueStatus->success = true;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function enqueue(Job $job): void
    {
        $this->jobs[] = $job;
    }

    public function head(): ?Job
    {
        if (!isset($this->jobs[0])) {
            return null;
        }

        return $this->jobs[0];
    }

    public function dequeue(): ?Job
    {
        return array_shift($this->jobs);
    }

    public function prepend(Job $job): void
    {
        array_unshift($this->jobs, $job);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->jobs);
    }

    public function queueStatus(): QueueStatus
    {
        return $this->queueStatus;
    }
}
