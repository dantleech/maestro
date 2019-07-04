<?php

namespace Maestro\Node;

interface ArtifactsResolver
{
    public function resolveFor(Graph $graph, Node $node): Artifacts;
}
