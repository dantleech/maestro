<?php

namespace Maestro\Node;

class TaskContext
{
    private $containingNode;
    private $artifacts;
    private $graph;

    public function __construct(
        ?Node $containingNode = null,
        ?Artifacts $artifacts = null,
        ?Graph $graph = null
    ) {
        $this->containingNode = $containingNode ?: Node::create('root');
        $this->artifacts = $artifacts ?: Artifacts::empty();
        $this->graph = $graph ?: Graph::create([$this->containingNode], []);
    }

    public function artifacts(): Artifacts
    {
        return $this->artifacts;
    }

    public function containingNode(): Node
    {
        return $this->containingNode;
    }

    public function graph(): Graph
    {
        return $this->graph;
    }
}
