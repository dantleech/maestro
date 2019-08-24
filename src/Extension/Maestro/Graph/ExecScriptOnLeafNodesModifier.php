<?php

namespace Maestro\Extension\Maestro\Graph;

use Maestro\Extension\Maestro\Task\ScriptTask;
use Maestro\Graph\Edge;
use Maestro\Graph\Graph;
use Maestro\Graph\Node;

final class ExecScriptOnLeafNodesModifier
{
    /**
     * @var string
     */
    private $script;

    public function __construct(string $script)
    {
        $this->script = $script;
    }

    public function __invoke(Graph $graph): Graph
    {
        $builder = $graph->builder();
        foreach ($graph->leafs() as $leaf) {
            $scriptNodeId = sprintf($leaf->id() . '/script');
            $builder->addNode(Node::create($scriptNodeId, [
                'label' => 'script',
                'task' => new ScriptTask($this->script)
            ]));
            $builder->addEdge(Edge::create($scriptNodeId, $leaf->id()));
        }

        return $builder->build();
    }
}
