<?php

namespace Maestro\Node;

interface EnvironmentResolver
{
    public function resolveFor(Graph $graph, Node $node): Environment;
}
