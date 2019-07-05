<?php

namespace Maestro\Tests\Unit\Node;

use Maestro\Node\Node;
use Maestro\Node\NodeStateMachine;
use Maestro\Node\State;
use Maestro\Node\StateChangeEvent;
use Maestro\Node\StateObserver;
use Maestro\Node\StateObservers;
use PHPUnit\Framework\TestCase;

class NodeStateMachineTest extends TestCase
{
    /**
     * @dataProvider provideStateChange
     */
    public function testStateChange(State $from, State $to)
    {
        $stateMachine = new NodeStateMachine();
        $node = Node::create('hello');
        $node = NodeHelper::setState($node, $from);

        $newState = $stateMachine->transition($node, $to);
        $this->assertEquals($to, $newState);
    }

    public function provideStateChange()
    {
        yield 'waiting => waiting' => [
            State::WAITING(),
            State::WAITING(),
        ];

        yield 'waiting => cancelled' => [
            State::WAITING(),
            State::CANCELLED(),
        ];

        yield 'waiting => busy' => [
            State::WAITING(),
            State::BUSY(),
        ];

        yield 'busy => failed' => [
            State::BUSY(),
            State::FAILED(),
        ];

        yield 'busy => done' => [
            State::BUSY(),
            State::DONE(),
        ];
    }

    public function testNotifiesObservers()
    {
        $observer = new class implements StateObserver {
            public $event;
            public function observe(StateChangeEvent $stateChangeEvent)
            {
                $this->event = $stateChangeEvent;
            }
        };
        $node = Node::create('hello');
        $stateMachine = new NodeStateMachine(new StateObservers([$observer]));
        $stateMachine->transition($node, State::BUSY());

        $this->assertNotNull($observer->event, 'Observer was called');
        $this->assertSame($node, $observer->event->node());
    }
}
