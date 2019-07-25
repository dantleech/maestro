<?php

namespace Maestro\Tests\Unit\Node\EnvironmentResolver;

use Closure;
use Maestro\Graph\Environment;
use Maestro\Graph\EnvironmentResolver\AggregatingEnvironmentResolver;
use Maestro\Graph\Edge;
use Maestro\Graph\Graph;
use Maestro\Graph\Node;
use Maestro\Graph\Vars;
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
        $this->assertEquals(
            array_merge([
                'env' => [],
                'vars' => [],
                'workspace' => null
            ], $expectedEnvironment),
            (new AggregatingEnvironmentResolver())->resolveFor($graph, $node)->debugInfo()
        );
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
            [
            ]
        ];

        yield 'returns parent environment' => [
            function (Node $node) {
                return Graph::create([
                    $this->setEnvironment(
                        Node::create('root'),
                        [
                            'vars' => Vars::fromArray([
                                'foo' => 'bar'
                            ]),
                        ]
                    ),
                    $node,
                ], [
                    Edge::create('target', 'root')
                ]);
            },
            Node::create('target'),
            [
                'vars' => ['foo' => 'bar']
            ]
        ];

        yield 'merges ancestor environment' => [
            function (Node $node) {
                return Graph::create([
                    $this->setEnvironment(
                        Node::create('n1'),
                        [
                            'vars' => Vars::fromArray([
                                'foo' => 'bar'
                            ]),
                        ]
                    ),
                    $this->setEnvironment(
                        Node::create('n2'),
                        [
                            'vars' => Vars::fromArray([
                                'bar' => 'foo'
                            ]),
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
                'vars' => [
                    'foo' => 'bar',
                    'bar' => 'foo'
                ],
            ]
        ];

        yield 'closer ancestors override more distant ones' => [
            function (Node $node) {
                return Graph::create([
                    $this->setEnvironment(
                        Node::create('n1'),
                        [
                            'vars' => Vars::fromArray([
                                'foo' => 'bar'
                            ]),
                        ]
                    ),
                    $this->setEnvironment(
                        Node::create('n2'),
                        [
                            'vars' => Vars::fromArray([
                                'bar' => 'foo'
                            ]),
                        ]
                    ),
                    $this->setEnvironment(
                        Node::create('n3'),
                        [
                            'vars' => Vars::fromArray([
                                'bar' => 'baz'
                            ]),
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
            [
                'vars' => [
                    'foo' => 'bar',
                    'bar' => 'baz'
                ],
            ]
        ];

        yield 'parallel dependencies are merged' => [
            function (Node $node) {
                return Graph::create([
                    $this->setEnvironment(
                        Node::create('n1'),
                        [
                            'vars' => Vars::fromArray([
                                'foo' => 'bar'
                            ]),
                        ]
                    ),
                    $this->setEnvironment(
                        Node::create('n2'),
                        [
                            'vars' => Vars::fromArray([
                                'bar' => 'foo'
                            ]),
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
                'vars' => [
                    'foo' => 'bar',
                    'bar' => 'foo'
                ],
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
