<?php


namespace Phpactor\Extension\Maestro\Model;

use Amp\Promise;

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
