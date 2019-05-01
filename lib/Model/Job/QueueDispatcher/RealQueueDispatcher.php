<?php

namespace Maestro\Model\Job\QueueDispatcher;

use DateTimeImmutable;
use Maestro\Model\Job\JobDispatcher;
use Maestro\Model\Job\Queue;
use Maestro\Model\Job\QueueDispatcher;
use Maestro\Model\Job\QueueDispatcher\Exception\JobFailure;
use Maestro\Model\Job\QueueStatus;
use Maestro\Model\Job\Queues;
use Maestro\Model\Job\QueueStatuses;

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
                    try {
                        $queueStatus->message = yield $this->dispatcher->dispatch($job);
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
