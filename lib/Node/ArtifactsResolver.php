<?php

namespace Maestro\Node;

use Maestro\Node\Artifacts;

interface ArtifactsResolver
{
    public function resolveFor(Graph $graph, Node $node): Artifacts;
}
