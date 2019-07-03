<?php

namespace Maestro\Tests\Unit\Node;

use Maestro\Node\Node;
use Maestro\Node\NodeStateMachine;
use Maestro\Node\State;
use PHPUnit\Framework\TestCase;
use Maestro\Tests\Unit\Node\NodeHelper;

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
}
