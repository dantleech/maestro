<?php

namespace Maestro\Task;

use Maestro\Task\Exception\NodeAlreadyExists;
use Maestro\Task\Exception\NodeDoesNotExist;
use Maestro\Task\Node;

class Graph
{
    /**
     * @var Node[]
     */
    private $nodes = [];

    /**
     * @var array<string,Edge[]>
     */
    private $edges = [];

    public function __construct(array $nodes, array $edges)
    {
        foreach ($nodes as $node) {
            $this->addNode($node);
        }

        foreach ($edges as $edge) {
            $this->addEdge($edge);
        }
    }

    private function addNode(Node $node)
    {
        if (isset($this->nodes[$node->name()])) {
            throw new NodeAlreadyExists(sprintf(
                'Node with name "%s" already exists',
                $node->name()
            ));
        }

        $this->nodes[$node->name()] = $node;
        $this->edges[$node->name()] = [];
    }

    private function addEdge(Edge $edge)
    {
        $this->validateNodeName($edge->to());
        $this->validateNodeName($edge->from());

        $this->edges[$edge->to()][] = $edge->from();
    }

    public function dependenciesOf(string $nodeName): Nodes
    {
        $this->validateNodeName($nodeName);
        return Nodes::fromNodes(array_map(function (string $nodeName) {
            return $this->nodes[$nodeName];
        }, $this->edges[$nodeName]));
    }

    private function validateNodeName(string $nodeName)
    {
        if (!isset($this->nodes[$nodeName])) {
            throw new NodeDoesNotExist(sprintf(
                'Node "%s" does not exist', $nodeName
            ));
        }
    }
}
