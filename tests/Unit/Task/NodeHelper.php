<?php

namespace Maestro\Tests\Unit\Task;

use Maestro\Task\Node;
use Maestro\Task\NodeStateMachine;
use Maestro\Task\State;
use ReflectionClass;

class NodeHelper
{
    public static function setState(Node $node, State $state): Node
    {
        $reflection = new ReflectionClass($node);
        $property = $reflection->getProperty('stateMachine');
        $property->setAccessible(true);
        $property->setValue($node, new NodeStateMachine($state));
        return $node;
    }
}
