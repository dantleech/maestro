<?php

namespace Maestro\Task;

use ArrayIterator;
use Iterator;
use IteratorAggregate;

final class Edges implements IteratorAggregate
{
    /**
     * @var Edge[]
     */
    private $edges = [];

    private function __construct(array $edges)
    {
        foreach ($edges as $edge) {
            $this->addEdge($edge);
        }
    }

    public static function fromEdges(array $edges): self
    {
        return new self($edges);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->edges);
    }

    public function remove(Edge $targetEdge): Edges
    {
        $edges = [];

        foreach ($this->edges as $edge) {
            if ($targetEdge === $edge) {
                continue;
            }

            $edges[] = $edge;
        }

        return new self($edges);
    }

    private function addEdge(Edge $edge)
    {
        $this->edges[] = $edge;
    }
}
