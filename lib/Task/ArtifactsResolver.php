<?php

namespace Maestro\Task;

interface ArtifactsResolver
{
    public function resolveFor(Graph $graph, Node $node): Artifacts;
}
