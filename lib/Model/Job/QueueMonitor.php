<?php

namespace Maestro\Model\Job;

final class QueueMonitor
{
    /**
     * @var array
     */
    private $statuses = [];

    public function __construct(array $statuses = [])
    {
        $this->statuses = $statuses;
    }

    public function update(QueueStatus $queueStatus): QueueStatus
    {
        $this->statuses[$queueStatus->id()] = $queueStatus;
        return $queueStatus;
    }

    /**
     * @return QueueStatuses<QueueStatus>
     */
    public function report(): QueueStatuses
    {
        return QueueStatuses::fromArray($this->statuses);
    }
}
