<?php

namespace Maestro\Dumper;

use Maestro\Task\Graph;

class TargetDumper
{
    public function dump(Graph $graph): string
    {
        $out = [];
        foreach ($graph->roots() as $root) {
            foreach ($graph->descendantsFor($root->id()) as $node) {
                $out[] = sprintf(
                    '<info>%s</> (<comment>%s</>) -> %s',
                    $node->id(),
                    $node->task() ? $node->task()->description() : '',
                    implode(', ', $graph->dependenciesFor($node->id())->names())
                );
            }
        }

        return implode(PHP_EOL, $out);
    }
}
