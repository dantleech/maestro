<?php

namespace Maestro\Model\Job;

class QueueManager
{
    /**
     * @var Queue[]
     */
    private $queues = [];

    /**
     * @var JobDispatcher
     */
    private $dispatcher;

    public function __construct(JobDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function getOrCreate(string $id): Queue
    {
        if (isset($this->queues[$id])) {
            return $this->queues[$id];
        }

        $this->queues[$id] = new Queue($id);

        return $this->queues[$id];
    }

    public function dispatch(): void
    {
        $promises = [];
        foreach ($this->queues as $queue) {
            $promises[] = \Amp\call(function () use ($queue) {
                while ($job = $queue->dequeue()) {
                    yield $this->dispatcher->dispatch($job);
                }
            });
        }

        \Amp\Promise\wait(\Amp\Promise\all($promises));
    }
}
