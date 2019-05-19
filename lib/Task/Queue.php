<?php

namespace Maestro\Task;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class Queue implements Countable, IteratorAggregate
{
    /**
     * @var array
     */
    private $nodes = [];

    public function enqueue(Node $node): void
    {
        $this->nodes[] = $node;
    }

    public function dequeue(): ?Node
    {
        return array_shift($this->nodes);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->nodes);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->nodes);
    }
}
