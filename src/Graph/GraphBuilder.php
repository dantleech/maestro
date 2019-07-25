<?php

namespace Maestro\Graph;

final class GraphBuilder
{
    private $nodes = [];
    private $edges = [];

    public static function create(): self
    {
        return new self();
    }

    public function addNode(Node $node): void
    {
        $this->nodes[] = $node;
    }

    public function addEdge(Edge $edge): void
    {
        $this->edges[] = $edge;
    }

    public function build(): Graph
    {
        return new Graph(
            Nodes::fromNodes($this->nodes),
            Edges::fromEdges($this->edges)
        );
    }
}
