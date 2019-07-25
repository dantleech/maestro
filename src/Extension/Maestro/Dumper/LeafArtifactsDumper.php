<?php

namespace Maestro\Extension\Maestro\Dumper;

use Maestro\Console\Dumper;
use Maestro\Graph\EnvironmentResolver\AggregatingEnvironmentResolver;
use Maestro\Graph\Graph;

class LeafArtifactsDumper implements Dumper
{
    public function dump(Graph $graph): string
    {
        $resolver = new AggregatingEnvironmentResolver();
        $out = [];
        foreach ($graph->leafs() as $leafNode) {
            $out[] = sprintf('<info>%s</>:', $leafNode->id());
            $out[] = json_encode($resolver->resolveFor($graph, $leafNode)->debugInfo(), JSON_PRETTY_PRINT);
        }

        return implode(PHP_EOL, $out);
    }
}
