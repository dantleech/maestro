<?php

namespace Maestro\Graph;

class NodeStateMachine extends StateMachine
{
    public function __construct(StateObservers $stateObservers = null)
    {
        parent::__construct([
            State::transition(State::WAITING(), State::BUSY()),
            State::transition(State::WAITING(), State::CANCELLED()),
            State::transition(State::BUSY(), State::DONE()),
            State::transition(State::CANCELLED(), State::SCHEDULED()),
            State::transition(State::SCHEDULED(), State::WAITING()),
            State::transition(State::DONE(), State::SCHEDULED()),
        ], $stateObservers);
    }
}
