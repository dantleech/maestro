<?php

namespace Maestro\Graph;

interface EnvironmentResolver
{
    public function resolveFor(Graph $graph, Node $node): Environment;
}