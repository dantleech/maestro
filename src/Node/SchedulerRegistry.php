<?php

namespace Maestro\Node;

interface SchedulerRegistry
{
    public function getFor(Schedule $task): Scheduler;
}
