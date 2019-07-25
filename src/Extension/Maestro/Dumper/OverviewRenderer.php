<?php

namespace Maestro\Extension\Maestro\Dumper;

use Maestro\Console\Dumper;
use Maestro\Graph\Graph;
use Maestro\Graph\Node;
use Maestro\Graph\Nodes;
use Maestro\Graph\State;
use Maestro\Graph\TaskResult;

class OverviewRenderer implements Dumper
{
    public function dump(Graph $graph): string
    {
        $out = "\n";
        foreach ($graph->roots() as $rootNode) {
            foreach ($graph->dependentsFor($rootNode->id()) as $packageNode) {
                $out .= $this->walkNode($graph, $packageNode, 0);
            }
        }
        return $out;
    }

    private function walkNode(Graph $graph, Node $packageNode, $depth): string
    {
        $busyTasks= [];
        $nodes = $graph->descendantsFor($packageNode->id());
        foreach ($nodes->byState(State::BUSY(), State::DONE()) as $node) {
            if ($node->taskResult()->is(TaskResult::SUCCESS())) {
                continue;
            }
            $busyTasks[] = sprintf(
                "\n           [\033[32m%s\033[0m] [\033[%sm%s\033[0m] %s %s",
                $node->label(),
                $this->stateColor($node->taskResult()),
                $node->taskResult()->toString(),
                $node->task()->description(),
                json_encode($node->environment()->debugInfo())
            );
        }

        $out = sprintf(
            "  %-2s/ %-2s %s [%s]%s\n",
            $nodes->byState(State::DONE())->count(),
            $nodes->count(),
            $this->successMark($nodes),
            "\033[34m" . $packageNode->label() . "\033[0m",
            implode("", $busyTasks),
            );
        
        return $out;
    }

    private function stateColor(TaskResult $taskResult): int
    {
        if ($taskResult->is(TaskResult::FAILURE())) {
            return 31;
        }

        if ($taskResult->is(TaskResult::PENDING())) {
            return 33;
        }

        return 0;
    }

    private function successMark(Nodes $nodes)
    {
        if ($nodes->byTaskResult(TaskResult::SUCCESS())->count() === $nodes->count()) {
            return  "\033[32m✔\033[0m";
        }

        if ($nodes->byTaskResult(TaskResult::FAILURE())->count()) {
            return  "\033[31m✘\033[0m";
        }

        return  "\033[35m↻\033[0m";
    }
}
