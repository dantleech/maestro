<?php

namespace Maestro\Tests\Unit\Task;

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

        foreach ($states as $state) {
            $stateMachine->changeTo($state);
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
