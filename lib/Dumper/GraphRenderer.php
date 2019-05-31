<?php

namespace Maestro\Dumper;

use Maestro\Task\Graph;
use Maestro\Task\Node;
use Maestro\Task\State;

class GraphRenderer
{
    private $clear;

    public function __construct($clear = false)
    {
        $this->clear = $clear;
    }

    public function render(Graph $graph, $depth = 0): string
    {
        $out = '';
        foreach ($graph->roots() as $rootNode) {
            $out .= $this->walkNode($graph, $rootNode, $depth);
        }
        return $out;
    }

    private function walkNode(Graph $graph, Node $node, $depth): string
    {
        $out = '';
        foreach ($graph->nodes()->byStates(State::BUSY(), State::FAILED()) as $node) {
            $out .= sprintf(
                '[%s] %s (%s) %s %s\n',
                "\033[34m" . $node->name() . "\033[0m",
                $node->state()->toString(),
                implode(', ', array_map(function (Node $node) {
                    return $node->name();
                }, iterator_to_array($graph->dependenciesFor($node->name())))),
                $node->task()->description(),
                " " . json_encode($node->artifacts()->toArray()),
                ) . PHP_EOL;
        }
        
        return $out;
    }
}
