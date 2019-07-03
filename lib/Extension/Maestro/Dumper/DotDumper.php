<?php

namespace Maestro\Extension\Maestro\Dumper;

use Maestro\Console\Dumper;
use Maestro\Node\Graph;

class DotDumper implements Dumper
{
    public function dump(Graph $graph): string
    {
        $lines = [
            'digraph maestro {'
        ];

        $lines[] = '  rankdir=BT';
        foreach ($graph->nodes() as $node) {
            $lines[] = sprintf('  "%s" [label="%s"]', $node->id(), $node->label());
        }

        foreach ($graph->edges() as $edge) {
            $nodeFrom = $graph->nodes()->get($edge->from());
            $nodeTo = $graph->nodes()->get($edge->to());
            $lines[] = sprintf('  "%s"->"%s"', $edge->from(), $edge->to());
        }

        $lines[] = '}';
        return implode(PHP_EOL, $lines);
    }
}
