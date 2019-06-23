<?php

namespace Maestro\Task;

interface StateObserver
{
    public function observe(StateChangeEvent $stateChangeEvent);
}
