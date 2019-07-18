<?php

namespace Maestro\Node;

final class StateObservers
{
    /**
     * @var StateObserver[]
     */
    private $observers = [];

    public function __construct(array $observers = [])
    {
        foreach ($observers as $observer) {
            $this->add($observer);
        }
    }

    public function notify(StateChangeEvent $event)
    {
        foreach ($this->observers as $observer) {
            $observer->observe($event);
        }
    }

    private function add(StateObserver $observer)
    {
        $this->observers[] = $observer;
    }
}
