<?php

namespace Maestro\Extension\Maestro\Dumper;

use Maestro\Console\Dumper;
use Maestro\Node\ArtifactsResolver\AggregatingArtifactsResolver;
use Maestro\Node\Graph;

class LeafArtifactsDumper implements Dumper
{
    public function dump(Graph $graph): string
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
