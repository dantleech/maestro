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
                    '<info>%s</info> -> %s',
                    $node->id(),
                    implode(', ', $graph->dependenciesFor($node->id())->names())
                );
            }
        }

        return implode(PHP_EOL, $out);
    }
}
