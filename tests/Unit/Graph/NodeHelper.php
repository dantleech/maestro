<?php

namespace Maestro\Tests\Unit\Graph;

use Maestro\Graph\Node;
use Maestro\Graph\State;
use ReflectionClass;

class NodeHelper
{
    public static function setState(Node $node, State $state): Node
    {
        $reflection = new ReflectionClass($node);
        $property = $reflection->getProperty('state');
        $property->setAccessible(true);
        $property->setValue($node, $state);
        return $node;
    }
}
