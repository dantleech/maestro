<?php

namespace Maestro\Tests\Unit\Task\NodeVisitor;

use Maestro\Task\Graph;
use Maestro\Task\Node;
use Maestro\Task\NodeVisitorDecision;
use Maestro\Task\NodeVisitor\ConcurrencyLimitingVisitor;
use Maestro\Task\State;
use Maestro\Tests\Unit\Task\NodeHelper;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ConcurrencyLimitingVisitorTest extends TestCase
{
    /**
     * @dataProvider provideVisit
     */
    public function testVisit(int $concurrency, array $nodes, Node $node, NodeVisitorDecision $expectedDecision)
    {
        $graph = Graph::create($nodes, []);
        $node4 = NodeHelper::setState(Node::create('n4'), State::WAITING());

        $visitor = new ConcurrencyLimitingVisitor($concurrency);
        $decision = $visitor->visit($graph, $node4);

        $this->assertEquals($expectedDecision, $decision);
    }

    public function provideVisit()
    {
        yield 'no busy nodes with limit of 1 will continue' => [
            1,
            [
                NodeHelper::setState(Node::create('n1'), State::IDLE()),
                NodeHelper::setState(Node::create('n2'), State::IDLE()),
                NodeHelper::setState(Node::create('n3'), State::IDLE()),
            ],
            NodeHelper::setState(Node::create('n4'), State::WAITING()),
            NodeVisitorDecision::CONTINUE()
        ];

        yield 'one busy node with limit of 1 will not traverse child nodes' => [
            1,
            [
                NodeHelper::setState(Node::create('n1'), State::BUSY()),
                NodeHelper::setState(Node::create('n3'), State::IDLE()),
            ],
            NodeHelper::setState(Node::create('n4'), State::WAITING()),
            NodeVisitorDecision::DO_NOT_WALK_CHILDREN()
        ];

        yield 'two busy nodes with limit of 1 will not traverse child nodes' => [
            1,
            [
                NodeHelper::setState(Node::create('n1'), State::BUSY()),
                NodeHelper::setState(Node::create('n3'), State::BUSY()),
            ],
            NodeHelper::setState(Node::create('n4'), State::WAITING()),
            NodeVisitorDecision::DO_NOT_WALK_CHILDREN()
        ];
    }

    public function testThrowsExceptionIfConcurrencyLessThan1()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Max concurrency must be 1 or more');
        $visitor = new ConcurrencyLimitingVisitor(0);
    }
}
