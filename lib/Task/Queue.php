<?php

namespace Maestro\Task;

class Queue
{
    /**
     * @var array
     */
    private $nodes;

    public function enqueue(Node $node): void
    {
        $this->nodes[] = $node;
    }

    public function dequeue(): ?Node
    {
        return array_shift($this->nodes);
    }
}
