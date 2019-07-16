<?php

namespace Maestro\Node\ArtifactsResolver;

use Maestro\Node\Artifacts;
use Maestro\Node\ArtifactsResolver;
use Maestro\Node\Graph;
use Maestro\Node\Node;

class AggregatingArtifactsResolver implements ArtifactsResolver
{
    public function resolveFor(Graph $graph, Node $node): Artifacts
    {
        $artifacts = Artifacts::empty();
        $ancestry = $graph->ancestryFor($node->id());
        foreach ($ancestry->reverse() as $ancestor) {
            $artifacts = $artifacts->merge($ancestor->artifacts());
        }
        return $artifacts->merge($node->artifacts());
    }
}
