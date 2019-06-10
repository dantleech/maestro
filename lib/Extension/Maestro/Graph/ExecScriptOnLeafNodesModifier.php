<?php

namespace Maestro\Extension\Maestro\Graph;

use Maestro\Extension\Maestro\Task\ScriptTask;
use Maestro\Task\Edge;
use Maestro\Task\Graph;
use Maestro\Task\Node;

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
        foreach ($graph->leafs() as $leaf) {
            $scriptNodeId = sprintf($leaf->id() . '/script');
            $nodes = $graph->nodes()->add(Node::create($scriptNodeId, [
                'label' => 'script',
                'task' => new ScriptTask($this->script)
            ]));
            $edges = $graph->edges()->add(Edge::create($scriptNodeId, $leaf->id()));
            $graph = new Graph($nodes, $edges);
        }

        return $graph;
    }
}
