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
        $out = "\n";
        foreach ($graph->roots() as $rootNode) {
            foreach ($graph->dependentsOf($rootNode->id()) as $packageNode) {
                $out .= $this->walkNode($graph, $packageNode, $depth);
            }
        }
        return $out;
    }

    private function walkNode(Graph $graph, Node $packageNode, $depth): string
    {
        $busyTasks= [];
        $nodes = $graph->descendantsOf($packageNode->id());
        foreach ($nodes->byState(State::BUSY(), State::FAILED()) as $node) {
            $busyTasks[] = sprintf(
                "[\033[32m%s\033[0m] [\033[%sm%s\033[0m] %s %s",
                $node->label(),
                $this->stateColor($node->state()),
                $node->state()->toString(),
                $node->task()->description(),
                json_encode($node->artifacts()->toArray()),
            );
        }

        $out = sprintf(
            "  [%s/%s] [%s] %s\n",
            $nodes->byState(State::DONE())->count(),
            $nodes->count(),
            "\033[34m" . $packageNode->label() . "\033[0m",
            implode(', ', $busyTasks)
        );
        
        return $out;
    }

    private function stateColor(State $state): int
    {
        if ($state->isFailed()) {
            return 31;
        }

        if ($state->isBusy()) {
            return 33;
        }

        return 0;
    }
}
