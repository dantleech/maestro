<?php

namespace Maestro\Node;

class NodeStateMachine extends StateMachine
{
    public function __construct(StateObservers $stateObservers = null)
    {
        parent::__construct([
            State::transition(State::WAITING(), State::BUSY()),
            State::transition(State::WAITING(), State::CANCELLED()),
            State::transition(State::BUSY(), State::DONE()),
            State::transition(State::BUSY(), State::FAILED()),
        ], $stateObservers);
    }
}
