<?php

namespace Maestro\Model\Job;

use ArrayIterator;
use IteratorAggregate;

class QueueStatuses implements IteratorAggregate
{
    /**
     * @var array
     */
    private $queueStatuses = [];

    public function __construct(array $queueStatuses)
    {
        foreach ($queueStatuses as $queueStatus) {
            $this->add($queueStatus);
        }
    }

    public static function fromArray(array $queueStatuses)
    {
        return new self($queueStatuses);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->queueStatuses);
    }

    private function add(QueueStatus $queueStatus)
    {
        $this->queueStatuses[] = $queueStatus;
    }
}
