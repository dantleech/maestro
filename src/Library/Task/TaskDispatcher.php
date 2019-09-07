<?php

namespace Maestro\Library\Task;

use Amp\Promise;

class TaskDispatcher
{
    /**
     * @var Queue
     */
    private $queue;

    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
    }

    public function dispatch(Task $task): Promise
    {
        $job = Job::create($task);
        $this->queue->enqueue($job);

        return $job->result();
    }
}
