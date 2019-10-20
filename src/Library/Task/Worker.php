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
     '*/
    private $taskRunner;

    /**
     * @var int
     */
    private $milliSleep;

    /**
     * @var int
     */
    private $concurrency;

    /**
     * @var Job[]
     */
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

                    if (false === $job->state()->is(JobState::BUSY())) {
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
            if ($job->state()->is(JobState::BUSY())) {
                $inc++;
            }

            return $inc;
        }, 0);
    }

    public function processingTasks(): array
    {
        return array_map(function (Job $job) {
            return $job->task();
        }, array_filter($this->jobs, function (Job $job) {
            return $job->state()->is(JobState::BUSY());
        }));
    }

    private function buildJobs(): void
    {
        while (count($this->jobs) < $this->concurrency && $job = $this->queue->dequeue()) {
            $this->jobs[] = $job;
        }
    }
}
