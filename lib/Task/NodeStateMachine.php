<?php

namespace Maestro\Task;

use Maestro\Task\Exception\InvalidStateTransition;

class NodeStateMachine
{
    public function transition(Node $node, State $state): State
    {
        if ($state->is($node->state())) {
            return $state;
        }

        if ($node->state()->is(State::WAITING())) {
            return $this->fromWaiting($node, $state);
        }

        if ($node->state()->is(State::BUSY())) {
            return $this->fromBusy($node, $state);
        }

        $this->fail($node, $state);
    }

    private function fromWaiting(Node $node, State $state): State
    {
        if ($state->is(State::BUSY())) {
            return State::BUSY();
        }

        if ($state->is(State::CANCELLED())) {
            return State::CANCELLED();
        }

        $this->fail($node, $state);
    }

    private function fromBusy(Node $node, State $state): State
    {
        if ($state->is(State::DONE())) {
            return State::DONE();
        }

        if ($state->is(State::FAILED())) {
            return State::FAILED();
        }

        $this->fail($node, $state);
    }

    private function fail(Node $node, State $state)
    {
        throw new InvalidStateTransition(sprintf(
            'Cannot transition from "%s" to "%s"',
            $node->state()->toString(),
            $state->toString()
        ));
    }
}
