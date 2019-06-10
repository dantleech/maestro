<?php

namespace Maestro\Tests\Unit\Extension\Maestro\Dumper;

use Maestro\Task\Edge;
use Maestro\Task\Graph;
use Maestro\Task\Node;
use PHPUnit\Framework\TestCase;

class DumperTestCase extends TestCase
{
    protected function createGraph(): Graph
    {
        return Graph::create([
            Node::create('n1'),
            Node::create('n2'),
            Node::create('n3'),
        ], [
            Edge::create('n2', 'n1'),
            Edge::create('n3', 'n1'),
        ]);
    }
}
