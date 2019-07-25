<?php

namespace Maestro\Graph;

use Maestro\Graph\Exception\InvalidStateTransition;

class StateMachine
{
    /**
     * @var StateObservers
     */
    private $stateObservers;

    /**
     * @var StateTransition[]
     */
    private $transitions = [];

    public function __construct(array $transitions = [], StateObservers $stateObservers = null)
    {
        $this->stateObservers = $stateObservers ?: new StateObservers();
        foreach ($transitions as $transition) {
            $this->addTransition($transition);
        }
    }

    public function transition(Node $node, State $state): State
    {
        foreach ($this->transitions as $transition) {
            if ($transition->to()->is($state)) {
                $this->stateObservers->notify(new StateChangeEvent($node, $node->state(), $state));
                return $state;
            }
        }

        $this->fail($node, $state);
    }

    private function addTransition(StateTransition $transition)
    {
        $this->transitions[] = $transition;
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
