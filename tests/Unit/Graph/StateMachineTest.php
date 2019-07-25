<?php

namespace Maestro\Tests\Unit\Graph;

use Maestro\Graph\Exception\InvalidStateTransition;
use Maestro\Graph\Node;
use Maestro\Graph\NodeStateMachine;
use Maestro\Graph\State;
use Maestro\Graph\StateChangeEvent;
use Maestro\Graph\StateMachine;
use Maestro\Graph\StateObserver;
use Maestro\Graph\StateObservers;
use PHPUnit\Framework\TestCase;

class StateMachineTest extends TestCase
{
    public function testTransitionsToValidState()
    {
        $stateMachine = new StateMachine([
            State::transition(State::BUSY(), State::DONE()),
        ]);
        $node = Node::create('hello');
        $node = NodeHelper::setState($node, State::BUSY());

        $newState = $stateMachine->transition($node, State::DONE());
        $this->assertEquals(State::DONE(), $newState);
    }

    public function testTransitionsFromUnknownState()
    {
        $this->expectException(InvalidStateTransition::class);
        $stateMachine = new StateMachine([
        ]);
        $node = Node::create('hello');
        $node = NodeHelper::setState($node, State::BUSY());

        $newState = $stateMachine->transition($node, State::DONE());
        $this->assertEquals(State::DONE(), $newState);
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
