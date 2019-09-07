<?php

namespace Maestro\Library\Task\Queue;

use Maestro\Library\Task\Job;
use Maestro\Library\Task\Queue;

class FifoQueue implements Queue
{
    /**
     * @var Job[]
     */
    private $jobs = [];

    public function dequeue(): ?Job
    {
        return array_shift($this->jobs);
    }

    public function enqueue(Job $job): void
    {
        $this->jobs[] = $job;
    }
}
