<?php

namespace Maestro\Node;

interface StateObserver
{
    public function observe(StateChangeEvent $stateChangeEvent);
}
