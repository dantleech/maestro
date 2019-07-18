<?php

namespace Maestro\Tests\Unit\Node\ArtifactsResolver;

use Closure;
use Maestro\Node\Artifacts;
use Maestro\Node\ArtifactsResolver\AggregatingArtifactsResolver;
use Maestro\Node\Edge;
use Maestro\Node\Graph;
use Maestro\Node\Node;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AggregatingArtifactsResolverTest extends TestCase
{
    /**
     * @dataProvider provideResolveFor
     */
    public function testResolveFor(Closure $graphFactory, Node $node, array $expectedArtifacts)
    {
        $graph = $graphFactory($node);
        $this->assertEquals($expectedArtifacts, (new AggregatingArtifactsResolver())->resolveFor($graph, $node)->toArray());
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

        yield 'returns parent artifacts' => [
            function (Node $node) {
                return Graph::create([
                    $this->setArtifacts(
                        Node::create('root'),
                        ['foo' => 'bar']
                    ),
                    $node,
                ], [
                    Edge::create('target', 'root')
                ]);
            },
            Node::create('target'),
            ['foo' => 'bar']
        ];

        yield 'merges ancestor artifacts' => [
            function (Node $node) {
                return Graph::create([
                    $this->setArtifacts(
                        Node::create('n1'),
                        ['foo' => 'bar']
                    ),
                    $this->setArtifacts(
                        Node::create('n2'),
                        ['bar' => 'foo']
                    ),
                    $node,
                ], [
                    Edge::create('target', 'n2'),
                    Edge::create('n2', 'n1'),
                ]);
            },
            Node::create('target'),
            ['foo' => 'bar','bar' => 'foo']
        ];

        yield 'closer ancestors override more distant ones' => [
            function (Node $node) {
                return Graph::create([
                    $this->setArtifacts(
                        Node::create('n1'),
                        ['foo' => 'bar']
                    ),
                    $this->setArtifacts(
                        Node::create('n2'),
                        ['bar' => 'foo']
                    ),
                    $this->setArtifacts(
                        Node::create('n3'),
                        ['bar' => 'baz']
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
                    $this->setArtifacts(
                        Node::create('n1'),
                        ['foo' => 'bar']
                    ),
                    $this->setArtifacts(
                        Node::create('n2'),
                        ['bar' => 'foo']
                    ),
                    $node,
                ], [
                    Edge::create('target', 'n2'),
                    Edge::create('target', 'n1'),
                ]);
            },
            Node::create('target'),
            ['foo' => 'bar','bar' => 'foo']
        ];
    }

    private function setArtifacts(Node $node, array $array): Node
    {
        $reflection = new ReflectionClass(Node::class);
        $property = $reflection->getProperty('artifacts');
        $property->setAccessible(true);
        $property->setValue($node, Artifacts::create($array));
        return $node;
    }
}