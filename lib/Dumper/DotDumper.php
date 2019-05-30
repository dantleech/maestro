<?php

namespace Maestro\Dumper;

use Maestro\Task\Graph;

class DotDumper
{
    public function dump(Graph $graph): string
    {
        $lines = [
            'digraph maestro {'
        ];

        $lines[] = '    rankdir=BT';
        foreach ($graph->edges() as $edge) {
            $lines[] = sprintf('  "%s"->"%s"', $edge->from(), $edge->to());
        }

        $lines[] = '}';
        return implode(PHP_EOL, $lines);
    }
}
