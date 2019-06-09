<?php

namespace Maestro\Task\ArtifactsResolver;

use Maestro\Task\Artifacts;
use Maestro\Task\ArtifactsResolver;
use Maestro\Task\Graph;
use Maestro\Task\Node;

class AggregatingArtifactsResolver implements ArtifactsResolver
{
    public function resolveFor(Graph $graph, Node $node): Artifacts
    {
        $artifacts = Artifacts::empty();
        $ancestry = $graph->ancestryFor($node->id());
        foreach ($ancestry as $ancestor) {
            $artifacts = $artifacts->merge($ancestor->artifacts());
        }
        return $artifacts->merge($node->artifacts());
    }
}
