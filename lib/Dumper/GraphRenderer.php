<?php

namespace Maestro\Dumper;

use Maestro\Task\Graph;
use Maestro\Task\Node;

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
        $out= sprintf(
            '[%s] (%s) %s (%s) %s\n',
            "\033[34m" . $node->name() . "\033[0m",
            implode(', ', array_map(function (Node $node) {
                return $node->name();
            }, iterator_to_array($graph->dependenciesFor($node->name())))),
            $node->task()->description(),
            $node->state()->isIdle() ? '' : $node->state()->isFailed() ? "\033[31m" . $node->state()->toString() . "\033[0m" : "\033[32m" . $node->state()->toString() . "\033[0m",
            " " . json_encode($node->artifacts()->toArray()),
            ) . PHP_EOL;
        
        foreach ($graph->dependentsOf($node->name()) as $child) {
            $out .= $this->walkNode($graph, $child, $depth + 1);
        }
        
        return $out;
    }
}
