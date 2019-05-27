<?php

namespace Maestro\Tests\Unit\Task;

use Maestro\Task\Edge;
use Maestro\Task\Exception\NodeDoesNotExist;
use Maestro\Task\Graph;
use Maestro\Task\Node;
use PHPUnit\Framework\TestCase;

class GraphTest extends TestCase
{
    public function testDependenciesForNode()
    {
        $graph = new Graph(
            [
                Node::create('n1'),
                Node::create('n2'),
                Node::create('n3'),
            ],
            [
                Edge::create('n2', 'n1'),
                Edge::create('n3', 'n1'),
            ]
        );

        $this->assertEquals([
            'n2', 'n3',
        ],$graph->dependenciesOf('n1')->names());
    }

    public function testThrowsExceptionIfEdgeFromNodeDoesNotExist()
    {
        $this->expectException(NodeDoesNotExist::class);

        $graph = new Graph(
            [
                Node::create('n1'),
            ],
            [
                Edge::create('n2', 'n1'),
            ]
        );
    }

    public function testThrowsExceptionIfEdgeToNodeDoesNotExist()
    {
        $this->expectException(NodeDoesNotExist::class);

        $graph = new Graph(
            [
                Node::create('n1'),
            ],
            [
                Edge::create('n1', 'n2'),
            ]
        );
    }
}
