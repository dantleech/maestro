<?php

namespace Maestro\Tests\Unit\Library\Graph;

use Maestro\Library\Graph\Exception\InvalidStateTransition;
use Maestro\Library\Graph\Node;
use Maestro\Library\Graph\NodeStateMachine;
use Maestro\Library\Graph\State;
use Maestro\Library\Graph\StateChangeEvent;
use Maestro\Library\Graph\StateMachine;
use Maestro\Library\Graph\StateObserver;
use Maestro\Library\Graph\StateObservers;
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
