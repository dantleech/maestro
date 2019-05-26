<?php

namespace Maestro\Tests\Unit\Task\Scheduler;

use Closure;
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
    public function testSchedule(Closure $nodeFactory, array $expectedOrder)
    {
        $node = $nodeFactory();
        $scheduler = new DepthFirstScheduler();
        $queue = new Queue();
        $queue = $scheduler->schedule($node, $queue);
        $order = array_map(function (Node $node) {
            return $node->name();
        }, iterator_to_array($queue));
        $this->assertEquals($expectedOrder, $order);
    }

    public function provideSchedule()
    {
        yield 'root node' => [
            function () {
                return Node::createRoot();
            },
            ['root'],
        ];

        yield 'waiting root node with children' => [
            function () {
                $node = Node::createRoot();
                $node->addChild(Node::create('one'));
                $node->addChild(Node::create('two'));
                return $node;
            },
            ['root'],
        ];

        yield 'idle root node with children' => [
            function () {
                $node = Node::createRoot();
                $this->setState($node, State::IDLE());
                $node->addChild(Node::create('package1'));
                $node->addChild(Node::create('package2'));
                return $node;
            },
            ['package1', 'package2'],
        ];

        yield 'busy root node with children' => [
            function () {
                $node = Node::createRoot();
                $this->setState($node, State::BUSY());
                $node->addChild(Node::create('one'));
                $node->addChild(Node::create('two'));
                return $node;
            },
            [],
        ];

        yield 'p1 idle p2 waiting' => [
            function () {
                $node = Node::createRoot();
                $this->setState($node, State::IDLE());
                $package1 = $node->addChild(Node::create('p1'));
                $this->setState($package1, State::IDLE());
                $package2 = $node->addChild(Node::create('p2'));
                $composerInstall1 = $package1->addChild(Node::create('composer install 1'));
                $composerInstall2 = $package2->addChild(Node::create('composer install 2'));
                $composerInstall1->addChild(Node::create('phpunit'));
                return $node;
            },
            ['composer install 1', 'p2',],
        ];
    }

    private function setState(Node $node, State $state)
    {
        $reflection = new ReflectionClass($node);
        $property = $reflection->getProperty('state');
        $property->setAccessible(true);
        $property->setValue($node, $state);
    }
}
