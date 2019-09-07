<?php

namespace Maestro\Library\Graph;

final class GraphBuilder
{
    private $nodes;
    private $edges;

    private function __construct(array $nodes = [], array $edges = [])
    {
        $this->nodes = $nodes;
        $this->edges = $edges;
    }

    public static function fromGraph(Graph $graph): self
    {
        return new self(
            iterator_to_array($graph->nodes()),
            iterator_to_array($graph->edges())
        );
    }

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
