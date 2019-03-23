<?php

namespace Phpactor\Extension\Maestro\Model\StateMachine;

use Phpactor\Extension\Maestro\Model\StateMachine\Exception\CircularReferenceDetected;
use Phpactor\Extension\Maestro\Model\StateMachine\Exception\PredicateNotSatisfied;
use Phpactor\Extension\Maestro\Model\StateMachine\Exception\StateNotFound;
use Phpactor\Extension\Maestro\Model\StateMachine\State;

class StateMachine
{
    /**
     * @var array
     */
    private $states = [];

    private $state;


    public function __construct(array $states)
    {
        foreach ($states as $state) {
            $this->add($state);
        }
    }

    public function goto(string $name): self
    {
        $context = new Context();
        $state = $this->getState($name);

        $this->satisfy($state, $context);

        $this->state = $state;
        return $this;
    }

    public function state(): State
    {
        return $this->state;
    }

    private function add(State $state)
    {
        $this->states[$state->name()] = $state;
    }

    private function getState(string $name): State
    {
        if (!isset($this->states[$name])) {
            throw new StateNotFound(sprintf(
                'Could not find state "%s", known states: "%s"',
                $name, implode('", "', array_keys($this->states))
            ));
        }

        return $this->states[$name];
    }

    private function satisfy(State $state, Context $context, array $seen = [])
    {
        $seen[$state->name()] = true;

        foreach ($state->dependsOn() as $dependencyName) {
            if (isset($seen[$dependencyName])) {
                throw new CircularReferenceDetected(sprintf(
                    'State "%s" has dependency on "%s" which has a dependency on "%s"',
                    $state->name(), $dependencyName, $state->name()
                ));
            }

            $dependency = $this->getState($dependencyName);
            $this->satisfy($dependency, $context, $seen);
        }

        if (!$state->predicate($context)) {
            $state->execute($context);
        }
        
        if (!$state->predicate($context)) {
            throw new PredicateNotSatisfied(sprintf(
                'Predicate for state "%s" was not satisfied after execution',
                $state->name()
            ));
        }
    }
}
