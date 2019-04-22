<?php

namespace Maestro\Model\Job;

use ArrayIterator;
use IteratorAggregate;

class Queues implements IteratorAggregate
{
    /**
     * @var Queue[]
     */
    private $queues = [];

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function get(string $id): Queue
    {
        if (isset($this->queues[$id])) {
            return $this->queues[$id];
        }

        $this->queues[$id] = new Queue($id);

        return $this->queues[$id];
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->queues);
    }
}
