<?php

namespace Maestro\Node;

use Maestro\Node\StateChangeEvent;

interface StateObserver
{
    public function observe(StateChangeEvent $stateChangeEvent);
}
