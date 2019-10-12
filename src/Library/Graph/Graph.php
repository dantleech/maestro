<?php

namespace Maestro\Library\Graph;

use Maestro\Library\Graph\Exception\GraphContainsCircularDependencies;
use Maestro\Library\Graph\Exception\NodeDoesNotExist;

class Graph
{
    /**
     * @var Nodes<Node>
     */
    private $nodes;

    /**
     * @var Edges<Edge>
     */
    private $edges;

    public function __construct(Nodes $nodes, Edges $edges)
    {
        $this->nodes = $nodes;
        $this->edges = $edges;

        $this->validateEdges();
        $this->validateCircularDependencies();
    }

    public static function create(array $nodes, array $edges): self
    {
        return new self(Nodes::fromNodes($nodes), Edges::fromEdges($edges));
    }

    /**
     * @return Nodes<Node>
     */
    public function roots(): Nodes
    {
        $nodesWithNoOutboundEdges = array_diff(
            $this->nodes->ids(),
            $this->edges->fromIds()
        );

        $nodes = [];
        foreach ($nodesWithNoOutboundEdges as $nodeName) {
            $nodes[] = $this->nodes->get($nodeName);
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
        return Nodes::fromNodes(array_map(function (string $nodeName) {
            return $this->nodes[$nodeName];
        }, $this->edges->to($nodeName)->fromIds()));
    }

    /**
     * @return Nodes<Node>
     */
    public function dependenciesFor(string $nodeName): Nodes
    {
        return Nodes::fromNodes(array_map(function (string $nodeName) {
            return $this->nodes[$nodeName];
        }, $this->edges->from($nodeName)->toIds()));
    }

    public function ancestryFor(string $nodeName, array $seen = []): Nodes
    {
        $ancestry = Nodes::empty();

        $parents = $this->nodes->byIds(
            ...$this->edges->from($nodeName)->toIds()
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

    public function pruneForTags(string ...$tags): Graph
    {
        return $this->pruneFor($this->nodes->byTags(...$tags)->ids());
    }

    /**
     * @return Nodes<Node>
     */
    public function descendantsFor(string $nodeName, array $seen = [], $level = 0): Nodes
    {
        if (isset($seen[$nodeName])) {
            return Nodes::empty();
        }


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

    /**
     * @return Nodes<Node>
     */
    public function leafs(): Nodes
    {
        return $this->nodes->filter(function (Node $node) {
            return $this->edges->to($node->id())->count() === 0;
        });
    }

    public function builder(): GraphBuilder
    {
        return GraphBuilder::fromGraph($this);
    }

    /**
     * Validate the graph using a reverse version of Kahn's algorithm
     *
     *    https://en.wikipedia.org/wiki/Topological_sorting#Kahn
     *
     * Note that we reverse it as the graph is built as a dependency tree (so
     * children have edges _from_ themselves _to_ the parent.
     */
    private function validateCircularDependencies()
    {
        $nodes = iterator_to_array($this->roots());
        $sortedElements = [];
        $edges = clone $this->edges;

        while ($nodes) {
            $node = array_pop($nodes);
            $sortedElements[] = $node;

            foreach ($edges->to($node->id()) as $edge) {
                $destNode = $this->nodes->get($edge->from());
                $edges = $edges->remove($edge);

                if ($edges->from($destNode->id())->count() === 0) {
                    $nodes[] = $destNode;
                }
            }
        }
        if ($edges->count()) {
            throw new GraphContainsCircularDependencies(sprintf(
                'Graph contains circular references (sorry): %s',
                $edges->toString()
            ));
        }
    }

    private function validateEdges(): void
    {
        if (!$diff = array_diff($this->edges->allIds(), $this->nodes->ids())) {
            return;
        }

        throw new NodeDoesNotExist(sprintf(
            'Node(s) do not exist "%s"',
            implode('", "', $diff)
        ));
    }
}
