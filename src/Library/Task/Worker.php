<?php

namespace Maestro\Library\Task;

use Amp\Delayed;

class Worker
{
    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var TaskRunner
     */
    private $taskRunner;

    /**
     * @var int
     */
    private $milliSleep;

    /**
     * @var int
     */
    private $concurrency;

    private $jobs = [];

    public function __construct(
        TaskRunner $taskRunner,
        Queue $queue,
        int $milliSleep = 1,
        int $concurrency = 10
    ) {
        $this->queue = $queue;
        $this->taskRunner = $taskRunner;
        $this->milliSleep = $milliSleep;
        $this->concurrency = $concurrency;
    }

    public function start(): void
    {
        \Amp\asyncCall(function () {
            $this->buildJobs();

            while ($this->jobs) {
                foreach ($this->jobs as $index => $job) {
                    if ($job->state()->is(JobState::WAITING())) {
                        $job->run($this->taskRunner);
                        continue;
                    }

                    if ($job->state()->is(JobState::DONE())) {
                        unset($this->jobs[$index]);
                    }
                }

                yield new Delayed($this->milliSleep);
                $this->buildJobs();
            }
        });
    }

    public function processingJobCount(): int
    {
        return array_reduce($this->jobs, function ($inc, Job $job) {
            if ($job->state()->is(JobState::PROCESSING())) {
                $inc++;
            }

            return $inc;
        }, 0);
    }

    private function buildJobs(): void
    {
        while (count($this->jobs) < $this->concurrency && $job = $this->queue->dequeue()) {
            $this->jobs[] = $job;
        }
    }
}
