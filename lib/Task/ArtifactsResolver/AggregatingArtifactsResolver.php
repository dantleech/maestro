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
    }
}
