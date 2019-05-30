<?php

namespace Maestro\Tests\Unit\Task;

use Maestro\Task\Edge;
use Maestro\Task\Exception\GraphContainsCircularDependencies;
use Maestro\Task\Exception\NodeDoesNotExist;
use Maestro\Task\Graph;
use Maestro\Task\Node;
use Maestro\Task\Nodes;
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

    public function testReturnsRootNodes()
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

        $this->assertEquals($graph->roots(), Nodes::fromNodes([
            Node::create('n1')
        ]));
    }

    /**
     * @dataProvider provideThrowsExceptionOnCicularDependencies
     */
    public function testThrowsExceptionIfThereAreCircularDependencies($graphFactory)
    {
        $this->expectException(GraphContainsCircularDependencies::class);
        $graphFactory()->roots();
    }

    public function provideThrowsExceptionOnCicularDependencies()
    {
        yield [
            function () {
                return new Graph(
                    [
                        Node::create('n1'),
                    ],
                    [
                        Edge::create('n1', 'n1'),
                    ]
                );
            }
        ];

        yield [
            function () {
                return new Graph(
                    [
                        Node::create('n1'),
                        Node::create('n2'),
                    ],
                    [
                        Edge::create('n1', 'n2'),
                        Edge::create('n2', 'n1'),
                    ]
                );
            }
        ];

        yield [
            function () {
                return new Graph(
                    [
                        Node::create('n1'),
                        Node::create('n2'),
                        Node::create('n3'),
                    ],
                    [
                        Edge::create('n1', 'n2'),
                        Edge::create('n2', 'n3'),
                        Edge::create('n3', 'n1'),
                    ]
                );
            }
        ];

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
