<?php

namespace Maestro\Graph;

interface StateObserver
{
    public function observe(StateChangeEvent $stateChangeEvent);
}
