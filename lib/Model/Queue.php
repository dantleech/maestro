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

    public function run(): Promise
    {
        return \Amp\call(function () {
            while ($job = array_shift($this->jobs)) {
               yield $job->execute();
            }
        });
    }
}
