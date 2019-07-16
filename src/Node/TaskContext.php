<?php

namespace Maestro\Node;

class TaskContext
{
    private $containingNode;
    private $artifacts;

    public function __construct(
        Node $containingNode,
        Artifacts $artifacts
    ) {
        $this->containingNode = $containingNode;
        $this->artifacts = $artifacts;
    }

    public function artifacts(): Artifacts
    {
        return $this->artifacts;
    }

    public function containingNode(): Node
    {
        return $this->containingNode;
    }
}
