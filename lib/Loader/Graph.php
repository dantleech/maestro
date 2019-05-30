<?php

namespace Maestro\Loader;

use Maestro\Task\Node;

class Graph
{
    /**
     * @var array
     */
    private $nodes = [];

    /**
     * @var array
     */
    private $edges = [];

    public function __construct(array $nodes, array $edges)
    {
        foreach ($nodes as $node) {
            $this->addNode($node);
        }

        foreach ($edges as $node) {
            $this->addEdge($edge);
        }
    }

    private function addNode(Node $node)
    {
        if (isset($this->nodes[$node->name()])) {
            throw new 
        }
        $this->nodes[$node->name()] = $node;
    }

    private function addEdge(Edge $edge)
    {
    }
}
