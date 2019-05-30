<?php

namespace Maestro\Task;

use Maestro\Task\Exception\ArtifactNotFound;

interface ArtifactsResolver
{
    public function resolveFor(Graph $graph, Node $node): Artifacts;
}
