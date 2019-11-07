<?php

namespace Maestro\Extension\Runner\Model;

use Maestro\Extension\File\Task\PurgeDirectoryTask;
use Maestro\Library\Graph\Edge;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\Node;

class PurgeWorkspaceModifier
{
    /**
     * @var string
     */
    private $workspacePath;

    public function __construct(string $workspacePath)
    {
        $this->workspacePath = $workspacePath;
    }

    public function modify(Graph $graph): Graph
    {
        $builder = $graph->builder();

        $node = Node::create(
            'purge',
            [
                'task' => new PurgeDirectoryTask($this->workspacePath)
            ],
        );
        $builder->addNode($node);

        foreach ($graph->roots() as $root) {
            $builder->addEdge(Edge::create($root->id(), $node->id()));
        }

        return $builder->build();
    }
}
