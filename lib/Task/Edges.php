<?php

namespace Maestro\Task;

use ArrayIterator;
use Iterator;
use IteratorAggregate;

final class Edges implements IteratorAggregate
{
    /**
     * @var array
     */
    private $edges;

    public function __construct(array $edges)
    {
        $this->edges = $edges;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->edges);
    }
}
