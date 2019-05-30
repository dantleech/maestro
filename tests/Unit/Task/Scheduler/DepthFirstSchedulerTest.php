<?php

namespace Maestro\Tests\Unit\Task\Scheduler;

use Closure;
use Maestro\Task\Edge;
use Maestro\Task\Graph;
use Maestro\Task\Node;
use Maestro\Task\Queue;
use Maestro\Task\Scheduler\DepthFirstScheduler;
use Maestro\Task\State;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class DepthFirstSchedulerTest extends TestCase
{
    /**
     * @dataProvider provideSchedule
     */
    public function testSchedule(Closure $graphFactory, array $expectedOrder)
    {
        $scheduler = new DepthFirstScheduler();
        $queue = new Queue();
        $queue = $scheduler->schedule($graphFactory(), $queue);
        $order = array_map(function (Node $node) {
            return $node->name();
        }, iterator_to_array($queue));
        $this->assertEquals($expectedOrder, $order);
    }

    public function provideSchedule()
    {
        yield 'root node' => [
            function () {
                return Graph::create([
                    Node::create('root')
                ], []);
            },
            ['root'],
        ];

        yield 'waiting root node with children' => [
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
            ['n1'],
        ];

        yield 'idle root node with children' => [
            function () {
                return Graph::create([
                    $this->setState(Node::create('n1'), State::IDLE()),
                    Node::create('n2'),
                    Node::create('n3'),
                ], [
                    Edge::create('n2', 'n1'),
                    Edge::create('n3', 'n1'),
                ]);
                return $node;
            },
            ['n2', 'n3'],
        ];

        yield 'busy root node with children' => [
            function () {
                return Graph::create([
                    $this->setState(Node::create('n1'), State::BUSY()),
                    Node::create('n2'),
                    Node::create('n3'),
                ], [
                    Edge::create('n2', 'n1'),
                    Edge::create('n3', 'n1'),
                ]);
                return $node;
            },
            [],
        ];

        yield 'p1 idle p2 waiting' => [
            function () {
                return Graph::create([
                    $this->setState(Node::create('root'), State::IDLE()),
                    $this->setState(Node::create('package1'), State::IDLE()),
                    Node::create('package2'),
                    Node::create('package1.composer-install'),
                    Node::create('package2.composer-install'),
                ], [
                    Edge::create('package1.composer-install', 'package1'),
                    Edge::create('package2.composer-install', 'package2'),
                    Edge::create('package2', 'root'),
                    Edge::create('package1', 'root'),
                ]);
            },
            [
                'package2',
                'package1.composer-install',
            ],
        ];
    }

    private function setState(Node $node, State $state): Node
    {
        $reflection = new ReflectionClass($node);
        $property = $reflection->getProperty('state');
        $property->setAccessible(true);
        $property->setValue($node, $state);
        return $node;
    }
}
