<?php

namespace Maestro\Model\Job\QueueDispatcher;

use Exception;
use Generator;
use Maestro\Model\Job\Exception\DispatchException;
use Maestro\Model\Job\Job;
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

    /**
     * @var int|null
     */
    private $concurrency;

    public function __construct(JobDispatcher $dispatcher, QueueMonitor $monitor, int $concurrency = null)
    {
        $this->dispatcher = $dispatcher;
        $this->monitor = $monitor;
        $this->concurrency = $concurrency;
    }

    public function dispatch(Queues $queues): QueueStatuses
    {
        $promises = [];
        $resolvedPromises = [];
        $concurrency = 0;

        foreach ($queues as $queue) {
            $queueStatus = QueueStatus::fromQueue($queue);
            $queueStatus = $this->monitor->update($queueStatus);
        }

        foreach ($queues as $queue) {
            assert($queue instanceof Queue);

            // if concurrency is enabled and we meet the threshold, then wait for one
            // of the existing promises to finish save it's result and remove that
            // promise from the set of "pending" promises.
            if (null !== $this->concurrency && count($promises) >= $this->concurrency) {
                try {
                    $result = \Amp\Promise\wait(\Amp\Promise\first($promises));
                } catch (Exception $e) {
                    throw DispatchException::fromException($e);
                }

                unset($promises[$result->id()]);
                $resolvedPromises[] = $result;
            }

            $promises[$queue->id()] = \Amp\call(function () use ($queue) {
                $queueStatus = QueueStatus::fromQueue($queue);
                $queueStatus = $queueStatus->queueStarted($queue);

                while ($job = $queue->dequeue()) {
                    $queueStatus = yield from $this->processJob($queueStatus, $queue, $job);
                }

                $queueStatus = $this->monitor->update($queueStatus->queueFinished($queue));

                return $queueStatus;
            });
        }

        return QueueStatuses::fromArray(array_merge(
            $resolvedPromises,
            \Amp\Promise\wait(\Amp\Promise\all($promises))
        ));
    }

    private function processJob(QueueStatus $queueStatus, Queue $queue, Job $job): Generator
    {
        $queueStatus = $this->monitor->update($queueStatus->jobStarted($queue, $job));
        
        try {
            return $this->monitor->update($queueStatus->jobFinished(
                $queue,
                $job,
                yield $this->dispatcher->dispatch($job)
            ));
        } catch (JobFailure $e) {
            return $this->monitor->update($queueStatus->jobFailure($e));
        }
    }
}
