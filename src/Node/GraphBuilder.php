<?php

namespace Maestro\Node;

final class GraphBuilder
{
    private $nodes = [];
    private $edges = [];

    public static function create(): self
    {
        return new self();
    }

    public function addNode(Node $node): self
    {
        $this->nodes[] = $node;
        return $this;
    }

    public function addEdge(Edge $edge): self
    {
        $this->edges[] = $edge;
        return $this;
    }

    public function build(): Graph
    {
        return new Graph(
            Nodes::fromNodes($this->nodes),
            Edges::fromEdges($this->edges)
        );
    }
}
