<?php

namespace Maestro\Tests\Unit\Library\Graph;

use Maestro\Library\Graph\Node;
use Maestro\Library\Graph\Nodes;
use Maestro\Library\Graph\State;
use PHPUnit\Framework\TestCase;

class NodesTest extends TestCase
{
    public function testReturnsByStates()
    {
        $nodes = Nodes::fromNodes([
            Node::create('foo')
        ]);

        $this->assertCount(0, $nodes->byStates(State::BUSY()));
        $this->assertCount(1, $nodes->byStates(State::SCHEDULED()));
        $this->assertCount(1, $nodes->byStates(State::BUSY(), State::SCHEDULED()));
    }

    public function testContainsId()
    {
        $nodes = Nodes::fromNodes([
            Node::create('foo')
        ]);

        $this->assertTrue($nodes->containsId('foo'));
        $this->assertFalse($nodes->containsId('bar'));
    }

    /**
     * @dataProvider provideQuery
     */
    public function testQuery(string $query, array $nodes, array $expectedNodes)
    {
        $nodes = Nodes::fromNodes($nodes);
        $this->assertEquals(
            $expectedNodes,
            $nodes->query($query)->ids()
        );
    }

    public function provideQuery()
    {
        yield 'empty query always returns nothing' => [
            '',
            [
                Node::create('n1'),
                Node::create('n2'),
            ],
            [],
        ];

        yield 'explicit query' => [
            'node1',
            [
                Node::create('node1'),
                Node::create('node1node2'),
                Node::create('node2node1'),
            ],
            ['node1'],
        ];

        yield 'wildcard query' => [
            'node1*',
            [
                Node::create('node1'),
                Node::create('node1node2'),
                Node::create('node2node1'),
            ],
            ['node1', 'node1node2'],
        ];
    }

    public function testReversesOrder()
    {
        $nodes = Nodes::fromNodes([
            Node::create('one'),
            Node::create('two'),
            Node::create('three'),
        ]);
        $this->assertEquals(['three', 'two', 'one'], $nodes->reverse()->ids());
    }

    public function testGetTaggedNodes()
    {
        $nodes = Nodes::fromNodes([
            Node::create('n1', [
                'tags' => ['one'],
            ]),
            Node::create('n2', [
                'tags' => ['one'],
            ]),
            Node::create('n3'),
            Node::create('n4', [
                'tags' => ['two'],
            ]),
        ]);

        $nodes = $nodes->byTags(...['one', 'two']);
        $this->assertEquals(['n1','n2','n4'], $nodes->ids());
    }
}
