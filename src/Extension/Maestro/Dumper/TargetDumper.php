<?php

namespace Maestro\Extension\Maestro\Dumper;

use Maestro\Console\Dumper;
use Maestro\Graph\Graph;
use Maestro\Graph\Node;

class TargetDumper implements Dumper
{
    public function dump(Graph $graph): string
    {
        $out = [];
        foreach ($graph->roots() as $root) {
            foreach ($graph->descendantsFor($root->id()) as $node) {
                assert($node instanceof Node);
                $out[] = sprintf(
                    '<info>%s</> <bg=black;fg=white>%s</> (<comment>%s</>) -> %s',
                    $node->id(),
                    implode(', ', $node->tags()),
                    $node->task()->description(),
                    implode(', ', $graph->dependenciesFor($node->id())->ids())
                );
            }
        }

        return implode(PHP_EOL, $out);
    }
}
