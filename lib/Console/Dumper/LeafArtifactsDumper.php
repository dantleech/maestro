<?php

namespace Maestro\Console\Dumper;

use Maestro\Task\ArtifactsResolver\AggregatingArtifactsResolver;
use Maestro\Task\Graph;

class LeafArtifactsDumper
{
    public function dump(Graph $graph)
    {
        $resolver = new AggregatingArtifactsResolver();
        $out = [];
        foreach ($graph->leafs() as $leafNode) {
            $out[] = sprintf('<info>%s</>:', $leafNode->id());
            $out[] = json_encode($resolver->resolveFor($graph, $leafNode)->toArray(), JSON_PRETTY_PRINT);
        }

        return implode(PHP_EOL, $out);
    }
}
