<?php

namespace Maestro\Node;

use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;

final class Edges implements IteratorAggregate, Countable
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

    public function add(Edge $edge): self
    {
        $edges = $this->edges;
        $edges[] = $edge;

        return new self($edges);
    }

    private function addEdge(Edge $edge)
    {
        $this->edges[] = $edge;
    }

    public function removeReferencesTo(string $nodeId): Edges
    {
        return new self(array_filter($this->edges, function (Edge $edge) use ($nodeId) {
            if ($edge->to() === $nodeId) {
                return false;
            }
            if ($edge->from() === $nodeId) {
                return false;
            }

            return true;
        }));
    }

    public function append(Edges $edges): Edges
    {
        return new self(array_merge($this->edges, $edges->edges));
    }

    public function to(string $nodeId): Edges
    {
        return new self(array_filter($this->edges, function (Edge $edge) use ($nodeId) {
            return $edge->to() === $nodeId;
        }));
    }

    public function from(string $nodeId): Edges
    {
        return new self(array_filter($this->edges, function (Edge $edge) use ($nodeId) {
            return $edge->from() === $nodeId;
        }));
    }

    public function count(): int
    {
        return count($this->edges);
    }

    public function toString(): string
    {
        return implode(', ', array_map(function (Edge $edge) {
            return sprintf('%s => %s', $edge->from(), $edge->to());
        }, $this->edges));
    }
}
