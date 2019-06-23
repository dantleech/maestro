<?php

namespace Maestro\Tests\Unit\Task;

use Maestro\Task\Node;
use Maestro\Task\NodeStateMachine;
use Maestro\Task\State;
use PHPUnit\Framework\TestCase;

class NodeStateMachineTest extends TestCase
{
    /**
     * @dataProvider provideStateChange
     */
    public function testStateChange(array $states)
    {
        $stateMachine = new NodeStateMachine();
        $node = Node::create('hello');

        foreach ($states as $state) {
            $stateMachine->transition($node, $state);
            $this->assertEquals($state, $stateMachine->state());
        }
    }

    public function provideStateChange()
    {
        yield 'waiting => waiting' => [
            [
                State::WAITING(),
            ]
        ];

        yield 'waiting => cancelled' => [
            [
                State::CANCELLED(),
            ]
        ];

        yield 'waiting => busy => done' => [
            [
                State::BUSY(),
                State::DONE(),
            ]
        ];

        yield 'waiting => busy => failed' => [
            [
                State::BUSY(),
                State::FAILED(),
            ]
        ];
    }
}
