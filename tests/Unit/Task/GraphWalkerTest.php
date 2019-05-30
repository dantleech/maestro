<?php

namespace Maestro\Tests\Unit\Task;

use Closure;
use Maestro\Task\Edge;
use Maestro\Task\Graph;
use Maestro\Task\Node;
use Maestro\Task\NodeVisitor;
use Maestro\Task\NodeVisitorDecision;
use Maestro\Task\GraphWalker;
use PHPUnit\Framework\TestCase;

class GraphWalkerTest extends TestCase
{
    /**
     * @dataProvider provideWalk
     */
    public function testWalk(Closure $graphFactory, array $decisions, array $expectedVisits)
    {
        $visitor = new class implements NodeVisitor {
            public $decisions = [];
            public $visitedNodes = [];

            public function visit(Graph $graph, Node $node): NodeVisitorDecision
            {
                $this->visitedNodes[] = $node->name();
                if (isset($this->decisions[$node->name()])) {
                    return $this->decisions[$node->name()];
                }

                return NodeVisitorDecision::CONTINUE();
            }
        };

        $visitor->decisions = $decisions;

        $walker = new GraphWalker([
            $visitor
        ]);
        $walker->walk($graphFactory());

        $this->assertEquals($expectedVisits, $visitor->visitedNodes);
    }

    public function provideWalk()
    {
        yield 'root node' => [
            function () {
                return Graph::create([
                    Node::create('root')
                ], []);
            },
            [],
            ['root'],
        ];

        yield 'decision to not walk root nodes children' => [
            function () {
                return Graph::create([
                    Node::create('n1'),
                    Node::create('n2'),
                    Node::create('n3'),
                ], [
                    Edge::create('n2', 'n1'),
                    Edge::create('n3', 'n1'),
                ]);
            },
            [
                'n1' => NodeVisitorDecision::DO_NOT_WALK_CHILDREN(),
            ],
            ['n1'],
        ];

        yield 'walk decendants of root node' => [
            function () {
                return Graph::create([
                    Node::create('n1'),
                    Node::create('n2'),
                    Node::create('n3'),
                    Node::create('n4'),
                ], [
                    Edge::create('n2', 'n1'),
                    Edge::create('n3', 'n1'),
                    Edge::create('n4', 'n2'),
                ]);
                return $node;
            },
            [
            ],
            ['n1', 'n2', 'n4', 'n3'],
        ];
    }
}
