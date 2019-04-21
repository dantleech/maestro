<?php

namespace Maestro\Model\Job;

class Queue
{
    /**
     * @var Job[]
     */
    private $jobs;

    /**
     * @var string
     */
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function enqueue(Job $job)
    {
        $this->jobs[] = $job;
    }

    public function id()
    {
        return $this->id;
    }
}
