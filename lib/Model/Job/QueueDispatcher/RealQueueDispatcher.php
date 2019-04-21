<?php

namespace Maestro\Model\Job\QueueDispatcher;

use Maestro\Model\Job\JobDispatcher;
use Maestro\Model\Job\Queue;
use Maestro\Model\Job\QueueDispatcher;
use Maestro\Model\Job\QueueRegistry;
use Maestro\Model\Job\Queues;

class RealQueueDispatcher implements QueueDispatcher
{
    /**
     * @var JobDispatcher
     */
    private $dispatcher;

    public function __construct(JobDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function dispatch(Queues $queues): void
    {
        $promises = [];
        foreach ($queues as $queue) {
            $promises[] = \Amp\call(function () use ($queue) {
                while ($job = $queue->dequeue()) {
                    yield $this->dispatcher->dispatch($job);
                }
            });
        }

        \Amp\Promise\wait(\Amp\Promise\all($promises));
    }
}
