<?php

namespace Maestro\Tests\Unit\Task;

use Closure;
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
        $graph = Graph::create(
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
        ], $graph->dependentsFor('n1')->names());
    }

    public function testReturnsRootNodes()
    {
        $graph = Graph::create(
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
     * @dataProvider provideReturnsAllAncestorsForGivenNode
     */
    public function testReturnsAncestryForNode(Closure $graphFactory, array $expectedOrder, string $targetNode)
    {
        $this->assertEquals($expectedOrder, $graphFactory()->ancestryFor($targetNode)->names());
    }

    public function provideReturnsAllAncestorsForGivenNode()
    {
        yield 'with no ancestors' => [
            function () {
                return Graph::create(
                    [
                        Node::create('n1'),
                    ],
                    [
                    ]
                );
            },
            [],
            'n1'
        ];

        yield 'width first 1' => [
            function () {
                return Graph::create(
                    [
                        Node::create('n1'),
                        Node::create('n2'),
                        Node::create('n3'),
                        Node::create('n4'),
                    ],
                    [
                        Edge::create('n2', 'n1'),
                        Edge::create('n3', 'n2'),
                        Edge::create('n3', 'n4'),
                        Edge::create('n4', 'n1'),
                    ]
                );
            },
            ['n2','n4','n1'],
            'n3'
        ];

        yield 'for leaf node' => [
            function () {
                return Graph::create(
                    [
                        Node::create('r'),
                        Node::create('p1'),
                        Node::create('p2'),
                        Node::create('p3'),
                        Node::create('init'),
                        Node::create('gc'),
                        Node::create('ci'),
                        Node::create('qa'),
                        Node::create('ut'),
                        Node::create('sa'),
                    ],
                    [
                        Edge::create('qa', 'sa'),
                        Edge::create('qa', 'ut'),
                        Edge::create('qa', 'init'),
                        Edge::create('sa', 'init'),
                        Edge::create('ut', 'init'),
                        Edge::create('init', 'ci'),
                        Edge::create('init', 'gc'),
                        Edge::create('ci', 'p1'),
                        Edge::create('gc', 'p1'),
                        Edge::create('init', 'p1'),
                        Edge::create('p1', 'r'),
                        Edge::create('p2', 'r'),
                        Edge::create('p3', 'r'),
                    ]
                );
            },
            ['init', 'ut', 'sa', 'p1', 'gc', 'ci', 'r'],
            'qa'
        ];

        yield 'for node with siblings' => [
            function () {
                return Graph::create(
                    [
                        Node::create('r'),
                        Node::create('p1'),
                        Node::create('p2'),
                        Node::create('p3'),
                        Node::create('init'),
                        Node::create('gc'),
                        Node::create('ci'),
                        Node::create('qa'),
                        Node::create('ut'),
                        Node::create('sa'),
                    ],
                    [
                        Edge::create('qa', 'sa'),
                        Edge::create('qa', 'ut'),
                        Edge::create('qa', 'init'),
                        Edge::create('sa', 'init'),
                        Edge::create('ut', 'init'),
                        Edge::create('init', 'ci'),
                        Edge::create('init', 'gc'),
                        Edge::create('ci', 'p1'),
                        Edge::create('gc', 'p1'),
                        Edge::create('init', 'p1'),
                        Edge::create('p1', 'r'),
                        Edge::create('p2', 'r'),
                        Edge::create('p3', 'r'),
                    ]
                );
            },
            ['p1','r'],
            'ci'
        ];
    }

    public function testReturnsDescendantsOfGivenNode()
    {
        $graph = Graph::create(
            [
                Node::create('r'),
                Node::create('p1'),
                Node::create('p2'),
                Node::create('p3'),
                Node::create('init'),
            ],
            [
                Edge::create('p1', 'r'),
                Edge::create('p2', 'r'),
                Edge::create('init', 'p1'),
            ]
        );

        $this->assertEquals([
            'p1', 'init', 'p2'
        ], $graph->descendantsFor('r')->names());
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
                return Graph::create(
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
                return Graph::create(
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
                return Graph::create(
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

        $graph = Graph::create(
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

        $graph = Graph::create(
            [
                Node::create('n1'),
            ],
            [
                Edge::create('n1', 'n2'),
            ]
        );
    }

    public function testReturnsAPrunedGraphForASpecifiedTarget()
    {
        $graph = Graph::create(
            [
                Node::create('n1'),
                Node::create('n2'),
                Node::create('n3'),
                Node::create('n4'),
                Node::create('n5'),
                Node::create('n6'),
            ],
            [
                Edge::create('n4', 'n3'),
                Edge::create('n5', 'n3'),
                Edge::create('n3', 'n2'),
                Edge::create('n2', 'n1'),
                Edge::create('n6', 'n1'),
            ]
        );

        $graph = $graph->pruneFor(['n5']);
        $this->assertEquals(['n3','n2','n1','n5'], $graph->nodes()->names());
        $this->assertCount(3, $graph->edges());
    }
}
