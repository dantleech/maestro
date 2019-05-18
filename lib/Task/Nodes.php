<?php

namespace Maestro\Task;

use ArrayIterator;
use Countable;
use IteratorAggregate;

final class Nodes implements IteratorAggregate, Countable
{
    /**
     * @var array
     */
    private $nodes = [];

    private function __construct(array $nodes)
    {
        $this->nodes = $nodes;
    }

    public static function fromNodes(array $nodes): self
    {
        return new self($nodes);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function add(Node $node): Nodes
    {
        return new self(array_merge($this->nodes, [$node]));
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->nodes);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->nodes);
    }
}
