<?php

namespace Maestro\Model\Job\QueueDispatcher;

use DateTimeImmutable;
use Maestro\Model\Job\JobDispatcher;
use Maestro\Model\Job\Queue;
use Maestro\Model\Job\QueueDispatcher;
use Maestro\Model\Job\Exception\JobFailure;
use Maestro\Model\Job\QueueMonitor;
use Maestro\Model\Job\QueueStatus;
use Maestro\Model\Job\Queues;
use Maestro\Model\Job\QueueStatuses;
use Maestro\Model\Job\QueueDispatcherObserver;

class RealQueueDispatcher implements QueueDispatcher
{
    /**
     * @var JobDispatcher
     */
    private $dispatcher;

    /**
     * @var QueueMonitor
     */
    private $monitor;

    public function __construct(JobDispatcher $dispatcher, QueueMonitor $monitor)
    {
        $this->dispatcher = $dispatcher;
        $this->monitor = $monitor;
    }

    public function dispatch(Queues $queues): QueueStatuses
    {
        $promises = [];
        foreach ($queues as $queue) {
            assert($queue instanceof Queue);
            $promises[] = \Amp\call(function () use ($queue) {

                $queueStatus = new QueueStatus();
                $queueStatus->success = true;
                $queueStatus->id = $queue->id();
                $queueStatus->start = new DateTimeImmutable();

                while ($job = $queue->dequeue()) {
                    $queueStatus->size = count($queue);
                    $this->monitor->update($queueStatus);
                    try {
                        $queueStatus->currentJob = $job;
                        $queueStatus->message = yield $this->dispatcher->dispatch($job);
                        $queueStatus->currentJob = null;
                    } catch (JobFailure $e) {
                        $queueStatus->success = false;
                        $queueStatus->code = $e->getCode();
                        $queueStatus->message = $e->getMessage();
                        break;
                    }
                }
                $queueStatus->end = new DateTimeImmutable();

                return $queueStatus;
            });
        }

        return QueueStatuses::fromArray(\Amp\Promise\wait(\Amp\Promise\all($promises)));
    }
}
