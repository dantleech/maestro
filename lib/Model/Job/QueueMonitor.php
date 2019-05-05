<?php

namespace Maestro\Model\Job;

use IteratorAggregate;
use Maestro\Model\Job\Event\JobException;
use Maestro\Model\Job\Event\JobStarted;
use Maestro\Model\Job\Event\JobSuccess;
use Maestro\Model\Job\Event\QueueStarted;
use Maestro\Model\Job\QueueStatus;

final class QueueMonitor
{
    /**
     * @var array
     */
    private $statuses = [];

    public function update(QueueStatus $queueStatus)
    {
        $this->statuses[$queueStatus->id] = $queueStatus;
    }

    /**
     * @return QueueStatuses<QueueStatus>
     */
    public function report(): QueueStatuses
    {
        return QueueStatuses::fromArray($this->statuses);
    }
}
