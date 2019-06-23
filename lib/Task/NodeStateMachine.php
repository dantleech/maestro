<?php

namespace Maestro\Task;

use Maestro\Task\Exception\InvalidStateTransition;

class NodeStateMachine
{
    /**
     * @var State
     */
    private $state;

    public function __construct(State $initialState = null)
    {
        $this->state = $initialState ?? State::WAITING();
    }

    public function transition(Node $node, State $state): void
    {
        if ($state->is($this->state)) {
            return;
        }

        if ($this->state->is(State::WAITING())) {
            $this->fromWaiting($state);
            return;
        }

        if ($this->state->is(State::BUSY())) {
            $this->fromBusy($state);
            return;
        }

        $this->fail($state);
    }

    public function state(): State
    {
        return $this->state;
    }

    private function fromWaiting(State $state): void
    {
        if ($state->is(State::BUSY())) {
            $this->state = State::BUSY();
            return;
        }

        if ($state->is(State::CANCELLED())) {
            $this->state = State::CANCELLED();
            return;
        }

        $this->fail($state);
    }

    private function fromBusy(State $state): void
    {
        if ($state->is(State::DONE())) {
            $this->state = State::DONE();
            return;
        }

        if ($state->is(State::FAILED())) {
            $this->state = State::FAILED();
            return;
        }

        $this->fail($state);
    }

    private function fail(State $state)
    {
        throw new InvalidStateTransition(sprintf(
            'Cannot transition from "%s" to "%s"',
            $this->state->toString(),
            $state->toString()
        ));
    }
}
