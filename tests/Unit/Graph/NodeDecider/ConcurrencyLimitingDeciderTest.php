<?php

namespace Maestro\Tests\Unit\Graph\NodeDecider;

use Maestro\Graph\Graph;
use Maestro\Graph\Node;
use Maestro\Graph\NodeStateMachine;
use Maestro\Graph\NodeDeciderDecision;
use Maestro\Graph\NodeDecider\ConcurrencyLimitingDecider;
use Maestro\Graph\State;
use Maestro\Tests\Unit\Graph\NodeHelper;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ConcurrencyLimitingDeciderTest extends TestCase
{
    /**
     * @dataProvider provideVisit
     */
    public function testVisit(int $concurrency, array $nodes, Node $node, NodeDeciderDecision $expectedDecision)
    {
        $graph = Graph::create($nodes, []);
        $node4 = NodeHelper::setState(Node::create('n4'), State::WAITING());

        $visitor = new ConcurrencyLimitingDecider($concurrency);
        $decision = $visitor->decide($this->prophesize(NodeStateMachine::class)->reveal(), $graph, $node4);

        $this->assertEquals($expectedDecision, $decision);
    }

    public function provideVisit()
    {
        yield 'no busy nodes with limit of 1 will continue' => [
            1,
            [
                NodeHelper::setState(Node::create('n1'), State::DONE()),
                NodeHelper::setState(Node::create('n2'), State::DONE()),
                NodeHelper::setState(Node::create('n3'), State::DONE()),
            ],
            NodeHelper::setState(Node::create('n4'), State::WAITING()),
            NodeDeciderDecision::CONTINUE()
        ];

        yield 'one busy node with limit of 1 will not traverse child nodes' => [
            1,
            [
                NodeHelper::setState(Node::create('n1'), State::BUSY()),
                NodeHelper::setState(Node::create('n3'), State::DONE()),
            ],
            NodeHelper::setState(Node::create('n4'), State::WAITING()),
            NodeDeciderDecision::DO_NOT_WALK_CHILDREN()
        ];

        yield 'two busy nodes with limit of 1 will not traverse child nodes' => [
            1,
            [
                NodeHelper::setState(Node::create('n1'), State::BUSY()),
                NodeHelper::setState(Node::create('n3'), State::BUSY()),
            ],
            NodeHelper::setState(Node::create('n4'), State::WAITING()),
            NodeDeciderDecision::DO_NOT_WALK_CHILDREN()
        ];
    }

    public function testThrowsExceptionIfConcurrencyLessThan1()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Max concurrency must be 1 or more');
        $visitor = new ConcurrencyLimitingDecider(0);
    }
}