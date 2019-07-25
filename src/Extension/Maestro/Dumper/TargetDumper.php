<?php

namespace Maestro\Extension\Maestro\Dumper;

use Maestro\Console\Dumper;
use Maestro\Graph\Graph;

class TargetDumper implements Dumper
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
                    implode(', ', $graph->dependenciesFor($node->id())->ids())
                );
            }
        }

        return implode(PHP_EOL, $out);
    }
}
