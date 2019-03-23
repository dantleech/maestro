<?php

namespace Phpactor\Extension\Maestro\Model\StateMachine;

use Phpactor\Extension\Maestro\Model\StateMachine\Exception\CircularReferenceDetected;
use Phpactor\Extension\Maestro\Model\StateMachine\Exception\PredicateNotSatisfied;
use Phpactor\Extension\Maestro\Model\StateMachine\Exception\StateNotFound;
use Phpactor\Extension\Maestro\Model\StateMachine\State;

interface StateMachine
{
    public function goto(string $name): StateMachine;

    public function state(): State;
}
