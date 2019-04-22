<?php

namespace Maestro\Model\Job;

class Queue
{
    /**
     * @var Job[]
     */
    private $jobs = [];

    /**
     * @var string
     */
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function enqueue(Job $job): void
    {
        $this->jobs[] = $job;
    }

    public function dequeue(): ?Job
    {
        return array_shift($this->jobs);
    }

    public function prepend(Job $job): void
    {
        array_unshift($this->jobs, $job);
    }
}
