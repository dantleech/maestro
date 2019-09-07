<?php

namespace Maestro\Library\Graph;

interface StateObserver
{
    public function observe(StateChangeEvent $stateChangeEvent);
}
