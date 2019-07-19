<?php

namespace Maestro\Tests\Unit\Node\EnvironmentResolver;

use Closure;
use Maestro\Node\Environment;
use Maestro\Node\EnvironmentResolver\AggregatingEnvironmentResolver;
use Maestro\Node\Edge;
use Maestro\Node\Graph;
use Maestro\Node\Node;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AggregatingEnvironmentResolverTest extends TestCase
{
    /**
     * @dataProvider provideResolveFor
     */
    public function testResolveFor(Closure $graphFactory, Node $node, array $expectedEnvironment)
    {
        $graph = $graphFactory($node);
        $this->assertEquals($expectedEnvironment, (new AggregatingEnvironmentResolver())->resolveFor($graph, $node)->toArray());
    }

    public function provideResolveFor()
    {
        yield 'root node' => [
            function (Node $node) {
                return Graph::create([
                    $node,
                ], []);
            },
            Node::create('n1'),
            []
        ];

        yield 'returns parent environment' => [
            function (Node $node) {
                return Graph::create([
                    $this->setEnvironment(
                        Node::create('root'),
                        [
                            'parameters' => [
                                'foo' => 'bar'
                            ],
                        ]
                    ),
                    $node,
                ], [
                    Edge::create('target', 'root')
                ]);
            },
            Node::create('target'),
            ['foo' => 'bar']
        ];

        yield 'merges ancestor environment' => [
            function (Node $node) {
                return Graph::create([
                    $this->setEnvironment(
                        Node::create('n1'),
                        [
                            'parameters' => [
                                'foo' => 'bar'
                            ],
                        ]
                    ),
                    $this->setEnvironment(
                        Node::create('n2'),
                        [
                            'parameters' => [
                                'bar' => 'foo'
                            ],
                        ]
                    ),
                    $node,
                ], [
                    Edge::create('target', 'n2'),
                    Edge::create('n2', 'n1'),
                ]);
            },
            Node::create('target'),
            [
                'foo' => 'bar',
                'bar' => 'foo'
            ]
        ];

        yield 'closer ancestors override more distant ones' => [
            function (Node $node) {
                return Graph::create([
                    $this->setEnvironment(
                        Node::create('n1'),
                        [
                            'parameters' => [
                                'foo' => 'bar'
                            ],
                        ]
                    ),
                    $this->setEnvironment(
                        Node::create('n2'),
                        [
                            'parameters' => [
                                'bar' => 'foo'
                            ],
                        ]
                    ),
                    $this->setEnvironment(
                        Node::create('n3'),
                        [
                            'parameters' => [
                                'bar' => 'baz'
                            ],
                        ]
                    ),
                    $node,
                ], [
                    Edge::create('target', 'n3'),
                    Edge::create('n3', 'n2'),
                    Edge::create('n2', 'n1'),
                ]);
            },
            Node::create('target'),
            ['foo' => 'bar','bar' => 'baz']
        ];

        yield 'parallel dependencies are merged' => [
            function (Node $node) {
                return Graph::create([
                    $this->setEnvironment(
                        Node::create('n1'),
                        [
                            'parameters' => [
                                'foo' => 'bar'
                            ],
                        ]
                    ),
                    $this->setEnvironment(
                        Node::create('n2'),
                        [
                            'parameters' => [
                                'bar' => 'foo'
                            ],
                        ]
                    ),
                    $node,
                ], [
                    Edge::create('target', 'n2'),
                    Edge::create('target', 'n1'),
                ]);
            },
            Node::create('target'),
            [
                'foo' => 'bar',
                'bar' => 'foo'
            ]
        ];
    }

    private function setEnvironment(Node $node, array $array): Node
    {
        $reflection = new ReflectionClass(Node::class);
        $property = $reflection->getProperty('environment');
        $property->setAccessible(true);
        $property->setValue($node, Environment::create($array));
        return $node;
    }
}
