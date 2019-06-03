<?php

namespace Maestro\Task;

use Maestro\Task\Exception\GraphContainsCircularDependencies;
use Maestro\Task\Exception\NodeAlreadyExists;
use Maestro\Task\Exception\NodeDoesNotExist;

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

    /**
     * @var array
     */
    private $edges = [];

    private function __construct(array $nodes, array $edges)
    {
        foreach ($nodes as $node) {
            $this->addNode($node);
        }

        foreach ($edges as $edge) {
            $this->addEdge($edge);
        }
    }

    public static function create(array $nodes, array $edges): self
    {
        return new self($nodes, $edges);
    }

    private function addNode(Node $node)
    {
        if (isset($this->nodes[$node->id()])) {
            throw new NodeAlreadyExists(sprintf(
                'Node with name "%s" already exists',
                $node->id()
            ));
        }

        $this->nodes[$node->id()] = $node;
        $this->toFromMap[$node->id()] = [];
    }

    public function dependentsOf(string $nodeName): Nodes
    {
        $this->validateNodeName($nodeName);

        return Nodes::fromNodes(array_map(function (string $nodeName) {
            return $this->nodes[$nodeName];
        }, $this->toFromMap[$nodeName]));
    }

    public function dependenciesFor(string $nodeName): Nodes
    {
        $this->validateNodeName($nodeName);

        if (!isset($this->fromToMap[$nodeName])) {
            return Nodes::empty();
        }

        return Nodes::fromNodes(array_map(function (string $nodeName) {
            return $this->nodes[$nodeName];
        }, $this->fromToMap[$nodeName]));
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
            assert($parent instanceof Node);
            $ancestry = $ancestry->merge($this->widthFirstAncestryOf($parent->id()));
        }

        return $ancestry;
    }

    private function validateNodeName(string $nodeName)
    {
        if (!isset($this->nodes[$nodeName])) {
            throw new NodeDoesNotExist(sprintf(
                'Node "%s" does not exist',
                $nodeName
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
        $this->edges[] = $edge;
    }

    /**
     * @return Edge[]
     */
    public function edges(): array
    {
        return $this->edges;
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

    public function node($nodeName): Node
    {
        $this->validateNodeName($nodeName);
        return $this->nodes[$nodeName];
    }

    public function nodes(): Nodes
    {
        return Nodes::fromNodes($this->nodes);
    }

    public function pruneTo(array $targets): Graph
    {
        $nodes = Nodes::empty();
        foreach ($targets as $target) {
            $node = $this->node($target);
            $ancestry = $this->widthFirstAncestryOf($target);
            $ancestry = $ancestry->add($node);
            $nodes = $nodes->merge($ancestry);
        }

        $edges = $this->edges;

        foreach ($edges as $index => $edge) {
            if (!$nodes->containsId($edge->from())) {
                unset($edges[$index]);
            }

            if (!$nodes->containsId($edge->to())) {
                unset($edges[$index]);
            }
        }

        return Graph::create(iterator_to_array($nodes), $edges);
    }

    public function descendantsOf(string $nodeName, array $seen = [], $level = 0): Nodes
    {
        if (isset($seen[$nodeName])) {
            return Nodes::empty();
        }

        $this->validateNodeName($nodeName);

        if ($level > 0) {
            $nodes = Nodes::fromNodes([$this->node($nodeName)]);
            $seen[$nodeName] = true;
        } else {
            $nodes = Nodes::empty();
        }

        $level++;
        foreach ($this->dependentsOf($nodeName) as $dependent) {
            $nodes = $nodes->merge(
                $this->descendantsOf($dependent->id(), $seen, $level)
            );
        }

        return $nodes;
    }
}
