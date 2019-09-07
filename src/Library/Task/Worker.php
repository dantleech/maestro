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
            /** @var Job[] $jobs */
            $jobs = $this->buildJobs([]);

            while ($jobs) {
                foreach ($jobs as $index => $job) {
                    if ($job->state()->is(JobState::WAITING())) {
                        $job->run($this->taskRunner);
                        continue;
                    }

                    if ($job->state()->is(JobState::DONE())) {
                        unset($jobs[$index]);
                    }
                }

                $jobs = $this->buildJobs($jobs);
                yield new Delayed($this->milliSleep);
            }
        });
    }

    private function buildJobs(array $jobs): array
    {
        while (count($jobs) < $this->concurrency && $job = $this->queue->dequeue()) {
            $jobs[] = $job;
        }

        return $jobs;
    }
}
