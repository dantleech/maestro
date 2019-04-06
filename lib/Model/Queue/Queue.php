<?php


namespace Phpactor\Extension\Maestro\Model\Queue;

use Amp\Promise;
use Phpactor\Extension\Maestro\Model\Job\Job;

class Queue
{
    private $jobs = [];

    public function enqueue(Job $job): void
    {
        $this->jobs[] = $job;
    }

    public function dequeue(): ?Job
    {
        return array_shift($this->jobs);
    }
}
