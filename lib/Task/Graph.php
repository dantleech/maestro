<?php

namespace Maestro\Task;

use Maestro\Task\Exception\GraphContainsCircularDependencies;
use Maestro\Task\Exception\NodeDoesNotExist;
use RuntimeException;

class Graph
{
    /**
     * @var Nodes<Node>
     */
    private $nodes;

    /**
     * @var array<string,Edge[]>
     */
    private $toFromMap = [];

    /**
     * @var array<string,Edge[]>
     */
    private $fromToMap = [];

    /**
     * @var Edges<Edge>
     */
    private $edges;

    private function __construct(Nodes $nodes, Edges $edges)
    {
        $this->nodes = $nodes;
        $this->edges = $edges;

        if ($nodes->count() === 0) {
            throw new RuntimeException(
                'Graph must have at least one node'
            );
        }


        foreach ($nodes as $node) {
            $this->addNode($node);
        }

        foreach ($edges as $edge) {
            $this->addEdge($edge);
        }

        // validate and detect circular dependencies
        $this->roots();
    }

    public static function create(array $nodes, array $edges): self
    {
        return new self(Nodes::fromNodes($nodes), Edges::fromEdges($edges));
    }

    public function roots(): Nodes
    {
        $nodesWithNoOutboundEdges = array_diff(
            $this->nodes->names(),
            array_keys($this->fromToMap)
        );

        $nodes = [];
        foreach ($nodesWithNoOutboundEdges as $nodeName) {
            $nodes[] = $this->nodes->get($nodeName);
        }

        if (empty($nodes)) {
            throw new GraphContainsCircularDependencies(sprintf(
                'Graph contains circular dependencies'
            ));
        }

        return Nodes::fromNodes($nodes);
    }

    /**
     * @return Edges<Edge>
     */
    public function edges(): Edges
    {
        return $this->edges;
    }

    /**
     * @return Nodes<Node>
     */
    public function nodes(): Nodes
    {
        return $this->nodes;
    }

    public function dependentsFor(string $nodeName): Nodes
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

    public function ancestryFor(string $nodeName): Nodes
    {
        $this->validateNodeName($nodeName);

        $ancestry = Nodes::empty();

        if (!isset($this->fromToMap[$nodeName])) {
            return $ancestry;
        }

        $parents = $this->nodes->byIds(
            $this->fromToMap[$nodeName]
        );

        $ancestry = $ancestry->merge($parents);

        foreach ($parents as $parent) {
            assert($parent instanceof Node);
            $ancestry = $ancestry->merge($this->ancestryFor($parent->id()));
        }

        return $ancestry;
    }

    public function pruneFor(array $targets): Graph
    {
        $nodes = Nodes::empty();
        foreach ($targets as $target) {
            $node = $this->nodes->get($target);
            $ancestry = $this->ancestryFor($target);
            $ancestry = $ancestry->add($node);
            $nodes = $nodes->merge($ancestry);
        }

        $edges = $this->edges;

        foreach ($edges as $index => $edge) {
            if (!$nodes->containsId($edge->from())) {
                $edges = $edges->remove($edge);
            }

            if (!$nodes->containsId($edge->to())) {
                $edges = $edges->remove($edge);
            }
        }

        return new self($nodes, $edges);
    }

    public function descendantsFor(string $nodeName, array $seen = [], $level = 0): Nodes
    {
        if (isset($seen[$nodeName])) {
            return Nodes::empty();
        }

        $this->validateNodeName($nodeName);

        $nodes = Nodes::empty();

        if ($level > 0) {
            $nodes = Nodes::fromNodes([$this->nodes()->get($nodeName)]);
            $seen[$nodeName] = true;
        }

        $level++;
        foreach ($this->dependentsFor($nodeName) as $dependent) {
            $nodes = $nodes->merge(
                $this->descendantsFor($dependent->id(), $seen, $level)
            );
        }

        return $nodes;
    }

    public function pruneToDepth(int $depth): Graph
    {
        $nodes = [];
        $edges = $this->edges;

        foreach ($this->nodes as $node) {
            $nodeDepth = $this->ancestryFor($node->id())->count();

            if ($nodeDepth > $depth) {
                $edges = $edges->removeReferencesTo($node->id());
                continue;
            }

            $nodes[] = $node;
        }

        return new self(Nodes::fromNodes($nodes), $edges);
    }

    private function addNode(Node $node): void
    {
        $this->toFromMap[$node->id()] = [];
    }

    private function validateNodeName(string $nodeName): void
    {
        if (isset($this->nodes[$nodeName])) {
            return;
        }

        throw new NodeDoesNotExist(sprintf(
            'Node "%s" does not exist',
            $nodeName
        ));
    }

    private function addEdge(Edge $edge): void
    {
        $this->validateNodeName($edge->to());
        $this->validateNodeName($edge->from());

        $this->toFromMap[$edge->to()][] = $edge->from();
        $this->fromToMap[$edge->from()][] = $edge->to();
    }
}
