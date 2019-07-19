<?php

namespace Maestro\Node\EnvironmentResolver;

use Maestro\Node\Environment;
use Maestro\Node\EnvironmentResolver;
use Maestro\Node\Graph;
use Maestro\Node\Node;

class AggregatingEnvironmentResolver implements EnvironmentResolver
{
    public function resolveFor(Graph $graph, Node $node): Environment
    {
        $environment = Environment::empty();
        $ancestry = $graph->ancestryFor($node->id());
        foreach ($ancestry->reverse() as $ancestor) {
            $environment = $environment->merge($ancestor->environment());
        }

        return $environment->merge($node->environment());
    }
}
