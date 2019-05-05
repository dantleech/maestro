<?php

namespace Maestro\Model\Job\QueueDispatcher;

use Maestro\Model\Job\JobDispatcher;
use Maestro\Model\Job\Queue;
use Maestro\Model\Job\QueueDispatcher;
use Maestro\Model\Job\Exception\JobFailure;
use Maestro\Model\Job\QueueMonitor;
use Maestro\Model\Job\QueueStatus;
use Maestro\Model\Job\Queues;
use Maestro\Model\Job\QueueStatuses;

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
                $queueStatus = QueueStatus::fromQueue($queue);
                $queueStatus = $queueStatus->queueStarted($queue);

                while ($job = $queue->dequeue()) {
                    $queueStatus = $this->monitor->update($queueStatus->jobStarted($queue, $job));

                    try {
                        $queueStatus = $this->monitor->update($queueStatus->jobFinished(
                            $job,
                            yield $this->dispatcher->dispatch($job)
                        ));
                    } catch (JobFailure $e) {
                        $queueStatus = $this->monitor->update($queueStatus->jobFailure($e));
                        break;
                    }
                }

                $queueStatus = $this->monitor->update($queueStatus->queueFinished($queue));

                return $queueStatus;
            });
        }

        return QueueStatuses::fromArray(\Amp\Promise\wait(\Amp\Promise\all($promises)));
    }
}
