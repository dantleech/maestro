<?php

namespace Maestro\Task;

use Countable;

class Queue implements Countable
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
}
