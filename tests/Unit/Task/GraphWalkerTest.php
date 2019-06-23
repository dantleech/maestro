<?php

namespace Maestro\Tests\Unit\Task;

use Closure;
use Maestro\Task\Edge;
use Maestro\Task\Graph;
use Maestro\Task\Node;
use Maestro\Task\NodeStateMachine;
use Maestro\Task\NodeVisitor;
use Maestro\Task\NodeVisitorDecision;
use Maestro\Task\GraphWalker;
use Maestro\Task\State;
use PHPUnit\Framework\TestCase;

class GraphWalkerTest extends TestCase
{
    /**
     * @dataProvider provideWalk
     */
    public function testWalk(Closure $graphFactory, array $decisions, array $expectedVisits)
    {
        $visitor = $this->createVisitor($decisions);
        $visitor->decisions = $decisions;

        $walker = new GraphWalker(new NodeStateMachine(), [
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
            },
            [
            ],
            ['n1', 'n2', 'n4', 'n3'],
        ];
    }

    /**
     * @dataProvider provideCancelsDescendant
     */
    public function testCancelsDescendants(
        Closure $graphFactory,
        array $decisions,
        array $expectedStates
    ) {
        $visitor = $this->createVisitor($decisions);
        $visitor->decisions = $decisions;

        $walker = new GraphWalker(new NodeStateMachine(), [
            $visitor
        ]);
        $graph = $graphFactory();
        assert($graph instanceof Graph);
        $walker->walk($graph);

        foreach ($expectedStates as $nodeName => $state) {
            $this->assertTrue(
                $graph->nodes()->get($nodeName)->state()->is($state),
                sprintf('Node %s is %s', $nodeName, $state->toString())
            );
        }
    }

    public function provideCancelsDescendant()
    {
        yield [
            function () {
                return Graph::create([
                    Node::create('root'),
                    Node::create('n1'),
                    Node::create('n2'),
                    Node::create('n3'),
                ], [
                    Edge::create('n1', 'root'),
                    Edge::create('n2', 'root'),
                    Edge::create('n3', 'n1'),
                ]);
            },
            [
                'root' => NodeVisitorDecision::CANCEL_DESCENDANTS(),
            ],
            [
                'root' => State::WAITING(),
                'n1' => State::CANCELLED(),
                'n2' => State::CANCELLED(),
                'n3' => State::CANCELLED(),
            ],
        ];
    }

    private function createVisitor(array $decisions): NodeVisitor
    {
        $visitor = new class implements NodeVisitor {
            public $decisions = [];
            public $visitedNodes = [];
        
            public function visit(NodeStateMachine $sm, Graph $graph, Node $node): NodeVisitorDecision
            {
                $this->visitedNodes[] = $node->id();
                if (isset($this->decisions[$node->id()])) {
                    return $this->decisions[$node->id()];
                }
        
                return NodeVisitorDecision::CONTINUE();
            }
        };

        return $visitor;
    }
}
