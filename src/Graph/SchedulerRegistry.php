<?php

namespace Maestro\Graph;

interface SchedulerRegistry
{
    public function getFor(Schedule $task): Scheduler;
}
