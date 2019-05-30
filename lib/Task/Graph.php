<?php

namespace Maestro\Task;

use Maestro\Task\Exception\GraphContainsCircularDependencies;
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
    private $toFromMap = [];

    /**
     * @var array<string,Edge[]>
     */
    private $fromToMap = [];

    public function __construct(array $nodes, array $edges)
    {
        foreach ($nodes as $node) {
            $this->addNode($node);
        }

        foreach ($edges as $edge) {
            $this->addEdge($edge);
        }
    }

    public static function create(array $nodes, array $edges)
    {
        return new self($nodes, $edges);
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
        $this->toFromMap[$node->name()] = [];
    }

    public function dependentsOf(string $nodeName): Nodes
    {
        $this->validateNodeName($nodeName);

        return Nodes::fromNodes(array_map(function (string $nodeName) {
            return $this->nodes[$nodeName];
        }, $this->toFromMap[$nodeName]));
    }

    public function widthFirstAncestryOf(string $nodeName): Nodes
    {
        $this->validateNodeName($nodeName);

        $ancestry = Nodes::empty();

        if (!isset($this->fromToMap[$nodeName])) {
            return $ancestry;
        }

        $parents = $this->nodesByNames(
            $this->fromToMap[$nodeName]
        );

        $ancestry = $ancestry->merge($parents);

        foreach ($parents as $parent) {
            $ancestry = $ancestry->merge($this->widthFirstAncestryOf($parent->name()));
        }

        return $ancestry;
    }

    private function validateNodeName(string $nodeName)
    {
        if (!isset($this->nodes[$nodeName])) {
            throw new NodeDoesNotExist(sprintf(
                'Node "%s" does not exist', $nodeName
            ));
        }
    }

    public function roots(): Nodes
    {
        $nodesWithNoOutboundEdges = array_diff(
            $this->nodeNames(),
            array_keys($this->fromToMap)
        );

        $nodes = [];
        foreach ($nodesWithNoOutboundEdges as $nodeName) {
            $nodes[] = $this->nodes[$nodeName];
        }

        if (empty($nodes)) {
            throw new GraphContainsCircularDependencies(sprintf(
                'Graph contains circular dependencies'
            ));
        }

        return Nodes::fromNodes($nodes);
    }

    public function allDone(): bool
    {
        foreach ($this->nodes as $node) {
            if ($node->state()->isBusy() || $node->state()->isWaiting()) {
                return false;
            }
        }

        return true;
    }

    private function addEdge(Edge $edge)
    {
        $this->validateNodeName($edge->to());
        $this->validateNodeName($edge->from());

        $this->toFromMap[$edge->to()][] = $edge->from();
        $this->fromToMap[$edge->from()][] = $edge->to();
    }

    private function nodesByNames(array $nodeNames): Nodes
    {
        return Nodes::fromNodes(array_map(function (string $nodeName) {
            return $this->nodes[$nodeName];
        }, $nodeNames));
    }

    private function nodeNames(): array
    {
        return Nodes::fromNodes($this->nodes)->names();
    }
}
