<?php

namespace Maestro\Graph\EnvironmentResolver;

use Maestro\Graph\Environment;
use Maestro\Graph\EnvironmentResolver;
use Maestro\Graph\Graph;
use Maestro\Graph\Node;

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
